<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\ExpenseRequest;
use App\Services\DayClose\ExpenseRequestService;
use Livewire\Component;

class PendingRequests extends Component
{
    public ?int $rejectingId = null;
    public string $rejectionReason = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager()) {
            abort(403);
        }
    }

    public function payRequest(int $id): void
    {
        $request = ExpenseRequest::findOrFail($id);

        try {
            app(ExpenseRequestService::class)->approveAndPay($request, auth()->user());
            session()->flash('success', "Request {$request->reference_number} paid successfully.");
            $this->dispatch('expense-added');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
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
            session()->flash('success', "Request {$request->reference_number} rejected.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->cancelReject();
    }

    public function render()
    {
        $requests = ExpenseRequest::pending()
            ->forShop(auth()->user()->location_id)
            ->with('warehouse', 'requestedBy')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.shop.day-close.pending-requests', compact('requests'));
    }
}
