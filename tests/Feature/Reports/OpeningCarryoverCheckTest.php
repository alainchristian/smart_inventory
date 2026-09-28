<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Services\DayClose\DailySessionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Cash must not vanish between days: a register opens with what the shop's
 * previous closed register kept. Found in dev data — a day opened 39 seconds
 * before the previous one was closed took its float from an older day,
 * leaving 10,295,000 RWF untraced and no check flagged it.
 */
class OpeningCarryoverCheckTest extends TestCase
{
    use DatabaseTransactions;

    private function makeSession(int $shop, int $by, string $date, string $status, int $opening, ?int $retained, string $openedAt, ?string $closedAt): void
    {
        DB::table('daily_sessions')->insert([
            'shop_id' => $shop, 'session_date' => $date, 'status' => $status, 'opened_by' => $by,
            'opened_at' => $openedAt, 'closed_at' => $closedAt, 'closed_by' => $closedAt ? $by : null,
            'opening_balance' => $opening, 'cash_retained' => $retained,
            'expected_cash' => $retained ?? $opening, 'actual_cash_counted' => $retained, 'cash_variance' => $closedAt ? 0 : null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_flags_opening_that_does_not_match_previous_close(): void
    {
        $u    = uniqid();
        $by   = User::forceCreate(['name' => 'Carry', 'email' => "co$u@example.test", 'password' => bcrypt('x'), 'role' => 'owner'])->id;
        $shop = DB::table('shops')->insertGetId(['name' => "Carry $u", 'code' => 'C' . substr($u, -8), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        // day 1 closed keeping 11,055,000 — but it was closed AFTER day 2 opened, with 760,000
        $this->makeSession($shop, $by, '2021-08-10', 'closed', 760000, 11055000, '2021-08-10 04:00:00', '2021-08-11 07:56:27');
        $this->makeSession($shop, $by, '2021-08-11', 'open', 760000, null, '2021-08-11 07:55:48', null);
        // day 3 after a clean carry-over: no flag
        $this->makeSession($shop, $by, '2021-08-12', 'open', 11055000, null, '2021-08-12 05:00:00', null);

        $checks = collect(app(DailySessionService::class)->getReportChecks($shop, '2021-08-10', '2021-08-11'))
            ->where('code', 'opening_carryover')->values();

        $this->assertCount(1, $checks);
        $this->assertSame('critical', $checks[0]['severity']);
        $this->assertSame(-10295000, $checks[0]['amount']);
        $this->assertStringContainsString('closed after this one was opened', $checks[0]['message']);
    }
}
