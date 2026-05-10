<?php

namespace App\Livewire\Transactions;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LiveFeed extends Component
{
    public bool   $isOpen     = false;
    public int    $unread     = 0;
    public string $activeTab  = 'transactions'; // transactions | stock | movements
    public string $period     = 'today'; // today | yesterday | this_week | this_month | last_30 | custom
    public string $filter     = 'all';   // all | in | out | transfer
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public array  $transactions   = [];
    public array  $periodTotals   = ['in' => 0, 'out' => 0, 'transfer' => 0, 'withdrawal' => 0, 'deposit' => 0, 'count' => 0];
    public array  $stockData      = ['warehouses' => [], 'shops' => []];
    public array  $movements      = [];

    // Timestamp of last time drawer was opened — persisted in session
    protected string $sessionKey = 'lf_last_opened_at';

    // The timestamp when the drawer was previously opened (used to mark rows NEW)
    public ?string $prevOpenedAt = null;

    public function mount(): void
    {
        if (!$this->authorized()) return;
        $this->unread = $this->countUnread();
    }

    // ── Actions ────────────────────────────────────────────────────────────────

    public function open(): void
    {
        $this->prevOpenedAt = session($this->sessionKey);
        session([$this->sessionKey => now()->toDateTimeString()]);
        $this->isOpen = true;
        $this->unread = 0;
        $this->loadTransactions();
        $this->loadStock();
        $this->loadMovements();
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        if ($tab === 'stock')     $this->loadStock();
        if ($tab === 'movements') $this->loadMovements();
    }

    public function setPeriod(string $p): void
    {
        $this->period = $p;
        if ($p !== 'custom') {
            $this->loadTransactions();
        }
    }

    public function updatedDateFrom(): void
    {
        if ($this->dateFrom && $this->dateTo) {
            $this->period = 'custom';
            $this->loadTransactions();
        }
    }

    public function updatedDateTo(): void
    {
        if ($this->dateFrom && $this->dateTo) {
            $this->period = 'custom';
            $this->loadTransactions();
        }
    }

    public function setFilter(string $f): void
    {
        $this->filter = $f;
        $this->loadTransactions();
    }

    public function poll(): void
    {
        if (!$this->authorized()) return;
        $this->unread = $this->countUnread();
        if ($this->isOpen) {
            $this->loadTransactions();
            $this->loadStock();
            $this->loadMovements();
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    protected function authorized(): bool
    {
        return auth()->check() && auth()->user()->isOwner();
    }

    protected function dateRange(): array
    {
        return match ($this->period) {
            'yesterday'  => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week'  => [now()->startOfWeek(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'custom'     => [
                $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : now()->startOfDay(),
                $this->dateTo   ? Carbon::parse($this->dateTo)->endOfDay()     : now()->endOfDay(),
            ],
            default      => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    protected function countUnread(): int
    {
        $since = session($this->sessionKey, now()->subMinutes(5)->toDateTimeString());

        $txSql = "
            SELECT COUNT(*) AS cnt FROM (
                SELECT id FROM sales
                    WHERE voided_at IS NULL AND deleted_at IS NULL AND sale_date > ?
                UNION ALL
                SELECT id FROM credit_repayments WHERE repayment_date > ?
                UNION ALL
                SELECT id FROM returns
                    WHERE deleted_at IS NULL AND is_exchange = false
                      AND refund_amount > 0 AND processed_at > ?
                UNION ALL
                SELECT id FROM owner_withdrawals WHERE deleted_at IS NULL AND recorded_at > ?
                UNION ALL
                SELECT id FROM expenses
                    WHERE deleted_at IS NULL AND is_system_generated = false AND recorded_at > ?
                UNION ALL
                SELECT id FROM bank_deposits WHERE deleted_at IS NULL AND deposited_at > ?
            ) t
        ";

        $txRow = DB::selectOne($txSql, array_fill(0, 6, $since));
        $mvRow = DB::selectOne("SELECT COUNT(*) AS cnt FROM box_movements WHERE moved_at > ?", [$since]);

        return (int) ($txRow->cnt ?? 0) + (int) ($mvRow->cnt ?? 0);
    }

    protected function loadTransactions(): void
    {
        [$from, $to] = $this->dateRange();

        $dirWhere = match ($this->filter) {
            'in'       => "AND direction = 'in'",
            'out'      => "AND direction = 'out'",
            'transfer' => "AND direction = 'transfer'",
            default    => '',
        };

        $sql = "
            SELECT * FROM (

                SELECT
                    'sale'                 AS type,
                    s.sale_number          AS reference,
                    s.total                AS amount,
                    'in'                   AS direction,
                    sh.name                AS shop_name,
                    u.name                 AS actor,
                    s.sale_date            AS happened_at,
                    s.payment_method::text AS method,
                    s.customer_name        AS customer,
                    NULL                   AS description
                FROM sales s
                LEFT JOIN shops sh ON sh.id = s.shop_id
                LEFT JOIN users u  ON u.id  = s.sold_by
                WHERE s.voided_at IS NULL AND s.deleted_at IS NULL
                  AND s.sale_date BETWEEN ? AND ?

                UNION ALL

                SELECT
                    'repayment'              AS type,
                    CONCAT('REP-', cr.id)    AS reference,
                    cr.amount                AS amount,
                    'in'                     AS direction,
                    sh.name                  AS shop_name,
                    u.name                   AS actor,
                    cr.repayment_date        AS happened_at,
                    cr.payment_method::text  AS method,
                    c.name                   AS customer,
                    NULL                     AS description
                FROM credit_repayments cr
                LEFT JOIN shops sh    ON sh.id = cr.shop_id
                LEFT JOIN users u     ON u.id  = cr.recorded_by
                LEFT JOIN customers c ON c.id  = cr.customer_id
                WHERE cr.repayment_date BETWEEN ? AND ?

                UNION ALL

                SELECT
                    'return'               AS type,
                    r.return_number        AS reference,
                    r.refund_amount        AS amount,
                    'out'                  AS direction,
                    sh.name                AS shop_name,
                    u.name                 AS actor,
                    r.processed_at         AS happened_at,
                    r.refund_method::text  AS method,
                    r.customer_name        AS customer,
                    r.reason::text         AS description
                FROM returns r
                LEFT JOIN shops sh ON sh.id = r.shop_id
                LEFT JOIN users u  ON u.id  = r.processed_by
                WHERE r.deleted_at IS NULL AND r.is_exchange = false
                  AND r.refund_amount > 0
                  AND r.processed_at BETWEEN ? AND ?

                UNION ALL

                SELECT
                    'withdrawal'          AS type,
                    CONCAT('WDR-',ow.id)  AS reference,
                    ow.amount             AS amount,
                    'transfer'            AS direction,
                    sh.name               AS shop_name,
                    u.name                AS actor,
                    ow.recorded_at        AS happened_at,
                    ow.method::text       AS method,
                    NULL                  AS customer,
                    ow.reason             AS description
                FROM owner_withdrawals ow
                LEFT JOIN shops sh ON sh.id = ow.shop_id
                LEFT JOIN users u  ON u.id  = ow.recorded_by
                WHERE ow.deleted_at IS NULL
                  AND ow.recorded_at BETWEEN ? AND ?

                UNION ALL

                SELECT
                    'expense'                        AS type,
                    CONCAT('EXP-', e.id)             AS reference,
                    e.amount                         AS amount,
                    'out'                            AS direction,
                    sh.name                          AS shop_name,
                    u.name                           AS actor,
                    e.recorded_at                    AS happened_at,
                    e.payment_method::text           AS method,
                    NULL                             AS customer,
                    COALESCE(ec.name, e.description) AS description
                FROM expenses e
                JOIN  daily_sessions ds    ON ds.id = e.daily_session_id
                LEFT JOIN shops sh         ON sh.id = ds.shop_id
                LEFT JOIN users u          ON u.id  = e.recorded_by
                LEFT JOIN expense_categories ec ON ec.id = e.expense_category_id
                WHERE e.deleted_at IS NULL AND e.is_system_generated = false
                  AND e.recorded_at BETWEEN ? AND ?

                UNION ALL

                SELECT
                    'deposit'              AS type,
                    CONCAT('DEP-', bd.id)  AS reference,
                    bd.amount              AS amount,
                    'transfer'             AS direction,
                    sh.name                AS shop_name,
                    u.name                 AS actor,
                    bd.deposited_at        AS happened_at,
                    'bank_transfer'        AS method,
                    NULL                   AS customer,
                    bd.notes               AS description
                FROM bank_deposits bd
                LEFT JOIN shops sh ON sh.id = bd.shop_id
                LEFT JOIN users u  ON u.id  = bd.deposited_by
                WHERE bd.deleted_at IS NULL
                  AND bd.deposited_at BETWEEN ? AND ?

            ) feed
            WHERE 1=1 {$dirWhere}
            ORDER BY happened_at DESC
            LIMIT 200
        ";

        // 2 params per subquery × 6 subqueries
        $params = [];
        for ($i = 0; $i < 6; $i++) {
            $params[] = $from->toDateTimeString();
            $params[] = $to->toDateTimeString();
        }

        $rows = DB::select($sql, $params);

        $in = $out = $withdrawal = $deposit = 0;
        $this->transactions = array_map(function ($row) use (&$in, &$out, &$withdrawal, &$deposit) {
            $r = (array) $row;
            $r['amount'] = (int) $r['amount'];
            match ($r['type']) {
                'sale', 'repayment' => $in         += $r['amount'],
                'return', 'expense' => $out        += $r['amount'],
                'withdrawal'        => $withdrawal += $r['amount'],
                'deposit'           => $deposit    += $r['amount'],
                default             => null,
            };
            return $r;
        }, $rows);

        $transfer = $withdrawal + $deposit;
        $this->periodTotals = [
            'in'         => $in,
            'out'        => $out,
            'transfer'   => $transfer,
            'withdrawal' => $withdrawal,
            'deposit'    => $deposit,
            'net_pl'     => $in - $out,
            'net_cash'   => $in - $out - $transfer,
            'count'      => count($this->transactions),
        ];
    }

    protected function loadMovements(): void
    {
        $since = now()->subHours(24)->toDateTimeString();

        $sql = "
            SELECT
                bm.id,
                b.box_code,
                b.status::text               AS box_status,
                p.name                       AS product_name,
                bm.movement_type,
                bm.from_location_type::text  AS from_type,
                bm.to_location_type::text    AS to_type,
                COALESCE(fw.name, fs.name)   AS from_location,
                COALESCE(tw.name, ts.name)   AS to_location,
                u.name                       AS moved_by,
                bm.moved_at,
                bm.items_moved,
                bm.reason,
                bm.notes
            FROM box_movements bm
            JOIN  boxes    b  ON b.id  = bm.box_id
            LEFT JOIN products p  ON p.id  = b.product_id
            JOIN  users    u  ON u.id  = bm.moved_by
            LEFT JOIN warehouses fw ON bm.from_location_type::text = 'warehouse' AND fw.id = bm.from_location_id
            LEFT JOIN shops      fs ON bm.from_location_type::text = 'shop'      AND fs.id = bm.from_location_id
            LEFT JOIN warehouses tw ON bm.to_location_type::text   = 'warehouse' AND tw.id = bm.to_location_id
            LEFT JOIN shops      ts ON bm.to_location_type::text   = 'shop'      AND ts.id = bm.to_location_id
            WHERE bm.moved_at >= ?
            ORDER BY bm.moved_at DESC
            LIMIT 150
        ";

        $this->movements = array_map(fn($r) => [
            'id'            => $r->id,
            'box_code'      => $r->box_code,
            'box_status'    => $r->box_status,
            'product_name'  => $r->product_name,
            'movement_type' => $r->movement_type,
            'from_location' => $r->from_location,
            'to_location'   => $r->to_location,
            'from_type'     => $r->from_type,
            'to_type'       => $r->to_type,
            'moved_by'      => $r->moved_by,
            'moved_at'      => $r->moved_at,
            'items_moved'   => $r->items_moved !== null ? (int) $r->items_moved : null,
            'reason'        => $r->reason,
            'notes'         => $r->notes,
        ], DB::select($sql, [$since]));
    }

    protected function loadStock(): void
    {
        $warehouses = DB::select("
            SELECT
                w.name,
                w.code,
                COUNT(b.id)                         AS box_count,
                COALESCE(SUM(b.items_remaining), 0) AS items_total
            FROM warehouses w
            LEFT JOIN boxes b ON b.location_type::text = 'warehouse'
                             AND b.location_id = w.id
                             AND b.status::text != 'empty'
            WHERE w.is_active = true
            GROUP BY w.id, w.name, w.code
            ORDER BY w.name
        ");

        $shops = DB::select("
            SELECT
                s.name,
                s.code,
                COUNT(b.id)                         AS box_count,
                COALESCE(SUM(b.items_remaining), 0) AS items_total
            FROM shops s
            LEFT JOIN boxes b ON b.location_type::text = 'shop'
                             AND b.location_id = s.id
                             AND b.status::text != 'empty'
            WHERE s.is_active = true
            GROUP BY s.id, s.name, s.code
            ORDER BY s.name
        ");

        $this->stockData = [
            'warehouses' => array_map(fn($r) => [
                'name'        => $r->name,
                'code'        => $r->code,
                'box_count'   => (int) $r->box_count,
                'items_total' => (int) $r->items_total,
            ], $warehouses),
            'shops' => array_map(fn($r) => [
                'name'        => $r->name,
                'code'        => $r->code,
                'box_count'   => (int) $r->box_count,
                'items_total' => (int) $r->items_total,
            ], $shops),
        ];
    }

    public function render()
    {
        return view('livewire.transactions.live-feed');
    }
}
