<?php

namespace App\Livewire\Dashboard;

use App\Models\Alert;
use App\Models\Box;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class BusinessSnapshot extends Component
{
    public string  $period   = 'today';
    public ?string $from     = null;
    public ?string $to       = null;

    public array $cashPos   = [];
    public array $bizOver   = [];
    public array $stockOver = [];
    public array $creditEx  = [];

    public function mount(): void { $this->loadData(); }

    #[On('time-filter-changed')]
    public function refresh(string $period, ?string $from = null, ?string $to = null): void
    {
        $this->period = $period;
        $this->from   = $from;
        $this->to     = $to;
        $this->loadData();
    }

    private function loadData(): void
    {
        [$start, $end] = $this->periodRange();

        // ── Cash Position ────────────────────────────────────────────────
        $payments = DB::select("
            SELECT sp.payment_method::text AS method, SUM(sp.amount)::bigint AS total
            FROM sale_payments sp
            JOIN sales s ON s.id = sp.sale_id
            WHERE s.voided_at IS NULL AND s.deleted_at IS NULL
              AND s.sale_date BETWEEN ? AND ?
            GROUP BY sp.payment_method::text
        ", [$start, $end]);

        $byMethod = [];
        foreach ($payments as $p) {
            $byMethod[$p->method] = (int) $p->total;
        }

        // Credit repayments also bring cash into the business — include them by payment method
        $repayByMethod = DB::table('credit_repayments')
            ->whereBetween('repayment_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("payment_method::text AS method, SUM(amount) AS total")
            ->groupByRaw("payment_method::text")
            ->pluck('total', 'method')->toArray();

        $this->cashPos = [
            'cash'         => ($byMethod['cash']          ?? 0) + (int)($repayByMethod['cash']          ?? 0),
            'mobile_money' => ($byMethod['mobile_money']  ?? 0) + (int)($repayByMethod['mobile_money']  ?? 0),
            'bank'         => ($byMethod['bank_transfer'] ?? 0),
            'card'         => ($byMethod['card']          ?? 0),
            'total'        => array_sum($byMethod) + (int)array_sum($repayByMethod),
        ];

        // ── Business Overview ─────────────────────────────────────────────
        $this->bizOver = [
            'shops'     => Shop::where('is_active', true)->count(),
            'customers' => Customer::count(),
            'products'  => Product::whereNull('deleted_at')->count(),
            'users'     => User::where('is_active', true)->count(),
        ];

        // ── Warehouse Overview ────────────────────────────────────────────
        $inv = Box::available()
            ->join('products', 'boxes.product_id', '=', 'products.id')
            ->selectRaw('SUM(boxes.items_remaining) AS items, SUM(boxes.items_remaining * products.selling_price) AS retail')
            ->first();

        $outOfStock = (int) DB::table('products')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->whereNotExists(function ($q) {
                $q->from('boxes')
                  ->whereColumn('boxes.product_id', 'products.id')
                  ->whereIn('boxes.status', ['full', 'partial'])
                  ->where('boxes.items_remaining', '>', 0);
            })
            ->whereExists(function ($q) {
                $q->from('boxes')->whereColumn('boxes.product_id', 'products.id');
            })
            ->count();

        $this->stockOver = [
            'items'       => (int) ($inv->items  ?? 0),
            'value'       => (int) ($inv->retail ?? 0),
            'low_stock'   => Alert::where('title', 'Low Stock Alert')->where('is_resolved', false)->count(),
            'out_of_stock'=> $outOfStock,
        ];

        // ── Credit Exposure ───────────────────────────────────────────────
        // Use all-time totals from customer aggregates — not period-filtered —
        // so the Paid/Pending split always reflects the full credit picture.
        $outstanding = (int) Customer::sum('outstanding_balance');
        $totalRepaid = (int) Customer::sum('total_repaid');

        $this->creditEx = [
            'total'       => $outstanding + $totalRepaid,
            'outstanding' => $outstanding,
            'repaid'      => $totalRepaid,
            'count'       => (int) Customer::where('outstanding_balance', '>', 0)->count(),
        ];

    }

    private function periodRange(): array
    {
        if ($this->period === 'custom') {
            return [
                Carbon::parse($this->from ?? today())->startOfDay(),
                Carbon::parse($this->to   ?? today())->endOfDay(),
            ];
        }
        return match ($this->period) {
            'today'      => [today()->startOfDay(), now()->endOfDay()],
            'yesterday'  => [today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'week'       => [now()->startOfWeek(), now()->endOfDay()],
            'month'      => [now()->startOfMonth(), now()->endOfDay()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'custom'     => [
                Carbon::parse($this->from ?? today())->startOfDay(),
                Carbon::parse($this->to   ?? today())->endOfDay(),
            ],
            default      => [today()->startOfDay(), now()->endOfDay()],
        };
    }

    public function render() { return view('livewire.dashboard.business-snapshot'); }
}
