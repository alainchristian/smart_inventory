<?php

namespace Tests\Feature\Sales;

use App\Livewire\Shop\Sales\UnifiedPos;
use App\Models\DailySession;
use App\Models\HeldSale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The POS polls checkForScans every 2s and checkApprovals every 5s. Each poll
 * used to re-render the whole POS (product grid, cart, modals) even when
 * nothing changed. They must skip the render then, and still render (and
 * toast) when a hold gets approved.
 * Runs on smart_inventory_test only (phpunit.xml + TestCase guard).
 */
class PosPollingTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();

        $this->shopId = DB::table('shops')->insertGetId([
            'name' => "Poll Shop $u", 'code' => 'P' . substr($u, -8), 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->seller = User::forceCreate([
            'name' => 'Poll Seller', 'email' => "poll$u@example.test", 'password' => bcrypt('x'), 'is_active' => true,
            'must_change_password' => false, 'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopId,
        ]);
        DailySession::forceCreate([
            'shop_id' => $this->shopId, 'session_date' => business_today()->toDateString(), 'status' => 'open',
            'opening_balance' => 0, 'opened_by' => $this->seller->id, 'opened_at' => now(),
        ]);
    }

    private function rendered($component): bool
    {
        return isset($component->effects['html']);
    }

    public function test_polls_skip_render_when_nothing_changed(): void
    {
        $pos = Livewire::actingAs($this->seller)->test(UnifiedPos::class);

        $pos->call('checkForScans');
        $this->assertFalse($this->rendered($pos), 'checkForScans re-rendered with no scanner session');

        $pos->call('checkApprovals');
        $this->assertFalse($this->rendered($pos), 'checkApprovals re-rendered with no hold changes');
    }

    public function test_approved_hold_still_renders_and_toasts(): void
    {
        $hold = HeldSale::forceCreate([
            'seller_id' => $this->seller->id, 'shop_id' => $this->shopId, 'hold_reference' => 'HOLD-' . uniqid(),
            'cart_data' => [], 'cart_total' => 5000, 'item_count' => 1, 'needs_price_approval' => true,
        ]);

        $pos = Livewire::actingAs($this->seller)->test(UnifiedPos::class);
        $pos->call('checkApprovals');
        $this->assertFalse($this->rendered($pos));

        $hold->forceFill(['override_approved_at' => now()])->save();

        $pos->call('checkApprovals');
        $this->assertTrue($this->rendered($pos), 'approval must re-render the POS');
        $pos->assertDispatched('notification');
    }
}
