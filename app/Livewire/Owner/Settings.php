<?php

namespace App\Livewire\Owner;

use App\Models\Category;
use App\Services\SettingsService;
use Livewire\Component;

class Settings extends Component
{
    // Sales
    public bool  $allowIndividualItemSales  = true;
    public array $individualSaleCategoryIds = [];

    // Returns
    public bool $allowSellerReturns       = true;
    public int  $returnApprovalThreshold  = 100000;
    public int  $maxReturnDays            = 30;

    // Credit
    public bool $allowCreditSales       = true;
    public bool $creditRequiresCustomer = true;
    public int  $maxCreditPerCustomer   = 0;

    // Price
    public bool $allowPriceOverride       = true;
    public int  $priceOverrideThreshold   = 20;

    // Payment methods
    public bool $allowCardPayment         = false;
    public bool $allowBankTransferPayment = false;

    public function mount(): void
    {
        if (!auth()->user()->isOwner()) abort(403);

        $svc = app(SettingsService::class);

        $this->allowIndividualItemSales  = $svc->allowIndividualItemSales();
        $this->individualSaleCategoryIds = $svc->individualSaleCategoryIds();
        $this->allowSellerReturns        = $svc->allowSellerReturns();
        $this->returnApprovalThreshold   = $svc->returnApprovalThreshold();
        $this->maxReturnDays             = $svc->maxReturnDays();
        $this->allowCreditSales          = $svc->allowCreditSales();
        $this->creditRequiresCustomer    = $svc->creditRequiresCustomer();
        $this->maxCreditPerCustomer      = $svc->maxCreditPerCustomer();
        $this->allowPriceOverride        = $svc->allowPriceOverride();
        $this->priceOverrideThreshold    = $svc->priceOverrideThreshold();
        $this->allowCardPayment          = $svc->allowCardPayment();
        $this->allowBankTransferPayment  = $svc->allowBankTransferPayment();
    }

    public function save(): void
    {
        $this->validate([
            'returnApprovalThreshold' => 'required|integer|min:0',
            'maxReturnDays'           => 'required|integer|min:0',
            'maxCreditPerCustomer'    => 'required|integer|min:0',
            'priceOverrideThreshold'  => 'required|integer|min:1|max:100',
        ]);

        $svc = app(SettingsService::class);

        $svc->set('allow_individual_item_sales',  $this->allowIndividualItemSales);
        $svc->set('individual_sale_category_ids', $this->individualSaleCategoryIds);
        $svc->set('allow_seller_returns',         $this->allowSellerReturns);
        $svc->set('return_approval_threshold',    $this->returnApprovalThreshold);
        $svc->set('max_return_days',              $this->maxReturnDays);
        $svc->set('allow_credit_sales',           $this->allowCreditSales);
        $svc->set('credit_requires_customer',     $this->creditRequiresCustomer);
        $svc->set('max_credit_per_customer',      $this->maxCreditPerCustomer);
        $svc->set('allow_price_override',         $this->allowPriceOverride);
        $svc->set('price_override_threshold',     $this->priceOverrideThreshold);
        $svc->set('allow_card_payment',           $this->allowCardPayment);
        $svc->set('allow_bank_transfer_payment',  $this->allowBankTransferPayment);

        $this->dispatch('notification', [
            'type'    => 'success',
            'message' => 'Settings saved successfully',
        ]);
        $this->dispatch('settings-saved');
    }

    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->individualSaleCategoryIds)) {
            $this->individualSaleCategoryIds = array_values(
                array_filter($this->individualSaleCategoryIds, fn ($id) => $id !== $categoryId)
            );
        } else {
            $this->individualSaleCategoryIds[] = $categoryId;
        }
    }

    public function render()
    {
        return view('livewire.owner.settings', [
            'categories' => Category::with('parent')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
