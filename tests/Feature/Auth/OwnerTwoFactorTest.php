<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Owners and admins have full system access, so a password alone isn't enough:
 * they pass the same authenticator-app step as platform support. Shop and
 * warehouse managers are not asked. (phpunit.xml turns it off for every other
 * test; these switch it on.)
 */
class OwnerTwoFactorTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.owner_two_factor' => true]);
    }

    private function user(string $role, array $extra = []): User
    {
        return User::forceCreate(array_merge([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('password-123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ], $extra));
    }

    private function enrol(User $user): User
    {
        $user->forceFill([
            'two_factor_secret' => Totp::generateSecret(),
            'two_factor_recovery_codes' => [Hash::make('aaaaa-11111')],
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user->fresh();
    }

    public function test_owner_and_admin_must_use_two_step_but_managers_do_not(): void
    {
        $this->assertTrue($this->user('owner')->requiresTwoFactor());
        $this->assertTrue($this->user('admin')->requiresTwoFactor());
        $this->assertFalse($this->user('shop_manager')->requiresTwoFactor());
        $this->assertFalse($this->user('warehouse_manager')->requiresTwoFactor());
    }

    public function test_owner_without_two_step_is_sent_to_setup_and_cannot_open_anything(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)->get(route('owner.dashboard'))->assertRedirect(route('two-factor.setup'));
        $this->actingAs($owner)->get(route('owner.users.index'))->assertRedirect(route('two-factor.setup'));
        $this->actingAs($owner)->getJson(route('owner.dashboard'))->assertStatus(401);
    }

    public function test_enrolled_owner_is_asked_for_the_code_until_it_is_passed(): void
    {
        $owner = $this->enrol($this->user('owner'));

        $this->actingAs($owner)->get(route('owner.dashboard'))->assertRedirect(route('two-factor.challenge'));

        $this->actingAs($owner)->withSession([RequireTwoFactor::SESSION_KEY => $owner->id])
            ->get(route('owner.dashboard'))->assertOk();
    }

    public function test_admin_is_held_at_the_code_step_too(): void
    {
        $admin = $this->enrol($this->user('admin'));

        $this->actingAs($admin)->get(route('owner.dashboard'))->assertRedirect(route('two-factor.challenge'));
    }

    public function test_a_session_passed_for_someone_else_does_not_count(): void
    {
        $owner = $this->enrol($this->user('owner'));

        $this->actingAs($owner)->withSession([RequireTwoFactor::SESSION_KEY => $owner->id + 1])
            ->get(route('owner.dashboard'))->assertRedirect(route('two-factor.challenge'));
    }

    public function test_shop_manager_is_never_held(): void
    {
        $manager = $this->user('shop_manager');

        $this->actingAs($manager)->get(route('two-factor.setup'))->assertRedirect(route('dashboard'));
    }

    public function test_support_view_as_still_works_on_an_owner_without_two_step(): void
    {
        // The gate looks at who is really signed in (support), before the view-as swap,
        // so an owner who has not set up two-step is still viewable by support.
        $support = $this->user('super_admin');
        $owner   = $this->user('owner');

        $this->actingAs($support)->post(route('support.view-as.start', $owner->id))->assertRedirect();
        $this->get(route('owner.dashboard'))->assertOk();
    }

    public function test_it_can_be_switched_off_on_the_server(): void
    {
        config(['auth.owner_two_factor' => false]);
        $owner = $this->user('owner');

        $this->assertFalse($owner->requiresTwoFactor());
        $this->actingAs($owner)->get(route('owner.dashboard'))->assertOk();
    }

    public function test_owner_can_complete_setup_with_a_real_code(): void
    {
        $owner = $this->user('owner');

        // The setup page works for an owner (it is where they are sent)...
        $this->actingAs($owner)->get(route('two-factor.setup'))->assertOk();

        // ...and one correct code from the app turns it on and issues recovery codes.
        $secret = Totp::generateSecret();
        $this->actingAs($owner)->withSession(['two_factor_pending_secret' => $secret])
            ->post(route('two-factor.confirm'), ['code' => Totp::codeForStep($secret, Totp::stepAt())])
            ->assertRedirect(route('two-factor.codes'));

        $owner->refresh();
        $this->assertTrue($owner->hasTwoFactor());
        $this->assertCount(8, $owner->two_factor_recovery_codes);
        $this->assertSame($owner->id, session(RequireTwoFactor::SESSION_KEY));
    }

    public function test_server_command_resets_an_owner_so_a_lost_phone_is_recoverable(): void
    {
        $owner = $this->enrol($this->user('owner'));

        $this->artisan('account:reset-2fa', ['email' => $owner->email])->assertSuccessful();

        $this->assertFalse($owner->fresh()->hasTwoFactor());
        $this->actingAs($owner->fresh())->get(route('owner.dashboard'))->assertRedirect(route('two-factor.setup'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_reset', 'entity_id' => $owner->id]);
    }
}
