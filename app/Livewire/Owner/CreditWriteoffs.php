<?php

namespace App\Livewire\Owner;

use App\Models\Customer;
use App\Services\Sales\CreditWriteoffService;
use App\Services\Sales\CustomerCreditLedger;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Owner write-offs. Credit belongs to the shop that gave it, so each row is
 * one customer's balance at one shop and a write-off reduces only that shop's
 * receivable (see CustomerCreditLedger).
 */
class CreditWriteoffs extends Component
{
    use WithPagination;

    public string $search             = '';
    public ?int   $writeoffCustomerId = null;
    public ?int   $writeoffShopId     = null;
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

    public function startWriteoff(int $customerId, int $shopId): void
    {
        $this->writeoffCustomerId = $customerId;
        $this->writeoffShopId     = $shopId;
        $this->writeoffAmount     = 0;
        $this->writeoffReason     = '';
        $this->confirmStep        = false;
    }

    public function cancelWriteoff(): void
    {
        $this->writeoffCustomerId = null;
        $this->writeoffShopId     = null;
        $this->writeoffAmount     = 0;
        $this->writeoffReason     = '';
        $this->confirmStep        = false;
    }

    private function owedToShop(): int
    {
        return $this->writeoffCustomerId && $this->writeoffShopId
            ? app(CustomerCreditLedger::class)->balanceAt($this->writeoffCustomerId, $this->writeoffShopId)
            : 0;
    }

    public function fillFullBalance(): void
    {
        $this->writeoffAmount = $this->owedToShop();
    }

    public function proceedToConfirm(): void
    {
        $owed = $this->owedToShop();

        $this->validate([
            'writeoffAmount' => ['required', 'integer', 'min:1', 'max:' . $owed],
            'writeoffReason' => 'required|string|min:10',
        ], [
            'writeoffAmount.max' => 'Amount cannot exceed the ' . number_format($owed) . ' RWF owed to this shop.',
            'writeoffReason.min' => 'Please provide at least 10 characters explaining the reason.',
        ]);

        $this->confirmStep = true;
    }

    public function submitWriteoff(): void
    {
        if (! $this->confirmStep || ! $this->writeoffShopId) {
            return;
        }

        $customer = Customer::findOrFail($this->writeoffCustomerId);

        try {
            app(CreditWriteoffService::class)->writeoff(
                $customer,
                $this->writeoffShopId,
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

    /**
     * One row per customer per shop that is owed money. The shop's ledger
     * figures are aliased over the customer's company-wide ones so the view
     * keeps reading ->outstanding_balance / ->last_repayment_at.
     */
    private function balanceRows(): Builder
    {
        return Customer::query()
            ->join('customer_shop_balances as csb', 'csb.customer_id', '=', 'customers.id')
            ->join('shops', 'shops.id', '=', 'csb.shop_id')
            ->whereNull('customers.deleted_at')
            ->where('csb.outstanding_balance', '>', 0)
            ->select(
                'customers.*',
                'csb.outstanding_balance as outstanding_balance',
                'csb.last_repayment_at as last_repayment_at',
                'csb.shop_id as balance_shop_id',
                'shops.name as balance_shop_name',
            );
    }

    public function getCustomersProperty()
    {
        $query = $this->balanceRows();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('customers.name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('customers.phone', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderByDesc('csb.outstanding_balance')->paginate(20);
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        if (! $this->writeoffCustomerId || ! $this->writeoffShopId) {
            return null;
        }

        return $this->balanceRows()
            ->where('csb.shop_id', $this->writeoffShopId)
            ->with(['writeoffs' => fn ($q) => $q->where('shop_id', $this->writeoffShopId)->orderByDesc('written_off_at')->limit(3)->with('writtenOffBy')])
            ->find($this->writeoffCustomerId);
    }

    public function render()
    {
        return view('livewire.owner.credit-writeoffs');
    }
}
