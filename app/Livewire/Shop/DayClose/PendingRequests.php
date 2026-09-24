<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\ExpenseRequest;
use App\Services\DayClose\ExpenseRequestService;
use Livewire\Attributes\On;
use Livewire\Component;

class PendingRequests extends Component
{
    public ?int $payingId = null;
    public ?int $rejectingId = null;
    public string $rejectionReason = '';

    #[On('session-opened')]
    public function refreshOnSessionOpen(): void
    {
        // triggers re-render automatically
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager()) {
            abort(403);
        }
    }

    public function confirmPay(int $id): void
    {
        $this->payingId = $id;
    }

    public function cancelPay(): void
    {
        $this->payingId = null;
    }

    public function payRequest(): void
    {
        if (! $this->payingId) {
            return;
        }

        $request = ExpenseRequest::findOrFail($this->payingId);

        try {
            app(ExpenseRequestService::class)->approveAndPay($request, auth()->user());
            $this->dispatch('notification', ['type' => 'success', 'message' => "Request {$request->reference_number} paid."]);
            $this->dispatch('expense-added');
        } catch (\Exception $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }

        $this->payingId = null;
    }

    public function showRejectForm(int $id): void
    {
        $this->rejectingId     = $id;
        $this->rejectionReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId     = null;
        $this->rejectionReason = '';
    }

    public function submitRejection(): void
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:3',
        ]);

        $request = ExpenseRequest::findOrFail($this->rejectingId);

        try {
            app(ExpenseRequestService::class)->rejectRequest($request, $this->rejectionReason, auth()->user());
            $this->dispatch('notification', ['type' => 'success', 'message' => "Request {$request->reference_number} rejected."]);
        } catch (\Exception $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }

        $this->cancelReject();
    }

    public function render()
    {
        $shopId = auth()->user()->location_id;

        $requests = ExpenseRequest::pending()
            ->forShop($shopId)
            ->with('warehouse', 'requestedBy')
            ->orderByDesc('created_at')
            ->get();

        $openSession = \App\Models\DailySession::open()
            ->forShop($shopId)
            ->forDate(business_today()->toDateString())
            ->first();

        return view('livewire.shop.day-close.pending-requests', [
            'requests'    => $requests,
            'openSession' => $openSession,
            'canAct'      => $openSession !== null,
        ]);
    }
}
