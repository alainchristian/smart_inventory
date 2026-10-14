<?php

namespace Tests\Feature\Credit;

use App\Livewire\Owner\Credit\OpeningCredits;
use App\Livewire\Shop\CreditRepayments;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\CustomerShopBalance;
use App\Models\DailySession;
use App\Models\OpeningCredit;
use App\Models\User;
use App\Services\Sales\CustomerCreditLedger;
use App\Services\Sales\OpeningCreditService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

/**
 * Old credit (debts from before the system): typed in or uploaded from Excel,
 * added to the lending shop's balance, customer registered when new.
 * Runs on smart_inventory_test only.
 */
class OpeningCreditTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopA;
    private int $shopB;
    private string $shopAName;
    private User $owner;
    private User $managerA;
    private Customer $known;

    protected function setUp(): void
    {
        parent::setUp();
        $u = uniqid();
        $this->shopAName = "Kigalifootwear — Remera$u";
        $this->shopA = $this->shop($this->shopAName, 'OA' . substr($u, -7));
        $this->shopB = $this->shop("Kigalifootwear — Kimironko$u", 'OB' . substr($u, -7));

        $this->owner    = User::forceCreate(['name' => 'Owner', 'email' => "o$u@example.test", 'password' => bcrypt('x'), 'role' => 'owner', 'is_active' => true]);
        $this->managerA = User::forceCreate([
            'name' => 'Manager A', 'email' => "a$u@example.test", 'password' => bcrypt('x'), 'is_active' => true,
            'role' => 'shop_manager', 'location_type' => 'shop', 'location_id' => $this->shopA,
        ]);
        $this->known = Customer::forceCreate([
            'name' => 'Innocent Mugenzi', 'phone' => $this->phone(),
            'registered_by' => $this->owner->id, 'shop_id' => $this->shopB,
            'total_credit_given' => 0, 'total_repaid' => 0, 'outstanding_balance' => 0,
        ]);
    }

    private function shop(string $name, string $code): int
    {
        $now = now()->toDateTimeString();

        return DB::table('shops')->insertGetId(['name' => $name, 'code' => $code, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
    }

    private function phone(): string
    {
        do {
            $p = '0788' . random_int(100000, 999999);
        } while (Customer::withTrashed()->where('phone', $p)->exists());

        return $p;
    }

    private function service(): OpeningCreditService
    {
        return app(OpeningCreditService::class);
    }

    public function test_a_new_customer_is_registered_and_owes_the_lending_shop(): void
    {
        $phone = $this->phone();

        Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('openForm')
            ->set('phone', '+250 ' . substr($phone, 1))      // typed with the country code
            ->set('name', 'Aline Uwase')
            ->set('shopId', (string) $this->shopA)
            ->set('amount', '45000')
            ->set('takenOn', '2026-03-15')
            ->set('reference', 'Notebook p.12')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $customer = Customer::where('phone', $phone)->firstOrFail();
        $this->assertSame('Aline Uwase', $customer->name);
        $this->assertSame($this->shopA, (int) $customer->shop_id);
        $this->assertSame(45000, $customer->outstanding_balance);
        $this->assertSame(45000, app(CustomerCreditLedger::class)->balanceAt($customer, $this->shopA));
        $this->assertSame(0, app(CustomerCreditLedger::class)->balanceAt($customer, $this->shopB));

        // Dated when it was taken, so overdue alerts count from then
        $this->assertSame('2026-03-15', local_time($customer->last_credit_at)->toDateString());

        $entry = OpeningCredit::where('customer_id', $customer->id)->firstOrFail();
        $this->assertTrue($entry->customer_created);
        $this->assertSame('manual', $entry->source);
        $this->assertSame('Notebook p.12', $entry->reference);

        // No sale behind it
        $this->assertFalse(DB::table('sales')->where('customer_id', $customer->id)->exists());
        $this->assertTrue(ActivityLog::where('action', 'opening_credit_recorded')->where('entity_id', $customer->id)->exists());
    }

    public function test_an_existing_customer_is_matched_by_phone_and_keeps_their_name(): void
    {
        $this->service()->record([
            'phone' => substr($this->known->phone, 1),       // Excel dropped the leading 0
            'name' => 'Someone Else', 'shop' => $this->shopA, 'amount' => '20,000', 'taken_on' => '01/09/2026',
        ], $this->owner, 'manual');

        $this->known->refresh();
        $this->assertSame('Innocent Mugenzi', $this->known->name);
        $this->assertSame(20000, $this->known->outstanding_balance);
        $this->assertSame(1, Customer::where('phone', $this->known->phone)->count());
        $this->assertFalse(OpeningCredit::where('customer_id', $this->known->id)->value('customer_created'));
    }

    public function test_it_is_repaid_at_the_shop_that_gave_it(): void
    {
        $this->service()->record(['phone' => $this->known->phone, 'shop' => $this->shopA, 'amount' => 30000], $this->owner);

        DailySession::forceCreate([
            'shop_id' => $this->shopA, 'session_date' => business_today()->toDateString(), 'status' => 'open',
            'opening_balance' => 0, 'opened_by' => $this->managerA->id, 'opened_at' => now(),
        ]);
        Livewire::actingAs($this->managerA)->test(CreditRepayments::class)
            ->call('selectCustomer', $this->known->id)
            ->set('payAmt_cash', 10000)
            ->call('recordRepayment')
            ->assertHasNoErrors();

        $this->assertSame(20000, $this->known->fresh()->outstanding_balance);
        $this->assertSame(10000, (int) DB::table('credit_repayments')->where('customer_id', $this->known->id)->sum('amount'));
    }

    public function test_back_dated_old_credit_does_not_hide_a_newer_credit_date(): void
    {
        $recent = now()->subDay();
        app(CustomerCreditLedger::class)->extend($this->known, $this->shopA, 5000, $recent);

        $this->service()->record(['phone' => $this->known->phone, 'shop' => $this->shopA, 'amount' => 7000, 'taken_on' => '2025-01-10'], $this->owner);

        $this->assertTrue($this->known->fresh()->last_credit_at->equalTo($recent->startOfSecond()));
        $row = CustomerShopBalance::where('customer_id', $this->known->id)->where('shop_id', $this->shopA)->first();
        $this->assertSame(12000, $row->outstanding_balance);
    }

    public function test_the_form_reports_problems_on_their_fields(): void
    {
        Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('openForm')
            ->set('phone', '12345')
            ->set('amount', '0')
            ->set('takenOn', business_today()->addDay()->toDateString())
            ->call('save')
            ->assertHasErrors(['phone', 'shopId', 'amount', 'takenOn']);

        Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('openForm')
            ->set('phone', $this->phone())
            ->set('shopId', (string) $this->shopA)
            ->set('amount', '5000')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertSame(0, OpeningCredit::whereIn('shop_id', [$this->shopA, $this->shopB])->count());
    }

    public function test_excel_upload_previews_then_adds_the_ready_rows(): void
    {
        $new = $this->phone();
        $file = $this->sheet([
            [$new, 'Claudine Ingabire', 'Remera' . substr($this->shopAName, strlen('Kigalifootwear — Remera')), 15000, '15/08/2026', 'p.3', ''],
            [$this->known->phone, '', $this->shopAName, '25,000', '', '', ''],
            [$new, 'Claudine Ingabire', $this->shopAName, 15000, '15/08/2026', '', ''],      // same as the first row
            [$this->phone(), '', $this->shopAName, 9000, '', '', ''],                         // new customer, no name
            [$this->phone(), 'Eric', 'No Such Shop', 9000, '', '', ''],
        ]);

        $c = Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('openImport')
            ->set('excelFile', $file)
            ->assertHasNoErrors();

        $preview = collect($c->get('preview'))->keyBy('row');
        $this->assertSame([], $preview[2]['errors']);
        $this->assertTrue($preview[2]['is_new_customer']);
        $this->assertSame([], $preview[3]['errors']);
        $this->assertFalse($preview[3]['is_new_customer']);
        $this->assertSame(['Same as row 2.'], $preview[4]['errors']);
        $this->assertSame(['New customer: the name is missing.'], $preview[5]['errors']);
        $this->assertStringContainsString('not found', $preview[6]['errors'][0]);
        $this->assertSame(['ok' => 2, 'amount' => 40000, 'new' => 1, 'problems' => 3], $c->instance()->previewTotals);

        $c->call('confirmImport')->assertSet('showImport', false);

        $this->assertSame(15000, Customer::where('phone', $new)->value('outstanding_balance'));
        $this->assertSame(25000, $this->known->fresh()->outstanding_balance);
        $this->assertSame(2, OpeningCredit::where('shop_id', $this->shopA)->where('source', 'excel')->count());
        $this->assertTrue(ActivityLog::where('action', 'opening_credits_imported')->where('details->entries', 2)->exists());

        // Uploading the same sheet again adds nothing twice
        $again = Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('openImport')->set('excelFile', $this->sheet([[$new, 'Claudine Ingabire', $this->shopAName, 15000, '15/08/2026', '', '']]));
        $this->assertStringStartsWith('Already entered', $again->get('preview')[0]['errors'][0]);
    }

    public function test_a_sheet_without_the_headings_is_refused(): void
    {
        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray([['foo', 'bar'], ['1', '2']]);
        $path = tempnam(sys_get_temp_dir(), 'oc') . '.xlsx';
        IOFactory::createWriter($book, 'Xlsx')->save($path);

        Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('openImport')
            ->set('excelFile', UploadedFile::fake()->createWithContent('x.xlsx', file_get_contents($path)))
            ->assertHasErrors('excelFile')
            ->assertSet('preview', []);
    }

    public function test_an_entry_can_be_removed_until_part_of_it_is_paid(): void
    {
        $entry = $this->service()->record(['phone' => $this->known->phone, 'shop' => $this->shopA, 'amount' => 30000], $this->owner);

        Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('askRemove', $entry->id)
            ->set('removeReason', 'Typed the wrong amount')
            ->call('remove')
            ->assertHasNoErrors();

        $this->assertNotNull($entry->fresh()->removed_at);
        $this->assertSame(0, $this->known->fresh()->outstanding_balance);

        $second = $this->service()->record(['phone' => $this->known->phone, 'shop' => $this->shopA, 'amount' => 30000, 'taken_on' => '2026-01-01'], $this->owner);
        app(CustomerCreditLedger::class)->repay($this->known->fresh(), $this->shopA, 5000);

        Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('askRemove', $second->id)
            ->set('removeReason', 'Mistake')
            ->call('remove')
            ->assertHasErrors('removeReason');
        $this->assertNull($second->fresh()->removed_at);
        $this->assertSame(25000, $this->known->fresh()->outstanding_balance);
    }

    public function test_only_the_owner_can_enter_old_credit(): void
    {
        $this->actingAs($this->managerA)->get(route('owner.credit.opening'))->assertForbidden();

        $this->expectException(\DomainException::class);
        $this->service()->record(['phone' => $this->known->phone, 'shop' => $this->shopA, 'amount' => 1000], $this->managerA);
    }

    public function test_the_template_downloads(): void
    {
        Livewire::actingAs($this->owner)->test(OpeningCredits::class)
            ->call('downloadTemplate')
            ->assertFileDownloaded('old-credit-template.xlsx');
    }

    public function test_phone_amount_and_date_parsing(): void
    {
        $this->assertSame('0788123456', OpeningCreditService::normalisePhone('+250 788 123 456'));
        $this->assertSame('0788123456', OpeningCreditService::normalisePhone(788123456));
        $this->assertSame('0788123456', OpeningCreditService::normalisePhone('078-812-3456'));
        $this->assertNull(OpeningCreditService::normalisePhone('12345'));

        $this->assertSame(12500, OpeningCreditService::parseAmount('12,500 RWF'));
        $this->assertSame(12500, OpeningCreditService::parseAmount(12500.0));
        $this->assertNull(OpeningCreditService::parseAmount('12.5'));
        $this->assertNull(OpeningCreditService::parseAmount('abc'));

        $this->assertSame('2026-03-05', OpeningCreditService::parseDate('05/03/2026'));
        $this->assertSame('2026-03-05', OpeningCreditService::parseDate('5/3/2026'));
        $this->assertSame('2026-03-05', OpeningCreditService::parseDate('2026-03-05'));
        $this->assertSame('2026-03-05', OpeningCreditService::parseDate(46086));    // Excel date number
        $this->assertSame('', OpeningCreditService::parseDate(''));
        $this->assertNull(OpeningCreditService::parseDate('soon'));
    }

    private function sheet(array $rows): UploadedFile
    {
        $book  = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->fromArray(['Phone', 'Customer name', 'Shop', 'Amount (RWF)', 'Date taken', 'Reference', 'Notes'], null, 'A1');
        foreach ($rows as $i => $row) {
            $sheet->setCellValueExplicit('A' . ($i + 2), (string) $row[0], DataType::TYPE_STRING);
            $sheet->fromArray(array_slice($row, 1), null, 'B' . ($i + 2), true);
        }
        $path = tempnam(sys_get_temp_dir(), 'oc') . '.xlsx';
        IOFactory::createWriter($book, 'Xlsx')->save($path);

        return UploadedFile::fake()->createWithContent('old-credit.xlsx', file_get_contents($path));
    }
}
