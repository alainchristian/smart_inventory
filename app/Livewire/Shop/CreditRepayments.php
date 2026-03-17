<?php

namespace App\Livewire\Shop;

use App\Enums\PaymentMethod;
use App\Models\CreditRepayment;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CreditRepayments extends Component
{
    use WithPagination;

    // Search and selection
    public string $searchQuery = '';
    public ?int $selectedCustomerId = null;

    // Repayment form
    public $amount = '';
    public string $paymentMethod = 'cash';
    public string $reference = '';
    public string $notes = '';

    // UI state
    public bool $showRepaymentForm = false;

    protected $rules = [
        'amount' => 'required|numeric|min:1',
        'paymentMethod' => 'required|in:cash,card,mobile_money,bank_transfer',
        'reference' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:500',
    ];

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function selectCustomer(int $customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->showRepaymentForm = true;
        $this->reset(['amount', 'paymentMethod', 'reference', 'notes']);
        $this->paymentMethod = 'cash';
    }

    public function cancelRepayment()
    {
        $this->showRepaymentForm = false;
        $this->selectedCustomerId = null;
        $this->reset(['amount', 'paymentMethod', 'reference', 'notes']);
    }

    public function recordRepayment()
    {
        $this->validate();

        $customer = Customer::findOrFail($this->selectedCustomerId);
        $amount = (int) ($this->amount);

        // Validate amount doesn't exceed outstanding balance
        if ($amount > $customer->outstanding_balance) {
            $this->addError('amount', 'Repayment amount cannot exceed outstanding balance of ' . number_format($customer->outstanding_balance, 0) . ' RWF');
            return;
        }

        DB::transaction(function () use ($customer, $amount) {
            // Create repayment record
            CreditRepayment::create([
                'customer_id' => $customer->id,
                'shop_id' => auth()->user()->location_id,
                'amount' => $amount,
                'payment_method' => $this->paymentMethod,
                'reference' => $this->reference ?: null,
                'notes' => $this->notes ?: null,
                'recorded_by' => auth()->id(),
                'repayment_date' => now(),
            ]);

            // Update customer balances
            $customer->update([
                'total_repaid' => $customer->total_repaid + $amount,
                'outstanding_balance' => $customer->outstanding_balance - $amount,
            ]);
        });

        session()->flash('success', 'Credit repayment of ' . number_format($amount, 0) . ' RWF recorded successfully for ' . $customer->name);

        $this->cancelRepayment();
    }

    public function getCustomersProperty()
    {
        $query = Customer::query()
            ->where('outstanding_balance', '>', 0);

        // Filter by shop if user is shop manager
        if (auth()->user()->isShopManager()) {
            $query->where('shop_id', auth()->user()->location_id);
        }

        // Search filter
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->searchQuery . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchQuery . '%');
            });
        }

        return $query->orderBy('outstanding_balance', 'desc')
            ->paginate(20);
    }

    public function getSelectedCustomerProperty()
    {
        if (!$this->selectedCustomerId) {
            return null;
        }
        return Customer::with(['shop', 'creditRepayments' => function ($query) {
            $query->orderBy('repayment_date', 'desc')->limit(10);
        }])->find($this->selectedCustomerId);
    }

    public function getPaymentMethodsProperty()
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
        ];
    }

    public function render()
    {
        return view('livewire.shop.credit-repayments');
    }
}
