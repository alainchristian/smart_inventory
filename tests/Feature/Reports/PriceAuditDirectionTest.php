<?php

namespace Tests\Feature\Reports;

use App\Livewire\Owner\Reports\SalesAnalytics;
use App\Models\User;
use App\Services\Analytics\SalesAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Price overrides go both ways: below list (discount) or above list (markup).
 * The Price Audit trail and its KPIs must keep them apart, and only a
 * discount beyond the threshold may ever need owner approval (same rule as
 * UnifiedPos: pct = (list − actual) / list > threshold).
 */
class PriceAuditDirectionTest extends TestCase
{
    use DatabaseTransactions;

    private const DAY = '2020-03-10';

    private int $shopId;
    private int $sellerId;
    private int $productId;

    private function insert(string $table, array $row): int
    {
        $now = now()->toDateTimeString();

        return DB::table($table)->insertGetId($row + ['created_at' => $now, 'updated_at' => $now]);
    }

    private function sale(string $number): int
    {
        return $this->insert('sales', [
            'sale_number' => $number . '-' . uniqid(), 'shop_id' => $this->shopId, 'sold_by' => $this->sellerId,
            'sale_date' => Carbon::parse(self::DAY . ' 12:00:00', config('tenant.timezone'))->utc()->toDateTimeString(),
            'type' => 'full_box', 'payment_method' => 'cash', 'subtotal' => 0, 'total' => 0,
            'has_price_override' => true,
        ]);
    }

    private function line(int $saleId, array $row): void
    {
        $this->insert('sale_items', $row + ['sale_id' => $saleId, 'product_id' => $this->productId, 'price_was_modified' => true]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $u = uniqid();
        $this->shopId   = $this->insert('shops', ['name' => "Audit Shop $u", 'code' => 'A' . substr($u, -8), 'is_active' => true]);
        $this->sellerId = $this->insert('users', ['name' => 'Seller', 'email' => "s$u@example.test", 'password' => 'x', 'role' => 'shop_manager']);
        $cat = $this->insert('categories', ['name' => "Cat $u", 'code' => 'C' . substr($u, -8)]);
        $this->productId = $this->insert('products', [
            'sku' => "SKU$u", 'name' => "Prod $u", 'items_per_box' => 24, 'category_id' => $cat,
            'purchase_price' => 1000, 'selling_price' => 2500,
        ]);

        // D: full box sold 30% below list (box prices): 60,000 → 42,000
        $this->line($this->sale('D'), [
            'quantity_sold' => 24, 'is_full_box' => true,
            'original_unit_price' => 60000, 'actual_unit_price' => 42000, 'line_total' => 42000,
        ]);
        // M: 4 single pieces sold 60% above list: 2,500 → 4,000
        $this->line($this->sale('M'), [
            'quantity_sold' => 4, 'is_full_box' => false,
            'original_unit_price' => 2500, 'actual_unit_price' => 4000, 'line_total' => 16000,
        ]);
        // P: 2 dozen (24 pieces), per-pack prices 28,000 → 25,000 (below list)
        $this->line($this->sale('P'), [
            'quantity_sold' => 24, 'is_full_box' => false, 'sell_unit_name' => 'Dozen', 'sell_unit_size' => 12,
            'original_unit_price' => 28000, 'actual_unit_price' => 25000, 'line_total' => 50000,
        ]);
        // X: one sale, same product — a discount line and a markup line
        $x = $this->sale('X');
        $this->line($x, [
            'quantity_sold' => 2, 'is_full_box' => false,
            'original_unit_price' => 2500, 'actual_unit_price' => 2000, 'line_total' => 4000,
        ]);
        $this->line($x, [
            'quantity_sold' => 1, 'is_full_box' => false,
            'original_unit_price' => 2500, 'actual_unit_price' => 3000, 'line_total' => 3000,
        ]);
    }

    private function rows(): array
    {
        $log = app(SalesAnalyticsService::class)->getPriceAuditLog(self::DAY, self::DAY, 'shop:' . $this->shopId);

        return collect($log)->keyBy(fn ($e) => explode('-', $e['sale_number'])[0])->all();
    }

    public function test_audit_rows_are_signed_by_direction(): void
    {
        $r = $this->rows();

        // Discount: positive total_discount / discount_pct, negative price_change
        $this->assertSame(18000, $r['D']['total_discount']);
        $this->assertSame(30.0, (float) $r['D']['discount_pct']);
        $this->assertSame(-18000, $r['D']['price_change']);
        $this->assertSame('discount', $r['D']['direction']);
        $this->assertSame(18000, $r['D']['discount_amount']);
        $this->assertSame(0, $r['D']['markup_amount']);

        // Markup: negative total_discount, positive price_change, never over threshold
        $this->assertSame(-6000, $r['M']['total_discount']);
        $this->assertSame(6000, $r['M']['price_change']);
        $this->assertSame('markup', $r['M']['direction']);
        $this->assertSame(6000, $r['M']['markup_amount']);
        $this->assertSame(0, $r['M']['discount_amount']);
        $this->assertLessThan(0, $r['M']['max_discount_pct']);
        $this->assertSame(60.0, (float) $r['M']['change_pct']);  // 6,000 over a 10,000 list total

        // Pack line: per-pack prices × 2 packs
        $this->assertSame(6000, $r['P']['total_discount']);
        $this->assertSame('discount', $r['P']['direction']);

        // Mixed row: 1,000 below + 500 above, net 500 below list
        $this->assertSame('mixed', $r['X']['direction']);
        $this->assertSame(1000, $r['X']['discount_amount']);
        $this->assertSame(500, $r['X']['markup_amount']);
        $this->assertSame(500, $r['X']['total_discount']);
        $this->assertSame(20.0, (float) $r['X']['max_discount_pct']);
    }

    public function test_stats_keep_discounts_and_markups_apart(): void
    {
        $s = app(SalesAnalyticsService::class)->getPriceOverrideStats(self::DAY, self::DAY, 'shop:' . $this->shopId);

        $this->assertSame(5, $s['override_items_count']);
        $this->assertSame(3, $s['discount_items_count']);
        $this->assertSame(2, $s['markup_items_count']);
        $this->assertSame(18000 + 6000 + 1000, $s['total_discount_given']);   // markups don't offset it
        $this->assertSame(6000 + 500, $s['total_markup_given']);
    }

    public function test_only_discounts_over_threshold_are_pending(): void
    {
        $owner = User::where('role', 'owner')->first()
            ?? User::forceCreate(['name' => 'Owner', 'email' => uniqid('o') . '@example.test', 'password' => 'x', 'role' => 'owner']);

        app(\App\Services\SettingsService::class)->set('price_override_threshold', 20);

        $c = Livewire::actingAs($owner)->test(SalesAnalytics::class)
            ->set('dateFrom', self::DAY)->set('dateTo', self::DAY)
            ->set('locationFilter', 'shop:' . $this->shopId)
            ->set('activeTab', 'audit');

        // Threshold 20%: only D (30% below list) needs action. M is 60% ABOVE list.
        $pending = $c->instance()->pendingPriceApprovals;
        $this->assertSame(1, $pending['count']);
        $this->assertSame(18000, $pending['total_discount']);

        $c->assertSee('Price change')
          ->assertSee('30% below list')
          ->assertSee('60% above list')
          ->assertSee('Markup — No Action Needed')
          ->assertSee('+6,000')
          ->assertSee('−18,000')
          ->assertDontSee('% off');
    }
}
