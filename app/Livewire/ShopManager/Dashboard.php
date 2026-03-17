<?php

namespace App\Livewire\ShopManager;

use App\Models\Sale;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $shopId;
    public $refreshInterval = 30000; // milliseconds (30 seconds for POS updates)
    public $selectedDate;

    protected $listeners = [
        'refreshDashboard' => '$refresh',
        'sale-completed' => '$refresh',
    ];

    public function mount()
    {
        $user = auth()->user();

        // Allow shop managers and owners
        if (!$user->isShopManager() && !$user->isOwner()) {
            abort(403, 'Unauthorized access.');
        }

        // For shop managers, use their assigned shop
        if ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        }

        // For owners, use session or first shop
        if ($user->isOwner()) {
            $this->shopId = session('selected_shop_id') ?? \App\Models\Shop::first()?->id;

            if (!$this->shopId) {
                abort(404, 'No shop found. Please create a shop first.');
            }
        }

        $this->selectedDate = today()->format('Y-m-d');
    }

    public function getSalesToday()
    {
        $date = $this->selectedDate ?? today();

        return [
            'total' => Sale::notVoided()
                ->where('shop_id', $this->shopId)
                ->whereDate('sale_date', $date)
                ->sum('total'),
            'count' => Sale::notVoided()
                ->where('shop_id', $this->shopId)
                ->whereDate('sale_date', $date)
                ->count(),
        ];
    }

    public function getHourlySalesData()
    {
        // Get sales for each hour of the selected day
        $data = [];
        $date = $this->selectedDate ?? today();

        for ($hour = 0; $hour < 24; $hour++) {
            $startTime = now()->setDate(
                date('Y', strtotime($date)),
                date('m', strtotime($date)),
                date('d', strtotime($date))
            )->setTime($hour, 0, 0);

            $endTime = (clone $startTime)->addHour();

            $total = Sale::notVoided()
                ->where('shop_id', $this->shopId)
                ->whereBetween('sale_date', [$startTime, $endTime])
                ->sum('total');

            if ($total > 0 || $hour >= 8) { // Only show from 8 AM onwards if there's data
                $data[] = [
                    'hour' => $startTime->format('H:00'),
                    'total' => $total,
                ];
            }
        }

        return $data;
    }

    private function getPaymentMethodBreakdown(): array
    {
        $date = $this->selectedDate ? \Carbon\Carbon::parse($this->selectedDate) : today();

        // Read from sale_payments table (the correct source for split payments)
        $rows = DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->where('sales.shop_id', $this->shopId)
            ->whereDate('sales.sale_date', $date)
            ->selectRaw('sale_payments.payment_method::text as method, SUM(sale_payments.amount) as total')
            ->groupBy('sale_payments.payment_method')
            ->get()
            ->keyBy('method');

        return [
            'cash'          => (int) ($rows['cash']?->total          ?? 0),
            'card'          => (int) ($rows['card']?->total          ?? 0),
            'mobile_money'  => (int) ($rows['mobile_money']?->total  ?? 0),
            'bank_transfer' => (int) ($rows['bank_transfer']?->total ?? 0),
            'credit'        => (int) ($rows['credit']?->total        ?? 0),
        ];
    }

    public function updatedSelectedDate()
    {
        $this->dispatch('date-changed');
    }

    public function viewTransfer($transferId)
    {
        return redirect()->route('transfers.show', $transferId);
    }

    private function getShopCreditOutstanding(): array
    {
        $outstanding = \App\Models\Customer::where('shop_id', $this->shopId)
            ->where('outstanding_balance', '>', 0)
            ->selectRaw('COUNT(*) as customer_count, SUM(outstanding_balance) as total_outstanding')
            ->first();

        return [
            'customer_count'    => (int) ($outstanding->customer_count   ?? 0),
            'total_outstanding' => (int) ($outstanding->total_outstanding ?? 0),
        ];
    }

    public function render()
    {
        return view('livewire.shop-manager.dashboard', [
            'salesToday'        => $this->getSalesToday(),
            'hourlySalesData'   => $this->getHourlySalesData(),
            'paymentBreakdown'  => $this->getPaymentMethodBreakdown(),
            'creditOutstanding' => $this->getShopCreditOutstanding(),
        ]);
    }
}
