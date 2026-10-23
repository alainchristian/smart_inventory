<?php

namespace Tests\Feature\Support;

use App\Livewire\Dashboard\ActivityFeed;
use App\Livewire\Owner\ActivityLogs;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/** The owner sees what platform support CHANGES, not its routine sign-ins and viewing. */
class ActivityVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::forceCreate([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('password-123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ]);
    }

    private function log(User $actor, string $action, string $identifier): ActivityLog
    {
        return AuditLogger::log([
            'actor' => $actor, 'action' => $action, 'module' => 'users',
            'entity_type' => 'User', 'entity_identifier' => $identifier,
        ]);
    }

    /** @return array{0: User, 1: User, 2: User, 3: array<string, ActivityLog>} */
    private function seedLogs(): array
    {
        $owner   = $this->user('owner');
        $support = $this->user('super_admin');
        $shop    = $this->user('shop_manager');

        $rows = [
            // routine support noise: hidden from the business
            'in'      => $this->log($support, 'signed_in', 'ROW-SUPPORT-SIGNIN'),
            'out'     => $this->log($support, 'signed_out', 'ROW-SUPPORT-SIGNOUT'),
            'view'    => $this->log($support, 'support_view_as_started', 'ROW-SUPPORT-VIEWAS'),
            'denied'  => $this->log($support, 'permission_denied', 'ROW-SUPPORT-DENIED'),
            // support CHANGES and security events: visible
            'reset'   => $this->log($support, 'support_password_reset', 'ROW-SUPPORT-RESET'),
            'role'    => $this->log($support, 'support_user_updated', 'ROW-SUPPORT-ROLE'),
            '2fa'     => $this->log($support, 'two_factor_failed', 'ROW-SUPPORT-2FAFAIL'),
            // everyone else's routine rows are untouched
            'shop_in' => $this->log($shop, 'signed_in', 'ROW-SHOP-SIGNIN'),
        ];

        return [$owner, $support, $shop, $rows];
    }

    public function test_owner_sees_support_changes_but_not_its_routine_entries(): void
    {
        [$owner] = $this->seedLogs();

        $page = Livewire::actingAs($owner)->test(ActivityLogs::class);

        foreach (['ROW-SUPPORT-RESET', 'ROW-SUPPORT-ROLE', 'ROW-SUPPORT-2FAFAIL', 'ROW-SHOP-SIGNIN'] as $shown) {
            $page->assertSee($shown);
        }
        foreach (['ROW-SUPPORT-SIGNIN', 'ROW-SUPPORT-SIGNOUT', 'ROW-SUPPORT-VIEWAS', 'ROW-SUPPORT-DENIED'] as $hidden) {
            $page->assertDontSee($hidden);
        }
        // Changes show under the neutral name, never the person's own
        $page->assertSee('Platform support');
    }

    public function test_support_itself_sees_everything(): void
    {
        [, $support] = $this->seedLogs();

        $page = Livewire::actingAs($support)->test(ActivityLogs::class);

        foreach (['ROW-SUPPORT-SIGNIN', 'ROW-SUPPORT-VIEWAS', 'ROW-SUPPORT-DENIED', 'ROW-SUPPORT-RESET', 'ROW-SHOP-SIGNIN'] as $shown) {
            $page->assertSee($shown);
        }
    }

    public function test_owner_cannot_open_a_hidden_entry_by_id(): void
    {
        [$owner, , , $rows] = $this->seedLogs();

        $page = Livewire::actingAs($owner)->test(ActivityLogs::class);

        $this->assertNull($page->set('selectedLogId', $rows['view']->id)->instance()->selectedLog);
        $this->assertNotNull($page->set('selectedLogId', $rows['reset']->id)->instance()->selectedLog);
    }

    public function test_owner_dashboard_feed_hides_the_routine_entries_too(): void
    {
        [$owner] = $this->seedLogs();

        $feed = Livewire::actingAs($owner)->test(ActivityFeed::class);

        $activities = $feed->viewData('activities');
        $this->assertFalse($activities->contains(fn ($a) => $a->entity_identifier === 'ROW-SUPPORT-SIGNIN'));
        $this->assertFalse($activities->contains(fn ($a) => $a->entity_identifier === 'ROW-SUPPORT-VIEWAS'));
        $this->assertTrue($activities->contains(fn ($a) => $a->entity_identifier === 'ROW-SUPPORT-RESET'));
    }
}
