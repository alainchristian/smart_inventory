<?php

namespace Tests\Feature\Audit;

use App\Livewire\Owner\ActivityLogs;
use App\Livewire\Owner\Customers\CustomerList;
use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Returns\ReturnService;
use App\Services\Sales\CustomerService;
use App\Support\ActivityPresenter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Audit entries say where they happened, read like sentences, and cover customers and returns properly. */
class AuditDetailTest extends TestCase
{
    use DatabaseTransactions;

    private function shop(string $name): Shop
    {
        return Shop::forceCreate(['name' => $name, 'code' => 'T' . substr(uniqid(), -7), 'is_active' => true, 'sells_all_categories' => true]);
    }

    private function user(string $role, ?Shop $shop = null, array $extra = []): User
    {
        return User::forceCreate(array_merge([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
            'location_type' => $shop ? 'shop' : null, 'location_id' => $shop?->id,
        ], $extra));
    }

    private function log(User $actor, array $extra = []): ActivityLog
    {
        return AuditLogger::log(array_merge([
            'actor' => $actor, 'action' => 'created', 'module' => 'expense',
            'entity_type' => 'Expense', 'entity_identifier' => 'EXP-' . uniqid(),
        ], $extra));
    }

    // ── Where ────────────────────────────────────────────────────────────────

    public function test_location_comes_from_the_entry_then_from_the_actor(): void
    {
        $remera = $this->shop('Remera');
        $kimi   = $this->shop('Kimironko');
        $mgr    = $this->user('shop_manager', $remera);
        $owner  = $this->user('owner');

        $own = $this->log($mgr);
        $this->assertSame(['shop', $remera->id, 'Remera'], [$own->location_type, $own->location_id, $own->location_name]);

        // An entry that names another shop wins over where the actor sits
        $other = $this->log($mgr, ['details' => ['shop_id' => $kimi->id]]);
        $this->assertSame([$kimi->id, 'Kimironko'], [$other->location_id, $other->location_name]);

        // An owner has no place of their own...
        $this->assertNull($this->log($owner)->location_id);
        // ...unless the entry says which shop it was about
        $this->assertSame('Kimironko', $this->log($owner, ['new_values' => ['shop_id' => $kimi->id]])->location_name);

        // An explicit location beats everything
        $explicit = $this->log($mgr, ['location_type' => 'shop', 'location_id' => $kimi->id]);
        $this->assertSame('Kimironko', $explicit->location_name);
    }

    public function test_direct_writes_also_get_a_role_and_a_place(): void
    {
        $shop = $this->shop('Remera');
        $mgr  = $this->user('shop_manager', $shop);

        // The many older call sites that write ActivityLog directly
        $row = ActivityLog::create([
            'user_id' => $mgr->id, 'user_name' => $mgr->name, 'action' => 'sale_created',
            'entity_type' => 'Sale', 'entity_id' => 1, 'entity_identifier' => 'S-1',
        ]);

        $this->assertSame('shop_manager', $row->actor_role_snapshot);
        $this->assertSame('Remera', $row->location_name);
    }

    public function test_location_filter_includes_older_entries_by_where_the_person_is_assigned(): void
    {
        $remera = $this->shop('Remera');
        $kimi   = $this->shop('Kimironko');
        $owner  = $this->user('owner');
        $mgrR   = $this->user('shop_manager', $remera);
        $mgrK   = $this->user('shop_manager', $kimi);

        $this->log($mgrR, ['entity_identifier' => 'NEW-REMERA']);
        $this->log($mgrK, ['entity_identifier' => 'NEW-KIMI']);
        // An entry from before locations were stored: no location columns at all
        DB::table('activity_logs')->insert([
            'user_id' => $mgrR->id, 'user_name' => $mgrR->name, 'action' => 'created',
            'entity_type' => 'Expense', 'entity_identifier' => 'OLD-REMERA', 'created_at' => now(),
        ]);

        Livewire::actingAs($owner)->test(ActivityLogs::class)
            ->set('filterLocation', "shop:{$remera->id}")
            ->assertSee('NEW-REMERA')->assertSee('OLD-REMERA')
            ->assertDontSee('NEW-KIMI');
    }

    // ── Sentences and rows ───────────────────────────────────────────────────

    public function test_summaries_read_like_sentences(): void
    {
        $shop = $this->shop('Remera');
        $mgr  = $this->user('shop_manager', $shop, ['name' => 'Jean']);
        $p    = new ActivityPresenter();

        $sale = $this->log($mgr, ['action' => 'mixed_sale_created', 'entity_type' => 'Sale', 'entity_identifier' => 'SALE-9', 'details' => ['total' => 120000]]);
        $this->assertSame('Jean made sale SALE-9 for 120,000 RWF at Remera', $p->summary($sale));

        $rep = $this->log($mgr, ['action' => 'credit_repayment_recorded', 'entity_type' => 'Customer', 'entity_identifier' => 'Aline', 'details' => ['amount' => 5000, 'new_balance' => 15000]]);
        $this->assertSame('Jean recorded a repayment of 5,000 RWF (the customer now owes 15,000 RWF) at Remera', $p->summary($rep));

        $ret = $this->log($mgr, ['action' => 'processed', 'entity_type' => 'ReturnModel', 'entity_identifier' => 'RET-1',
            'new_values' => ['refund_amount' => 8000, 'is_exchange' => false], 'details' => ['refund_method' => 'mobile_money']]);
        $this->assertSame('Jean processed return RET-1, refunding 8,000 RWF by Mobile money at Remera', $p->summary($ret));

        $exp = $this->log($mgr, ['action' => 'created', 'entity_type' => 'Expense', 'entity_identifier' => 'Transport', 'new_values' => ['amount' => 3000]]);
        $this->assertSame('Jean created expense Transport for 3,000 RWF at Remera', $p->summary($exp));

        $upd = $this->log($mgr, ['action' => 'updated', 'entity_type' => 'Product', 'entity_identifier' => 'Air Max',
            'old_values' => ['selling_price' => 3000, 'name' => 'Air Max'], 'new_values' => ['selling_price' => 3500, 'name' => 'Air Max', 'updated_at' => 'x']]);
        $this->assertSame('Jean changed product Air Max (selling price) at Remera', $p->summary($upd));

        $support = $this->user('super_admin');
        $reset = $this->log($support, ['action' => 'support_password_reset', 'entity_type' => 'User', 'entity_identifier' => 'a@b.test']);
        $this->assertSame('Platform support reset the password of a@b.test', $p->summary($reset));

        // Anything unknown still gets a sensible line
        $other = $this->log($mgr, ['action' => 'transfer_approved', 'entity_type' => 'Transfer', 'entity_identifier' => 'TRF-3']);
        $this->assertSame('Jean: Transfer Approved TRF-3 at Remera', $p->summary($other));
    }

    public function test_rows_show_names_money_and_hide_bookkeeping(): void
    {
        $shop = $this->shop('Remera');
        $user = $this->user('owner', null, ['name' => 'Olivia']);
        $cust = \App\Models\Customer::forceCreate(['name' => 'Aline', 'phone' => '07' . random_int(10000000, 99999999), 'registered_by' => $user->id]);

        $rows = collect((new ActivityPresenter())->rows([
            'id' => 5, 'updated_at' => 'x',
            'shop_id' => $shop->id, 'customer_id' => $cust->id, 'approved_by' => $user->id,
            'refund_amount' => 8000, 'items_count' => 3, 'is_exchange' => false, 'reason' => 'customer_request',
            'approved_at' => '2026-10-23T10:30:00+00:00',
            'products' => [['product' => 'Air Max', 'returned' => 2, 'damaged' => 0], ['product' => 'Soap', 'returned' => 1, 'damaged' => 1]],
        ]))->pluck('value', 'label');

        $this->assertFalse($rows->has('Id') || $rows->has('Updated at'));
        $this->assertSame('Remera', $rows['Shop']);
        $this->assertSame('Aline', $rows['Customer']);
        $this->assertSame('Olivia', $rows['Approved by']);
        $this->assertSame('8,000 RWF', $rows['Refund amount']);
        $this->assertSame('3', $rows['Items count']);                // a count is not money
        $this->assertSame('No', $rows['Is exchange']);
        $this->assertSame('Customer request', $rows['Reason']);
        $this->assertSame('Product: Air Max, Returned: 2, Damaged: 0; Product: Soap, Returned: 1, Damaged: 1', $rows['Products']);
        $this->assertMatchesRegularExpression('/^\d{2} \w{3} 2026 \d{2}:\d{2}$/', $rows['Approved at']);
    }

    public function test_changes_show_before_and_after_in_the_same_form(): void
    {
        $shopA = $this->shop('Remera');
        $shopB = $this->shop('Kimironko');
        $mgr   = $this->user('owner');

        $log = $this->log($mgr, ['action' => 'updated', 'entity_type' => 'Customer', 'entity_identifier' => 'Aline',
            'old_values' => ['shop_id' => $shopA->id, 'phone' => '0788000001'], 'new_values' => ['shop_id' => $shopB->id, 'phone' => '0788000002']]);

        $changes = collect((new ActivityPresenter())->changes($log))->keyBy('label');

        $this->assertSame(['Remera', 'Kimironko'], [$changes['Shop']['old'], $changes['Shop']['new']]);
        $this->assertSame(['0788000001', '0788000002'], [$changes['Phone']['old'], $changes['Phone']['new']]);
    }

    public function test_an_older_entry_whose_details_is_a_sentence_still_shows(): void
    {
        $owner = $this->user('owner');
        $mgr   = $this->user('shop_manager', $this->shop('Remera'));

        // fulfillment_confirmed used to store a plain sentence in `details`
        $log = $this->log($mgr, ['action' => 'fulfillment_confirmed', 'entity_type' => 'Sale', 'entity_identifier' => 'S-7',
            'details' => 'Fulfillment confirmed at Main warehouse — handed to Eric (via queue)']);

        $this->assertSame([['label' => 'Note', 'value' => 'Fulfillment confirmed at Main warehouse — handed to Eric (via queue)']],
            (new ActivityPresenter())->detailRows($log->fresh()));

        Livewire::actingAs($owner)->test(ActivityLogs::class)
            ->call('openDrawer', $log->id)
            ->assertSee('handed to Eric')
            ->assertSee('What happened');
    }

    public function test_record_link_for_the_owner_but_never_for_support(): void
    {
        $owner   = $this->user('owner');
        $support = $this->user('super_admin');
        $log     = $this->log($owner, ['action' => 'sale_voided', 'entity_type' => 'Sale', 'entity_id' => 42, 'entity_identifier' => 'S-42']);
        $p       = new ActivityPresenter();

        $this->assertSame(['url' => route('owner.sales.show', 42), 'label' => 'Open sale'], $p->recordLink($log, $owner));
        $this->assertNull($p->recordLink($log, $support));
    }

    public function test_the_detail_panel_shows_the_sentence_the_place_and_readable_values(): void
    {
        $shop  = $this->shop('Remera');
        $owner = $this->user('owner');
        $mgr   = $this->user('shop_manager', $shop, ['name' => 'Jean']);
        $log   = $this->log($mgr, ['action' => 'sale_voided', 'entity_type' => 'Sale', 'entity_id' => 1, 'entity_identifier' => 'S-1',
            'details' => ['shop_id' => $shop->id, 'reason' => 'Wrong size', 'total' => 45000, 'credit_reversed' => 0]]);

        Livewire::actingAs($owner)->test(ActivityLogs::class)
            ->call('openDrawer', $log->id)
            ->assertSee('Jean voided sale S-1 (Wrong size) at Remera')
            ->assertSee('45,000 RWF')
            ->assertSee('Where')
            ->assertSee('Open sale');
    }

    // ── Customers ────────────────────────────────────────────────────────────

    public function test_registering_a_customer_is_logged_with_the_shop(): void
    {
        $shop = $this->shop('Remera');
        $mgr  = $this->user('shop_manager', $shop);
        $this->actingAs($mgr);

        $customer = app(CustomerService::class)->create(['name' => 'Aline', 'phone' => '0788123456'], $shop->id);

        $log = ActivityLog::where('action', 'customer_registered')->where('entity_id', $customer->id)->firstOrFail();
        $this->assertSame('Aline', $log->entity_identifier);
        $this->assertSame('0788123456', $log->new_values['phone']);
        $this->assertSame('Remera', $log->location_name);
        $this->assertSame($mgr->id, $log->user_id);
    }

    public function test_editing_a_customer_logs_only_what_changed_and_flags_a_new_phone(): void
    {
        $owner = $this->user('owner');
        $customer = \App\Models\Customer::forceCreate(['name' => 'Aline', 'phone' => '0788000001', 'email' => null, 'registered_by' => $owner->id]);

        // Nothing changed: nothing logged
        Livewire::actingAs($owner)->test(CustomerList::class)
            ->call('openEdit', $customer->id)->call('save')->assertHasNoErrors();
        $this->assertSame(0, ActivityLog::where('action', 'customer_updated')->where('entity_id', $customer->id)->count());

        // Change the phone and the name
        Livewire::actingAs($owner)->test(CustomerList::class)
            ->call('openEdit', $customer->id)
            ->set('form_phone', '0788000002')->set('form_name', 'Aline M.')
            ->call('save')->assertHasNoErrors();

        $log = ActivityLog::where('action', 'customer_updated')->where('entity_id', $customer->id)->firstOrFail();
        $this->assertSame(['name' => 'Aline', 'phone' => '0788000001'], $log->old_values);
        $this->assertSame(['name' => 'Aline M.', 'phone' => '0788000002'], $log->new_values);
        $this->assertSame('warning', $log->severity);
    }

    // ── Returns ──────────────────────────────────────────────────────────────

    public function test_a_return_is_logged_with_sale_method_customer_and_products(): void
    {
        $shop = $this->shop('Remera');
        $mgr  = $this->user('shop_manager', $shop);
        $now  = now();
        $categoryId = DB::table('categories')->insertGetId(['name' => 'Cat ' . uniqid(), 'code' => 'C' . substr(uniqid(), -8), 'created_at' => $now, 'updated_at' => $now]);
        $productId = DB::table('products')->insertGetId([
            'sku' => 'SKU' . uniqid(), 'name' => 'Air Max', 'items_per_box' => 10, 'is_active' => true, 'category_id' => $categoryId,
            'purchase_price' => 1000, 'selling_price' => 3000, 'box_selling_price' => 28000, 'created_at' => $now, 'updated_at' => $now,
        ]);

        // The stock-moving parts are not what is under test: the real return and its audit entry are
        $service = new class extends ReturnService {
            protected function returnItemsToInventory(...$args): void {}
            protected function createDamagedGoodsRecord(...$args): void {}
        };

        $return = $service->processReturn([
            'shop_id' => $shop->id, 'reason' => 'customer_request', 'refund_method' => 'cash',
            'customer_name' => 'Aline', 'customer_phone' => '0788123456',
            'estimated_refund' => 6000,
            'items' => [['product_id' => $productId, 'quantity_returned' => 2, 'quantity_damaged' => 1, 'unit_price' => 3000]],
        ], $mgr);

        $log = ActivityLog::where('action', 'processed')->where('entity_id', $return->id)->firstOrFail();

        $this->assertSame('returns', $log->module);
        $this->assertSame($mgr->id, $log->user_id);
        $this->assertSame('Remera', $log->location_name);
        $this->assertSame('cash', $log->details['refund_method']);
        $this->assertSame('Aline · 0788123456', $log->details['customer']);
        $this->assertSame([['product' => 'Air Max', 'returned' => 2, 'damaged' => 1]], $log->details['products']);
        $this->assertSame(6000, $log->new_values['refund_amount']);
        $this->assertStringContainsString('refunding 6,000 RWF by Cash', (new ActivityPresenter())->summary($log));
    }
}
