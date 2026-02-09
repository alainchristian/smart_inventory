<?php

namespace App\Livewire\ShopManager;

use App\Models\Sale;
use App\Models\Transfer;
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
                ->sum('total') / 100,
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
                ->sum('total') / 100;

            if ($total > 0 || $hour >= 8) { // Only show from 8 AM onwards if there's data
                $data[] = [
                    'hour' => $startTime->format('H:00'),
                    'total' => $total,
                ];
            }
        }

        return $data;
    }

    public function getPaymentMethodBreakdown()
    {
        $date = $this->selectedDate ?? today();

        $sales = Sale::notVoided()
            ->where('shop_id', $this->shopId)
            ->whereDate('sale_date', $date)
            ->get();

        return [
            'cash' => $sales->where('payment_method', 'cash')->sum('total') / 100,
            'card' => $sales->where('payment_method', 'card')->sum('total') / 100,
            'mobile_money' => $sales->where('payment_method', 'mobile_money')->sum('total') / 100,
            'bank_transfer' => $sales->where('payment_method', 'bank_transfer')->sum('total') / 100,
            'credit' => $sales->where('payment_method', 'credit')->sum('total') / 100,
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

    public function render()
    {
        return view('livewire.shop-manager.dashboard', [
            'salesToday' => $this->getSalesToday(),
            'hourlySalesData' => $this->getHourlySalesData(),
            'paymentBreakdown' => $this->getPaymentMethodBreakdown(),
        ]);
    }
}
