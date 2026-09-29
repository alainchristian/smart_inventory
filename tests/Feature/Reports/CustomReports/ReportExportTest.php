<?php

namespace Tests\Feature\Reports\CustomReports;

use App\Models\ActivityLog;
use App\Models\ReportRunHistory;
use App\Models\SavedReport;
use App\Models\User;
use App\Services\Reports\ReportDocument;
use App\Services\Reports\ReportExporter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/** Custom report PDF / Excel / CSV downloads (phase 6) */
class ReportExportTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::forceCreate([
            'name' => 'Report Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
    }

    private function report(array $attrs = []): SavedReport
    {
        return SavedReport::create(array_merge([
            'name'       => 'Monthly pack: =cmd',
            'created_by' => $this->owner->id,
            'config'     => [
                'date_range' => 'month', 'comparison_mode' => 'prior_period',
                'blocks' => [
                    ['id' => 'k1', 'metric_id' => 'sales_revenue', 'title' => 'Revenue', 'viz' => 'kpi_card'],
                    ['id' => 't1', 'metric_id' => 'sales_revenue', 'title' => 'Revenue table', 'viz' => 'table'],
                    ['id' => 'c1', 'metric_id' => 'sales_revenue_trend', 'title' => 'Daily revenue', 'viz' => 'line_chart'],
                    ['id' => 'x1', 'metric_id' => 'text_block', 'title' => 'Commentary', 'viz' => 'text', 'content' => "Line one\nLine two"],
                ],
            ],
        ], $attrs));
    }

    private function url(SavedReport $r, string $format, array $q = []): string
    {
        return route('owner.reports.custom.export', [$r, $format]) . ($q ? '?' . http_build_query($q) : '');
    }

    public function test_pdf_download(): void
    {
        $report = $this->report();
        $res = $this->actingAs($this->owner)->get($this->url($report, 'pdf', ['date_range' => 'custom', 'date_from' => '2026-03-01', 'date_to' => '2026-03-15']));

        $res->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $res->getContent());
        $this->assertStringContainsString('monthly-pack-cmd_2026-03-01_to_2026-03-15.pdf', $res->headers->get('Content-Disposition'));

        // The template itself: header, period, blocks
        $doc  = new ReportDocument($report, ['date_range' => 'custom', 'date_from' => '2026-03-01', 'date_to' => '2026-03-15'], $this->owner);
        $html = view('pdf.custom-report', ['doc' => $doc, 'tenant' => config('tenant.name')])->render();
        foreach (['Monthly pack: =cmd', '1 – 15 Mar 2026', 'All locations', '14 – 28 Feb 2026', 'Report Owner', 'Revenue table', 'Daily revenue', 'Line one<br />'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_excel_workbook_has_summary_and_table_sheets(): void
    {
        $report = $this->report();
        $res = $this->actingAs($this->owner)->get($this->url($report, 'xlsx'));
        $res->assertOk()->assertHeader('Content-Type', ReportExporter::FORMATS['xlsx']);

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $res->getContent());
        $book = IOFactory::load($path);
        unlink($path);

        $this->assertSame(['Summary', 'Revenue table', 'Daily revenue'], $book->getSheetNames(), implode(' | ', $book->getSheetNames()));

        $summary = $book->getSheetByName('Summary');
        $this->assertSame('Monthly pack: =cmd', $summary->getCell('A1')->getValue());

        $table = $book->getSheetByName('Revenue table');
        $this->assertSame(['Period', 'Sales', 'Revenue (RWF)'], [$table->getCell('A1')->getValue(), $table->getCell('B1')->getValue(), $table->getCell('C1')->getValue()]);
        $this->assertIsNumeric($table->getCell('C3')->getValue());
        $this->assertSame('#,##0', $table->getStyle('C3')->getNumberFormat()->getFormatCode());
        $this->assertSame(0 + $table->getCell('C3')->getValue(), $table->getCell('C3')->getValue(), 'zeros stay numbers, not blanks');

        $trend = $book->getSheetByName('Daily revenue');
        $last  = $trend->getHighestRow();
        $this->assertSame('Total', $trend->getCell("A{$last}")->getValue());
        $this->assertIsNumeric($trend->getCell("C{$last}")->getValue());
        $this->assertSame('d mmm yyyy', $trend->getStyle('A2')->getNumberFormat()->getFormatCode());
        $this->assertIsNumeric($trend->getCell('A2')->getValue(), 'dates are real Excel dates');
    }

    public function test_csv_download_is_safe_utf8(): void
    {
        $report = $this->report();
        $res = $this->actingAs($this->owner)->get($this->url($report, 'csv'));
        $res->assertOk();

        $csv = $res->getContent();
        $this->assertStringStartsWith("\u{FEFF}", $csv);
        $this->assertStringContainsString('"Monthly pack: =cmd"', $csv);          // not at the start: harmless
        $this->assertStringContainsString('"Date","Sales","Revenue (RWF)"', $csv); // comparison on used to crash here
    }

    public function test_exports_follow_filters_reuse_the_cache_and_are_logged(): void
    {
        $report = $this->report();
        $q = ['date_range' => 'custom', 'date_from' => '2026-02-01', 'date_to' => '2026-02-10', 'location_filter' => 'all', 'comparison_mode' => 'none'];

        $this->actingAs($this->owner)->get($this->url($report, 'csv', $q))->assertOk();
        $this->actingAs($this->owner)->get($this->url($report, 'xlsx', $q))->assertOk();

        $this->assertSame(0, ReportRunHistory::where('report_id', $report->id)->count(), 'downloads are not report runs');
        $log = ActivityLog::where('action', 'report_csv_downloaded')->where('entity_id', $report->id)->latest('id')->firstOrFail();
        $this->assertSame('2026-02-01', $log->details['date_from']);
        $this->assertTrue(ActivityLog::where('action', 'report_xlsx_downloaded')->where('entity_id', $report->id)->exists());
    }

    public function test_access_and_unknown_formats(): void
    {
        $report = $this->report();
        $other = User::forceCreate([
            'name' => 'Other', 'email' => 'x' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);

        $this->actingAs($other)->get($this->url($report, 'pdf'))->assertForbidden();
        $this->actingAs($this->owner)->get(route('owner.reports.custom.view', $report) . '/export/docx')->assertNotFound();

        $this->actingAs($this->owner)->get(route('owner.reports.custom.print', $report) . '?date_from=2026-03-01')
            ->assertRedirect($this->url($report, 'pdf', ['date_from' => '2026-03-01']));
    }

    public function test_sheet_names_are_valid_and_unique(): void
    {
        $report = $this->report(['config' => ['date_range' => 'month', 'blocks' => [
            ['id' => 'a', 'metric_id' => 'sales_revenue', 'title' => 'Revenue: this/that [draft] and a very long title indeed', 'viz' => 'table'],
            ['id' => 'b', 'metric_id' => 'sales_revenue', 'title' => 'Revenue: this/that [draft] and a very long title indeed', 'viz' => 'table'],
        ]]]);

        $sheets = (new \App\Exports\CustomReport\CustomReportExport(new ReportDocument($report)))->sheets();
        $titles = array_map(fn ($s) => $s->title(), $sheets);

        $this->assertCount(3, array_unique($titles));
        foreach ($titles as $t) {
            $this->assertLessThanOrEqual(31, mb_strlen($t));
            $this->assertDoesNotMatchRegularExpression('/[\[\]:*?\/\\\\]/', $t);
        }
    }
}
