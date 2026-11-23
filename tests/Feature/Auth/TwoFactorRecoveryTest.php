<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\RequireTwoFactor;
use App\Livewire\Owner\Users\UserList;
use App\Livewire\Support\Accounts;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lost phone: who can reset whom, and fresh recovery codes for a signed-in owner.
 */
class TwoFactorRecoveryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.owner_two_factor' => true]);
    }

    private function user(string $role, bool $enrolled = true): User
    {
        $user = User::forceCreate([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('password-123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ]);

        if ($enrolled) {
            $user->forceFill([
                'two_factor_secret' => Totp::generateSecret(),
                'two_factor_recovery_codes' => [Hash::make('aaaaa-11111')],
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        return $user->fresh();
    }

    private function resetLogged(User $target): bool
    {
        return \DB::table('activity_logs')->where('action', 'two_factor_reset')->where('entity_id', $target->id)->exists();
    }

    public function test_owner_resets_an_admin_and_it_is_audited(): void
    {
        $owner = $this->user('owner');
        $admin = $this->user('admin');

        Livewire::actingAs($owner)->test(UserList::class)
            ->call('confirmReset2fa', $admin->id)->assertSet('confirmResetId', $admin->id)
            ->call('executeReset2fa')->assertSet('confirmResetId', null);

        $this->assertFalse($admin->fresh()->hasTwoFactor());
        $this->assertTrue($this->resetLogged($admin));
        // They are sent to set it up again, and can open nothing else meanwhile
        $this->actingAs($admin->fresh())->get(route('owner.dashboard'))->assertRedirect(route('two-factor.setup'));
    }

    public function test_owner_can_reset_another_owner_but_never_themselves(): void
    {
        $owner = $this->user('owner');
        $other = $this->user('owner');

        Livewire::actingAs($owner)->test(UserList::class)
            ->call('confirmReset2fa', $owner->id)->assertSet('confirmResetId', null)
            ->call('confirmReset2fa', $other->id)->assertSet('confirmResetId', $other->id);

        $this->assertTrue($owner->fresh()->hasTwoFactor());
    }

    public function test_admin_cannot_reset_anyone(): void
    {
        $admin = $this->user('admin');
        $other = $this->user('admin');
        $owner = $this->user('owner');

        $c = Livewire::actingAs($admin)->test(UserList::class);
        $c->call('confirmReset2fa', $other->id)->assertSet('confirmResetId', null);
        $c->call('confirmReset2fa', $owner->id)->assertSet('confirmResetId', null);

        // Forcing the id from the browser changes nothing: the action re-checks.
        $c->set('confirmResetId', $owner->id)->call('executeReset2fa');
        $this->assertTrue($owner->fresh()->hasTwoFactor());
        $this->assertTrue($other->fresh()->hasTwoFactor());
    }

    public function test_managers_and_accounts_without_setup_have_nothing_to_reset(): void
    {
        $owner   = $this->user('owner');
        $manager = $this->user('shop_manager', enrolled: false);
        $fresh   = $this->user('admin', enrolled: false);

        Livewire::actingAs($owner)->test(UserList::class)
            ->call('confirmReset2fa', $manager->id)->assertSet('confirmResetId', null)
            ->call('confirmReset2fa', $fresh->id)->assertSet('confirmResetId', null);
    }

    public function test_support_resets_an_owner(): void
    {
        $support = $this->user('super_admin', enrolled: false);
        $owner   = $this->user('owner');

        Livewire::actingAs($support)->test(Accounts::class)
            ->call('confirmReset2fa', $owner->id)->assertSet('confirmResetId', $owner->id)
            ->call('executeReset2fa')->assertSet('confirmResetId', null);

        $this->assertFalse($owner->fresh()->hasTwoFactor());
        $this->assertTrue($this->resetLogged($owner));
    }

    public function test_new_recovery_codes_need_a_current_app_code_and_replace_the_old_ones(): void
    {
        $owner  = $this->user('owner');
        $oldHash = $owner->two_factor_recovery_codes;
        $code   = Totp::codeForStep($owner->two_factor_secret, Totp::stepAt());

        $this->actingAs($owner)->withSession([RequireTwoFactor::SESSION_KEY => $owner->id])
            ->post(route('two-factor.regenerate'), ['code' => $code])
            ->assertRedirect(route('two-factor.codes'));

        $owner->refresh();
        $this->assertCount(8, $owner->two_factor_recovery_codes);
        $this->assertNotSame($oldHash, $owner->two_factor_recovery_codes);
        $this->assertCount(8, session('two_factor_new_codes'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor_codes_regenerated', 'entity_id' => $owner->id]);
    }

    public function test_a_wrong_code_or_a_recovery_code_does_not_make_new_codes(): void
    {
        $owner   = $this->user('owner');
        $oldHash = $owner->two_factor_recovery_codes;

        $this->actingAs($owner)->withSession([RequireTwoFactor::SESSION_KEY => $owner->id]);
        $this->post(route('two-factor.regenerate'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post(route('two-factor.regenerate'), ['code' => 'aaaaa-11111'])->assertSessionHasErrors('code');

        $this->assertSame($oldHash, $owner->fresh()->two_factor_recovery_codes);
    }

    public function test_the_codes_page_makes_you_confirm_you_saved_them(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)->withSession([RequireTwoFactor::SESSION_KEY => $owner->id, 'two_factor_new_codes' => ['aaaaa-11111']])
            ->get(route('two-factor.codes'))->assertOk()
            ->assertSee('x-model="saved"', false)
            ->assertSee(':disabled="!saved"', false);
    }
}
