<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * "Trust this device for 30 days" for owners and admins.
 */
class TrustedDeviceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.owner_two_factor' => true]);
    }

    private function user(string $role): User
    {
        $user = User::forceCreate([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('password-123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ]);
        $user->forceFill([
            'two_factor_secret' => Totp::generateSecret(),
            'two_factor_recovery_codes' => [Hash::make('aaaaa-11111')],
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user->fresh();
    }

    /** A trusted device row and the cookie token that goes with it. */
    private function device(User $user, array $extra = []): array
    {
        $token  = Str::random(60);
        $device = TrustedDevice::create($extra + [
            'user_id' => $user->id, 'token_hash' => TrustedDevice::hash($token),
            'expires_at' => now()->addDays(10), 'last_used_at' => now(),
        ]);

        return [$device, $token];
    }

    private function code(User $user): string
    {
        return Totp::codeForStep($user->two_factor_secret, Totp::stepAt());
    }

    public function test_ticking_the_box_after_a_real_code_trusts_the_device(): void
    {
        $owner = $this->user('owner');

        $response = $this->actingAs($owner)
            ->post(route('two-factor.verify'), ['code' => $this->code($owner), 'trust_device' => '1']);

        $response->assertRedirect(route('dashboard'));
        $token = $response->getCookie(TrustedDevice::COOKIE)?->getValue();
        $this->assertNotEmpty($token);

        $device = $owner->trustedDevices()->first();
        $this->assertNotNull($device);
        $this->assertSame(TrustedDevice::hash($token), $device->token_hash);   // only the hash is stored
        $this->assertNotSame($token, $device->token_hash);
        $this->assertEqualsWithDelta(TrustedDevice::DAYS * 86400, now()->diffInSeconds($device->expires_at), 5);
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_device_trusted', 'entity_id' => $owner->id]);
    }

    public function test_without_the_box_nothing_is_remembered(): void
    {
        $owner = $this->user('owner');

        $response = $this->actingAs($owner)->post(route('two-factor.verify'), ['code' => $this->code($owner)]);

        $response->assertRedirect(route('dashboard'));
        $this->assertSame(0, $owner->trustedDevices()->count());
        $this->assertNull($response->getCookie(TrustedDevice::COOKIE));
    }

    public function test_a_recovery_code_never_trusts_the_device(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)
            ->post(route('two-factor.verify'), ['code' => 'aaaaa-11111', 'recovery' => 1, 'trust_device' => '1'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, $owner->trustedDevices()->count());
    }

    public function test_a_trusted_device_skips_the_code_and_the_token_is_replaced(): void
    {
        $owner = $this->user('owner');
        [$device, $token] = $this->device($owner);
        $before = $device->token_hash;
        $expires = $device->expires_at->timestamp;

        $response = $this->actingAs($owner)->withCookie(TrustedDevice::COOKIE, $token)->get(route('owner.dashboard'));

        $response->assertOk();
        $device->refresh();
        $this->assertNotSame($before, $device->token_hash);          // rotated
        $this->assertSame($expires, $device->expires_at->timestamp); // expiry never slides
        $this->assertNotNull($response->getCookie(TrustedDevice::COOKIE));
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_device_skipped', 'entity_id' => $owner->id]);

        // The old token is dead
        $this->flushSession();
        $this->actingAs($owner)->withCookie(TrustedDevice::COOKIE, $token)->get(route('owner.dashboard'))
            ->assertRedirect(route('two-factor.challenge'));
    }

    public function test_someone_elses_expired_or_unknown_token_does_not_count(): void
    {
        $owner = $this->user('owner');
        $other = $this->user('owner');
        [, $othersToken] = $this->device($other);
        [, $expiredToken] = $this->device($owner, ['expires_at' => now()->subMinute()]);

        foreach ([$othersToken, $expiredToken, Str::random(60)] as $token) {
            $this->flushSession();
            $this->actingAs($owner)->withCookie(TrustedDevice::COOKIE, $token)->get(route('owner.dashboard'))
                ->assertRedirect(route('two-factor.challenge'));
        }
    }

    public function test_platform_support_can_never_trust_a_device(): void
    {
        config(['auth.support_two_factor' => true]);
        $support = $this->user('super_admin');
        [, $token] = $this->device($support);

        $this->assertFalse($support->canTrustDevices());
        $this->actingAs($support)->withCookie(TrustedDevice::COOKIE, $token)->get(route('support.accounts'))
            ->assertRedirect(route('two-factor.challenge'));

        $this->actingAs($support)->post(route('two-factor.verify'), ['code' => $this->code($support), 'trust_device' => '1']);
        $this->assertSame(1, $support->trustedDevices()->count());   // only the one made by hand above
    }

    public function test_the_option_is_shown_to_owners_and_not_to_support(): void
    {
        $owner = $this->user('owner');
        $this->actingAs($owner)->get(route('two-factor.challenge'))->assertOk()->assertSee('name="trust_device"', false);

        config(['auth.support_two_factor' => true]);
        $support = $this->user('super_admin');
        $this->actingAs($support)->get(route('two-factor.challenge'))->assertOk()->assertDontSee('name="trust_device"', false);
    }

    public function test_a_new_password_a_changed_app_or_a_deactivation_removes_every_trusted_device(): void
    {
        foreach ([
            fn (User $u) => $u->update(['password' => Hash::make('another-password-1')]),
            fn (User $u) => $u->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null])->save(),
            fn (User $u) => $u->update(['is_active' => false]),
        ] as $change) {
            $owner = $this->user('owner');
            $this->device($owner);
            $this->device($owner);

            $change($owner);

            $this->assertSame(0, $owner->trustedDevices()->count());
        }
    }

    public function test_ordinary_edits_keep_the_trusted_devices(): void
    {
        $owner = $this->user('owner');
        $this->device($owner);

        $owner->update(['name' => 'New Name', 'phone' => '0788000000']);
        $owner->forceFill(['two_factor_last_step' => 123])->save();

        $this->assertSame(1, $owner->trustedDevices()->count());
    }

    public function test_sign_out_all_devices_and_the_list(): void
    {
        $owner = $this->user('owner');
        [$mine, $token] = $this->device($owner);
        $this->device($owner);

        $this->actingAs($owner)->withSession([RequireTwoFactor::SESSION_KEY => $owner->id])->withCookie(TrustedDevice::COOKIE, $token)
            ->get(route('two-factor.manage'))->assertOk()->assertSee('this device')->assertSee('Sign out all devices');

        $this->post(route('two-factor.devices.revoke'))->assertRedirect(route('two-factor.manage'));

        $this->assertSame(0, $owner->trustedDevices()->count());
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_devices_revoked', 'entity_id' => $owner->id]);
    }
}
