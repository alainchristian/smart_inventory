<?php

namespace Tests\Feature\Support;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/** Platform support must pass an authenticator-app code (or a recovery code) after the password. */
class TwoFactorTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role, array $extra = []): User
    {
        return User::forceCreate(array_merge([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('password-123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ], $extra));
    }

    /** A support account that has finished setup; returns [user, secret, recoveryCodes]. */
    private function enrolled(): array
    {
        $secret = Totp::generateSecret();
        $codes  = ['aaaaa-11111', 'bbbbb-22222'];
        $user   = $this->user('super_admin');
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => array_map(fn ($c) => Hash::make($c), $codes),
            'two_factor_confirmed_at' => now(),
            'two_factor_last_step' => null,
        ])->save();
        RateLimiter::clear('two-factor|' . $user->id);

        return [$user->fresh(), $secret, $codes];
    }

    // ── The code maths ───────────────────────────────────────────────────────

    public function test_codes_match_the_rfc_6238_test_vectors(): void
    {
        $secret = Totp::base32Encode('12345678901234567890');   // the RFC's SHA-1 test key

        // The RFC lists 8 digits; ours are the last 6 of the same value
        foreach ([59 => '287082', 1111111109 => '081804', 1111111111 => '050471', 1234567890 => '005924', 2000000000 => '279037'] as $time => $code) {
            $this->assertSame($code, Totp::codeForStep($secret, Totp::stepAt($time)), "t={$time}");
        }

        $this->assertSame('12345678901234567890', Totp::base32Decode($secret));
        $this->assertSame(32, strlen(Totp::generateSecret()));
    }

    public function test_verify_allows_one_step_of_drift_but_never_reuses_a_step(): void
    {
        $secret = Totp::generateSecret();
        $t      = 1_700_000_000;
        $step   = Totp::stepAt($t);

        $this->assertSame($step, Totp::verify($secret, Totp::codeForStep($secret, $step), null, $t));
        $this->assertSame($step - 1, Totp::verify($secret, Totp::codeForStep($secret, $step - 1), null, $t));
        $this->assertSame($step + 1, Totp::verify($secret, chunk_split(Totp::codeForStep($secret, $step + 1), 3, ' '), null, $t)); // "123 456"
        $this->assertNull(Totp::verify($secret, Totp::codeForStep($secret, $step - 2), null, $t));        // too old
        $this->assertNull(Totp::verify($secret, Totp::codeForStep($secret, $step), $step, $t));           // already used
        $this->assertNull(Totp::verify($secret, 'abcdef', null, $t));
        $this->assertNull(Totp::verify($secret, '12345', null, $t));
    }

    // ── The gate ─────────────────────────────────────────────────────────────

    public function test_a_password_alone_opens_nothing(): void
    {
        [$user] = $this->enrolled();

        $this->actingAs($user)->get(route('support.accounts'))->assertRedirect(route('two-factor.challenge'));
        $this->actingAs($user)->get(route('owner.dashboard'))->assertRedirect(route('two-factor.challenge'));
        $this->actingAs($user)->postJson(route('support.view-as.stop'))->assertStatus(401);
        $this->actingAs($user)->get(route('two-factor.challenge'))->assertOk()->assertSee('authenticator');
    }

    public function test_an_account_without_setup_is_sent_to_setup_and_cannot_skip_it(): void
    {
        $user = $this->user('super_admin');

        $this->actingAs($user)->get(route('support.accounts'))->assertRedirect(route('two-factor.setup'));
        $this->actingAs($user)->get(route('two-factor.challenge'))->assertRedirect(route('two-factor.setup'));

        $page = $this->actingAs($user)->get(route('two-factor.setup'))->assertOk();
        $page->assertSee('<svg', false);                       // the QR code
        $secret = session('two_factor_pending_secret');
        $this->assertSame(32, strlen($secret));
        $page->assertSee(Totp::groupedSecret($secret));
    }

    public function test_other_roles_are_never_asked_for_a_code(): void
    {
        $this->actingAs($this->user('owner'))->get(route('owner.dashboard'))->assertOk();
        $this->actingAs($this->user('owner'))->get(route('two-factor.challenge'))->assertRedirect(route('dashboard'));
    }

    // ── Setup ────────────────────────────────────────────────────────────────

    public function test_setup_with_a_correct_code_turns_it_on_and_shows_recovery_codes_once(): void
    {
        $user   = $this->user('super_admin', ['name' => 'Setup User']);
        $secret = Totp::generateSecret();

        $this->actingAs($user)->withSession(['two_factor_pending_secret' => $secret])
            ->post(route('two-factor.confirm'), ['code' => Totp::codeForStep($secret, Totp::stepAt())])
            ->assertRedirect(route('two-factor.codes'));

        $user->refresh();
        $this->assertTrue($user->hasTwoFactor());
        $this->assertSame($secret, $user->two_factor_secret);
        $this->assertCount(8, $user->two_factor_recovery_codes);
        $this->assertNotContains(session('two_factor_new_codes')[0], $user->two_factor_recovery_codes); // stored hashed
        $this->assertTrue(Hash::check(session('two_factor_new_codes')[0], $user->two_factor_recovery_codes[0]));
        $this->assertSame($user->id, session(RequireTwoFactor::SESSION_KEY));
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_enabled', 'entity_id' => $user->id]);

        // The secret is encrypted at rest
        $raw = \DB::table('users')->where('id', $user->id)->value('two_factor_secret');
        $this->assertNotSame($secret, $raw);

        $this->get(route('two-factor.codes'))->assertOk()->assertSee(session('two_factor_new_codes')[0]);
        $this->post(route('two-factor.codes.done'))->assertRedirect(route('dashboard'));
        $this->get(route('two-factor.codes'))->assertRedirect(route('dashboard'));   // gone for good
    }

    public function test_setup_with_a_wrong_code_changes_nothing(): void
    {
        $user = $this->user('super_admin');

        $this->actingAs($user)->withSession(['two_factor_pending_secret' => Totp::generateSecret()])
            ->post(route('two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasTwoFactor());
    }

    // ── Signing in with a code ───────────────────────────────────────────────

    public function test_a_correct_code_opens_the_console_and_cannot_be_used_twice(): void
    {
        [$user, $secret] = $this->enrolled();
        $code = Totp::codeForStep($secret, Totp::stepAt());

        $this->actingAs($user)->post(route('two-factor.verify'), ['code' => $code])->assertRedirect(route('dashboard'));
        $this->assertSame($user->id, session(RequireTwoFactor::SESSION_KEY));
        $this->get(route('support.accounts'))->assertOk();

        // The same code on a fresh sign-in is refused
        session()->flush();
        $this->actingAs($user->fresh())->post(route('two-factor.verify'), ['code' => $code])->assertSessionHasErrors('code');
    }

    public function test_a_wrong_code_is_refused_audited_and_locks_after_five(): void
    {
        [$user] = $this->enrolled();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->post(route('two-factor.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
        }
        $this->assertSame(5, \DB::table('activity_logs')->where('action', 'two_factor_failed')->where('entity_id', $user->id)->count());
        $this->assertNull(session(RequireTwoFactor::SESSION_KEY));

        // Locked: even the right code is refused now
        [, $secret] = [null, $user->two_factor_secret];
        $this->actingAs($user)->post(route('two-factor.verify'), ['code' => Totp::codeForStep($secret, Totp::stepAt())])
            ->assertSessionHasErrors('code');
        $this->assertNull(session(RequireTwoFactor::SESSION_KEY));

        RateLimiter::clear('two-factor|' . $user->id);
    }

    public function test_a_recovery_code_works_once(): void
    {
        [$user, , $codes] = $this->enrolled();

        $this->actingAs($user)->post(route('two-factor.verify'), ['code' => strtoupper($codes[0]), 'recovery' => 1])
            ->assertRedirect(route('dashboard'));
        $this->assertSame($user->id, session(RequireTwoFactor::SESSION_KEY));
        $this->assertCount(1, $user->fresh()->two_factor_recovery_codes);
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_recovery_used', 'entity_id' => $user->id]);

        session()->flush();
        $this->actingAs($user->fresh())->post(route('two-factor.verify'), ['code' => $codes[0], 'recovery' => 1])
            ->assertSessionHasErrors('code');
    }

    // ── Reset ────────────────────────────────────────────────────────────────

    public function test_server_command_clears_setup_so_it_must_be_done_again(): void
    {
        [$user] = $this->enrolled();

        $this->artisan('super:reset-2fa', ['email' => $user->email])->assertSuccessful();

        $user->refresh();
        $this->assertFalse($user->hasTwoFactor());
        $this->assertNull($user->two_factor_secret);
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_reset', 'entity_id' => $user->id]);
        $this->actingAs($user)->get(route('support.accounts'))->assertRedirect(route('two-factor.setup'));

        $owner = $this->user('owner');
        $this->artisan('super:reset-2fa', ['email' => $owner->email])->assertFailed();
    }
}
