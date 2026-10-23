<?php

namespace Tests\Feature\Support;

use App\Livewire\Owner\Users\UserList;
use App\Livewire\Support\Accounts;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/** Platform support: manages accounts, cannot operate the business, hidden from the business's lists. */
class SuperAdminTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role, array $extra = []): User
    {
        return User::forceCreate(array_merge([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('password-123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ], $extra));
    }

    /** Signed in as support, past the two-step check (that has its own tests). */
    private function asSupport(User $support): static
    {
        return $this->actingAs($support)->withSession([\App\Http\Middleware\RequireTwoFactor::SESSION_KEY => $support->id]);
    }

    public function test_support_account_lands_on_accounts_and_nowhere_operational(): void
    {
        $support = $this->user('super_admin');

        $this->assertSame(route('support.accounts'), $support->getDashboardRoute());
        $this->asSupport($support)->get(route('support.accounts'))->assertOk();

        foreach (['owner.dashboard', 'owner.users.index', 'owner.settings', 'shop.dashboard', 'warehouse.dashboard'] as $route) {
            $this->asSupport($support)->get(route($route))->assertForbidden();
        }

        $this->assertFalse($support->isOwner());
        $this->assertFalse($support->isAdmin());
        $this->assertFalse($support->canViewPurchasePrices());
        $this->assertFalse($support->canApprovePriceOverrides());
    }

    public function test_support_reads_everyones_activity_including_its_own(): void
    {
        $support = $this->user('super_admin');
        $shop    = $this->user('shop_manager', ['name' => 'Seller Sam']);

        \App\Services\AuditLogger::log(['actor' => $shop, 'action' => 'sale_completed', 'module' => 'sales', 'entity_type' => 'Sale', 'entity_identifier' => 'SALE-1']);
        \App\Services\AuditLogger::log(['actor' => $support, 'action' => 'support_password_reset', 'module' => 'users', 'entity_type' => 'User', 'entity_identifier' => 'x@example.test']);

        $this->asSupport($support)->get(route('support.activity-logs'))
            ->assertOk()
            ->assertSee('Seller Sam')
            ->assertSee('Platform support');

        // Still closed to the roles that were never meant to read it
        $this->actingAs($shop)->get(route('support.activity-logs'))->assertForbidden();
        $this->actingAs($shop)->get(route('owner.activity-logs.index'))->assertForbidden();
    }

    public function test_each_account_row_links_to_that_persons_activity(): void
    {
        $support = $this->user('super_admin');
        $sam     = $this->user('shop_manager', ['name' => 'Seller Sam']);
        $zoe     = $this->user('shop_manager', ['name' => 'Seller Zoe']);

        \App\Services\AuditLogger::log(['actor' => $sam, 'action' => 'sale_completed', 'module' => 'sales', 'entity_type' => 'Sale', 'entity_identifier' => 'SALE-SAM']);
        \App\Services\AuditLogger::log(['actor' => $zoe, 'action' => 'sale_completed', 'module' => 'sales', 'entity_type' => 'Sale', 'entity_identifier' => 'SALE-ZOE']);

        $link = route('support.activity-logs', ['filterUser' => $sam->id]);

        Livewire::actingAs($support)->test(Accounts::class)->assertSeeHtml('href="' . $link . '"');

        // The link opens the logs with only that person's rows
        $this->asSupport($support)->get($link)->assertOk()
            ->assertSee('SALE-SAM')
            ->assertDontSee('SALE-ZOE');
    }

    public function test_nobody_else_reaches_the_support_console(): void
    {
        foreach (['owner', 'admin', 'shop_manager', 'warehouse_manager'] as $role) {
            $this->actingAs($this->user($role))->get(route('support.accounts'))->assertForbidden();
        }

        // Even if the component is mounted some other way, it refuses
        Livewire::actingAs($this->user('owner'))->test(Accounts::class)->assertForbidden();
    }

    public function test_the_business_cannot_see_or_open_a_support_account(): void
    {
        $owner   = $this->user('owner');
        $support = $this->user('super_admin', ['name' => 'Hidden Support']);

        Livewire::actingAs($owner)->test(UserList::class)->assertDontSee('Hidden Support');

        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($owner)->test(UserList::class)->call('openEdit', $support->id);
    }

    public function test_support_account_cannot_be_managed_from_the_console_or_by_an_owner(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner');

        $this->assertFalse($owner->canManageUser($support));
        $this->assertFalse($support->canManageUser($this->user('super_admin')));
        $this->assertTrue($support->canManageUser($owner));

        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($support)->test(Accounts::class)->call('openEdit', $support->id);
    }

    public function test_support_can_reset_an_owners_password_and_it_is_audited(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner');
        $this->user('owner'); // a second active owner

        Livewire::actingAs($support)->test(Accounts::class)
            ->call('openEdit', $owner->id)
            ->set('form_password', 'a-new-temp-pass')
            ->call('save')
            ->assertHasNoErrors();

        $owner->refresh();
        $this->assertTrue(password_verify('a-new-temp-pass', $owner->password));
        $this->assertTrue($owner->must_change_password);

        $log = ActivityLog::where('action', 'support_password_reset')->where('entity_id', $owner->id)->first();
        $this->assertNotNull($log);
        $this->assertSame('Platform support', $log->user_name);
        $this->assertSame('super_admin', $log->actor_role_snapshot);
        $this->assertStringNotContainsString('a-new-temp-pass', json_encode($log->toArray()));
    }

    public function test_the_last_active_owner_cannot_be_deactivated_or_demoted(): void
    {
        $support = $this->user('super_admin');
        // Start from a clean slate: this owner is the only active one
        User::where('role', 'owner')->update(['is_active' => false]);
        $owner = $this->user('owner');

        Livewire::actingAs($support)->test(Accounts::class)
            ->call('confirmToggle', $owner->id)
            ->call('executeToggle');
        $this->assertTrue($owner->fresh()->is_active);

        Livewire::actingAs($support)->test(Accounts::class)
            ->call('openEdit', $owner->id)
            ->set('form_role', 'admin')
            ->call('save')
            ->assertHasErrors('form_role');
        $this->assertSame('owner', $owner->fresh()->role->value);
    }

    public function test_a_support_role_cannot_be_granted_through_the_console(): void
    {
        $support = $this->user('super_admin');

        Livewire::actingAs($support)->test(Accounts::class)
            ->call('openCreate')
            ->set('form_name', 'Sneaky')
            ->set('form_email', 'sneaky@example.test')
            ->set('form_role', 'super_admin')
            ->set('form_password', 'password-123')
            ->call('save')
            ->assertHasErrors('form_role');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.test']);
    }

    public function test_support_can_create_an_account_that_must_change_its_password(): void
    {
        $support = $this->user('super_admin');

        Livewire::actingAs($support)->test(Accounts::class)
            ->call('openCreate')
            ->set('form_name', 'New Owner')
            ->set('form_email', 'new.owner@example.test')
            ->set('form_role', 'owner')
            ->set('form_password', 'password-123')
            ->call('save')
            ->assertHasNoErrors();

        $created = User::where('email', 'new.owner@example.test')->firstOrFail();
        $this->assertSame('owner', $created->role->value);
        $this->assertTrue($created->must_change_password);
        $this->assertDatabaseHas('activity_logs', ['action' => 'support_user_created', 'entity_id' => $created->id]);
    }

    public function test_command_creates_and_removes_a_support_account(): void
    {
        $this->artisan('super:create', ['email' => 'Support@Example.test', '--name' => 'Alain'])
            ->expectsQuestion('Password (min 12 characters)', 'correct-horse-battery')
            ->expectsQuestion('Repeat password', 'correct-horse-battery')
            ->assertSuccessful();

        $user = User::where('email', 'support@example.test')->firstOrFail();
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->is_active);

        $this->artisan('super:create', ['email' => 'support@example.test', '--remove' => true])->assertSuccessful();
        $this->assertDatabaseMissing('users', ['email' => 'support@example.test']);
    }

    public function test_command_can_read_the_password_from_the_environment_and_force_a_change(): void
    {
        putenv('SUPER_TEST_PW=correct-horse-battery');

        $this->artisan('super:create', ['email' => 'env@example.test', '--password-env' => 'SUPER_TEST_PW', '--must-change' => true])
            ->assertSuccessful();

        $user = User::where('email', 'env@example.test')->firstOrFail();
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(password_verify('correct-horse-battery', $user->password));

        putenv('SUPER_TEST_PW');
        $this->artisan('super:create', ['email' => 'env2@example.test', '--password-env' => 'SUPER_TEST_PW'])->assertFailed();
    }

    public function test_command_refuses_a_short_password_and_never_removes_other_roles(): void
    {
        $this->artisan('super:create', ['email' => 'short@example.test'])
            ->expectsQuestion('Password (min 12 characters)', 'short')
            ->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'short@example.test']);

        $owner = $this->user('owner');
        $this->artisan('super:create', ['email' => $owner->email, '--remove' => true])->assertFailed();
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }
}
