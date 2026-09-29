<?php

namespace Tests\Feature\Layout;

use App\Livewire\Layout\NotificationBell;
use App\Models\HeldSale;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The bell used to live inside Topbar, whose 15s poll re-rendered the whole
 * topbar with both notification lists (~92 KB for the owner). As its own
 * component it renders just the badge while closed and the lists once opened.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class NotificationBellTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $owner;
    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $u = uniqid();
        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Bell Shop $u", 'code' => 'BL' . substr($u, -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->owner = User::forceCreate([
            'name' => 'Bell Owner', 'email' => "bo$u@example.test", 'password' => bcrypt('x'),
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
        $this->seller = User::forceCreate([
            'name' => 'Bell Seller', 'email' => "bs$u@example.test", 'password' => bcrypt('x'), 'is_active' => true,
            'must_change_password' => false, 'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);
    }

    private function sellerActivity(string $label): void
    {
        AuditLogger::log([
            'actor' => $this->seller, 'action' => 'sale_created', 'module' => 'sales',
            'entity_type' => 'Sale', 'entity_id' => 1, 'entity_identifier' => $label,
        ]);
    }

    public function test_closed_bell_renders_only_the_badge(): void
    {
        $this->sellerActivity('SALE-BELL-1');

        $bell = Livewire::actingAs($this->owner)->test(NotificationBell::class);

        $this->assertGreaterThan(0, $bell->get('unreadNotificationsCount'));
        $bell->assertDontSee('SALE-BELL-1')      // list not rendered while closed
             ->assertDontSee('No recent activity');
        $this->assertLessThan(8000, strlen($bell->html()), 'closed bell should be small');
    }

    public function test_opening_renders_lists_and_marks_read(): void
    {
        $this->sellerActivity('SALE-BELL-2');

        $bell = Livewire::actingAs($this->owner)->test(NotificationBell::class)
            ->call('openPanel')
            ->assertSee('SALE-BELL-2');

        $this->assertNotNull($this->owner->fresh()->notifications_read_at);
        $this->assertSame(0, $bell->get('unreadActivityCount'));

        // Closing doesn't re-render (the dropdown is already hidden)
        $bell->call('closePanel');
        $this->assertFalse(isset($bell->effects['html']));
    }

    public function test_seller_sees_decision_on_own_hold(): void
    {
        $hold = HeldSale::forceCreate([
            'seller_id' => $this->seller->id, 'shop_id' => $this->shopId, 'hold_reference' => 'HOLD-BELL-' . uniqid(),
            'cart_data' => [], 'cart_total' => 5000, 'item_count' => 1, 'needs_price_approval' => true,
        ]);
        AuditLogger::log([
            'actor' => $this->owner, 'action' => 'held_sale_rejected', 'module' => 'sales',
            'entity_type' => 'HeldSale', 'entity_id' => $hold->id, 'entity_identifier' => $hold->hold_reference,
        ]);

        Livewire::actingAs($this->seller)->test(NotificationBell::class)
            ->call('openPanel')
            ->assertSee('Price Override Rejected')
            ->assertSee($hold->hold_reference);
    }
}
