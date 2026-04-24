<?php

namespace App\Livewire\Owner;

use App\Models\Customer;
use App\Services\Sales\CreditWriteoffService;
use Livewire\Component;
use Livewire\WithPagination;

class CreditWriteoffs extends Component
{
    use WithPagination;

    public string $search             = '';
    public ?int   $writeoffCustomerId = null;
    public int    $writeoffAmount     = 0;
    public string $writeoffReason     = '';
    public bool   $confirmStep        = false;

    public function mount(): void
    {
        if (! auth()->user()->isOwner()) {
            abort(403);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function startWriteoff(int $customerId): void
    {
        $this->writeoffCustomerId = $customerId;
        $this->writeoffAmount     = 0;
        $this->writeoffReason     = '';
        $this->confirmStep        = false;
    }

    public function cancelWriteoff(): void
    {
        $this->writeoffCustomerId = null;
        $this->writeoffAmount     = 0;
        $this->writeoffReason     = '';
        $this->confirmStep        = false;
    }

    public function fillFullBalance(): void
    {
        $customer = Customer::find($this->writeoffCustomerId);
        if ($customer) {
            $this->writeoffAmount = $customer->outstanding_balance;
        }
    }

    public function proceedToConfirm(): void
    {
        $customer = Customer::findOrFail($this->writeoffCustomerId);

        $this->validate([
            'writeoffAmount' => [
                'required', 'integer', 'min:1',
                'max:' . $customer->outstanding_balance,
            ],
            'writeoffReason' => 'required|string|min:10',
        ], [
            'writeoffAmount.max'      => 'Amount cannot exceed the outstanding balance of ' . number_format($customer->outstanding_balance) . ' RWF.',
            'writeoffReason.min'      => 'Please provide at least 10 characters explaining the reason.',
        ]);

        $this->confirmStep = true;
    }

    public function submitWriteoff(): void
    {
        if (! $this->confirmStep) {
            return;
        }

        $customer = Customer::findOrFail($this->writeoffCustomerId);

        try {
            app(CreditWriteoffService::class)->writeoff(
                $customer,
                $this->writeoffAmount,
                $this->writeoffReason,
                auth()->user()
            );

            session()->flash('success', 'Write-off of ' . number_format($this->writeoffAmount) . ' RWF recorded. Balance updated.');
            $this->dispatch('writeoff-recorded');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->cancelWriteoff();
    }

    public function getCustomersProperty()
    {
        $query = Customer::where('outstanding_balance', '>', 0)
            ->whereNull('deleted_at')
            ->with(['writeoffs' => fn ($q) => $q->orderByDesc('written_off_at')->limit(3)]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderByDesc('outstanding_balance')->paginate(20);
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        if (! $this->writeoffCustomerId) {
            return null;
        }
        return Customer::with(['writeoffs' => fn ($q) => $q->orderByDesc('written_off_at')->limit(3)->with('writtenOffBy')])
            ->find($this->writeoffCustomerId);
    }

    public function render()
    {
        return view('livewire.owner.credit-writeoffs');
    }
}
