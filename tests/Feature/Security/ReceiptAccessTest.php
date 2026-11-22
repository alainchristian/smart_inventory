<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Receipts / picking slips: who may open a sale, and what warehouse staff see.
 * Shop and warehouse ids are separate sequences, so "location_id equals the
 * sale's shop_id" must never be enough for a warehouse manager.
 */
class ReceiptAccessTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopId;
    private int $warehouseId;
    private int $otherWarehouseId;
    private int $saleId;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = substr(uniqid(), -8);
        $now = now()->toDateTimeString();

        $this->shopId           = DB::table('shops')->insertGetId(['name' => "Shop $u", 'code' => "S$u", 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $this->warehouseId      = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => $now, 'updated_at' => $now]);
        $this->otherWarehouseId = DB::table('warehouses')->insertGetId(['name' => "WH2 $u", 'code' => "X$u", 'created_at' => $now, 'updated_at' => $now]);

        $seller = $this->user('shop_manager', 'shop', $this->shopId);

        $this->saleId = DB::table('sales')->insertGetId([
            'sale_number' => 'WS-' . strtoupper($u), 'shop_id' => $this->shopId, 'sold_by' => $seller->id,
            'sale_date' => $now, 'type' => 'full_box', 'payment_method' => 'cash', 'total' => 90000, 'subtotal' => 90000,
            'customer_name' => 'Aline Uwase', 'fulfillment_type' => 'warehouse_direct', 'fulfillment_status' => 'pending',
            'fulfillment_method' => 'pickup', 'source_warehouse_id' => $this->warehouseId, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function user(string $role, ?string $type, ?int $locationId): User
    {
        return User::forceCreate([
            'name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test', 'password' => bcrypt('password-123'),
            'role' => $role, 'location_type' => $type, 'location_id' => $locationId,
            'is_active' => true, 'must_change_password' => false,
        ]);
    }

    private function slip(User $user, string $query = '')
    {
        return $this->actingAs($user)->get(route('warehouse.sales.fulfillment.picking-slip', $this->saleId) . $query);
    }

    public function test_warehouse_manager_whose_id_matches_the_shop_id_cannot_open_the_sale(): void
    {
        // A warehouse manager of ANOTHER warehouse whose location_id happens to equal the shop's id.
        $stranger = $this->user('warehouse_manager', 'warehouse', $this->shopId);

        $this->slip($stranger, '?full=1')->assertForbidden();
    }

    public function test_warehouse_manager_of_a_different_warehouse_is_refused(): void
    {
        $other = $this->user('warehouse_manager', 'warehouse', $this->otherWarehouseId);

        $this->assertSame(403, $this->actingAs($other)->getJson(route('warehouse.sales.fulfillment.picking-slip', $this->saleId))->getStatusCode());
    }

    public function test_the_right_warehouse_manager_never_sees_amounts_even_with_full(): void
    {
        $manager = $this->user('warehouse_manager', 'warehouse', $this->warehouseId);

        $this->slip($manager)->assertOk()->assertViewHas('hideAmounts', true);
        $this->slip($manager, '?full=1')->assertOk()->assertViewHas('hideAmounts', true);
    }

    public function test_shop_manager_of_the_sale_sees_the_full_receipt(): void
    {
        $manager = $this->user('shop_manager', 'shop', $this->shopId);

        $this->actingAs($manager)->get(route('shop.receipt.print', $this->saleId))
            ->assertOk()->assertViewHas('hideAmounts', false);
    }

    public function test_shop_manager_of_another_shop_is_refused(): void
    {
        $now = now()->toDateTimeString();
        $otherShop = DB::table('shops')->insertGetId(['name' => 'Other ' . uniqid(), 'code' => 'O' . substr(uniqid(), -8), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $manager = $this->user('shop_manager', 'shop', $otherShop);

        $this->assertSame(403, $this->actingAs($manager)->getJson(route('shop.receipt.print', $this->saleId))->getStatusCode());
    }
}
