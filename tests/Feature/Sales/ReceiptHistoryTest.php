<?php

namespace Tests\Feature\Sales;

use App\Livewire\Shop\Sales\ReprintSearch;
use App\Models\ProductSellUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Receipt History (shop.receipts): managers only open their own shop's
 * receipts, and the date filter uses business-timezone days.
 */
class ReceiptHistoryTest extends TestCase
{
    use DatabaseTransactions;

    private function shop(): int
    {
        $u = uniqid();

        return DB::table('shops')->insertGetId([
            'name' => "Receipts $u", 'code' => 'H' . substr($u, -8), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function manager(int $shopId): User
    {
        return User::forceCreate([
            'name' => 'Receipt Tester', 'email' => 'rh' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $shopId,
        ]);
    }

    private function sale(int $shopId, User $by, Carbon $at, string $number): int
    {
        return DB::table('sales')->insertGetId([
            'sale_number' => $number, 'shop_id' => $shopId, 'sold_by' => $by->id,
            'sale_date' => $at, 'type' => 'full_box', 'payment_method' => 'cash',
            'subtotal' => 1000, 'total' => 1000, 'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    public function test_manager_cannot_open_another_shops_receipt(): void
    {
        $mine  = $this->shop();
        $other = $this->shop();
        $me    = $this->manager($mine);

        $ownId   = $this->sale($mine, $me, now(), 'RH-OWN-' . uniqid());
        $otherId = $this->sale($other, $this->manager($other), now(), 'RH-OTHER-' . uniqid());

        Livewire::actingAs($me)->test(ReprintSearch::class)
            ->call('viewSale', $otherId)
            ->assertSet('showReceiptModal', false)
            ->call('viewSale', $ownId)
            ->assertSet('showReceiptModal', true)
            ->assertSet('selectedSaleId', $ownId);
    }

    public function test_date_filter_uses_business_days(): void
    {
        $shop = $this->shop();
        $me   = $this->manager($shop);

        // 01:30 in Kigali on 10 Mar = 23:30 UTC on 9 Mar
        $number = 'RH-LATE-' . uniqid();
        $this->sale($shop, $me, Carbon::parse('2020-03-10 01:30', config('tenant.timezone'))->utc(), $number);

        Livewire::actingAs($me)->test(ReprintSearch::class)
            ->set('dateFrom', '2020-03-10')->set('dateTo', '2020-03-10')
            ->assertSee($number)
            ->set('dateFrom', '2020-03-09')->set('dateTo', '2020-03-09')
            ->assertDontSee($number);
    }

    public function test_pack_names_with_numbers_are_not_pluralised(): void
    {
        $this->assertSame('3 × Pack of 3', ProductSellUnit::quantityLabel(3, false, 'Pack of 3'));
        $this->assertSame('3 Dozen', ProductSellUnit::quantityLabel(3, false, 'Dozen'));
        $this->assertSame('2 Pairs', ProductSellUnit::quantityLabel(2, false, 'Pair'));
    }
}
