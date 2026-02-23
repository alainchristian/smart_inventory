<?php

namespace App\Livewire\Shop\Returns;

use App\Enums\ReturnReason;
use App\Enums\AlertSeverity;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use App\Models\Alert;
use App\Models\ReturnModel;
use App\Services\Returns\ReturnService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProcessReturn extends Component
{
    use WithFileUploads;
    // Shop
    public $shopId;
    public $shopName;

    // Step tracking
    public $currentStep = 1; // 1: Select Sale, 2: Select Items, 3: Return Details

    // Sale linking (required) — store ID, not model
    public $saleSearch = '';
    public $linkedSaleId = null;
    public $saleSearchResults = []; // stored as array, not collection
    public $showSaleSearchDropdown = false;
    public $showQuickSales = false;
    public $saleAgeWarning = false;

    // Return items — selected from the sale
    public $items = [];

    // Photo uploads for damaged items (indexed by item index)
    public $itemPhotos = [];

    // Return details
    public $reason = 'customer_request';
    public $customerName = '';
    public $customerPhone = '';
    public $isExchange = false;
    public $refundMethod = 'cash';
    public $notes = '';

    // Confirmation
    public $showConfirmation = false;
    public $showReceiptPreview = false;

    // Flags
    public $requiresApproval = false;
    public $existingReturnWarning = null;

    protected $rules = [
        'linkedSaleId' => 'required',
        'reason' => 'required|in:defective,wrong_item,damaged,expired,customer_request,other',
        'customerName' => 'nullable|string|max:255',
        'customerPhone' => 'nullable|string|max:20',
        'isExchange' => 'boolean',
        'refundMethod' => 'required_if:isExchange,false|in:cash,card,mobile_money,store_credit',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity_returned' => 'required|integer|min:1',
        'items.*.quantity_damaged' => 'required|integer|min:0',
        'itemPhotos.*' => 'nullable|image|max:2048', // Max 2MB per photo
    ];

    protected $messages = [
        'linkedSaleId.required' => 'You must link a sale before processing a return.',
        'items.required' => 'You must select at least one item to return.',
        'items.min' => 'You must select at least one item to return.',
    ];

    public function mount()
    {
        $user = auth()->user();

        if (!$user->isShopManager()) {
            abort(403, 'Only shop managers can process returns.');
        }

        $this->shopId = $user->location_id;
        $shop = Shop::find($this->shopId);
        $this->shopName = $shop->name ?? 'Unknown Shop';
    }

    /**
     * Load the linked sale fresh from DB (never stored as public property).
     */
    protected function getLinkedSale()
    {
        if (!$this->linkedSaleId) return null;

        return Sale::where('id', $this->linkedSaleId)
            ->where('shop_id', $this->shopId)
            ->with('items.product')
            ->first();
    }

    // --- Step 1: Search and Select Sale ---

    public function updatedSaleSearch()
    {
        $this->searchSales();
    }

    public function searchSales()
    {
        if (strlen($this->saleSearch) < 2) {
            $this->saleSearchResults = [];
            $this->showSaleSearchDropdown = false;
            $this->showQuickSales = false;
            return;
        }

        // Convert to array to avoid Livewire model serialization issues
        $this->saleSearchResults = Sale::where('shop_id', $this->shopId)
            ->whereNull('voided_at')
            ->where(function ($query) {
                $query->where('sale_number', 'like', '%' . $this->saleSearch . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->saleSearch . '%')
                    ->orWhere('customer_phone', 'like', '%' . $this->saleSearch . '%');
            })
            ->with(['items.product', 'soldBy'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'customer_name' => $sale->customer_name,
                    'customer_phone' => $sale->customer_phone,
                    'total' => $sale->total ?? 0,
                    'created_at' => $sale->created_at->format('M d, Y'),
                    'items_count' => $sale->items->count(),
                    'sold_by' => $sale->soldBy?->name ?? null,
                ];
            })
            ->toArray();

        $this->showSaleSearchDropdown = count($this->saleSearchResults) > 0;
    }

    public function loadTodaySales()
    {
        $this->showQuickSales = true;
        $this->saleSearch = '';

        $this->saleSearchResults = Sale::where('shop_id', $this->shopId)
            ->whereNull('voided_at')
            ->whereDate('sale_date', today())
            ->with(['items.product', 'soldBy'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'customer_name' => $sale->customer_name,
                    'customer_phone' => $sale->customer_phone,
                    'total' => $sale->total ?? 0,
                    'created_at' => $sale->created_at->format('M d, Y g:i A'),
                    'items_count' => $sale->items->count(),
                    'sold_by' => $sale->soldBy?->name ?? null,
                ];
            })
            ->toArray();

        $this->showSaleSearchDropdown = count($this->saleSearchResults) > 0;
    }

    public function selectSale($saleId)
    {
        $sale = Sale::where('id', $saleId)
            ->where('shop_id', $this->shopId)
            ->whereNull('voided_at')
            ->first();

        if ($sale) {
            // Check for existing returns
            $existingReturn = \App\Models\ReturnModel::where('sale_id', $saleId)
                ->whereNull('deleted_at')
                ->first();

            if ($existingReturn) {
                $this->existingReturnWarning = "This sale already has a return ({$existingReturn->return_number}). Creating multiple returns for one sale may cause inventory issues.";
            } else {
                $this->existingReturnWarning = null;
            }

            // Check sale age (7 days warning)
            $saleAge = $sale->sale_date->diffInDays(now());
            $this->saleAgeWarning = $saleAge > 7;

            $this->linkedSaleId = $sale->id;
            $this->saleSearch = $sale->sale_number;
            $this->showSaleSearchDropdown = false;
            $this->saleSearchResults = [];
            $this->showQuickSales = false;

            // Pre-fill customer details
            $this->customerName = $sale->customer_name ?? '';
            $this->customerPhone = $sale->customer_phone ?? '';

            // Reset items when changing sale
            $this->items = [];

            // Move to step 2
            $this->currentStep = 2;
        } else {
            session()->flash('error', 'Sale not found or already voided.');
        }
    }

    public function changeSale()
    {
        $this->linkedSaleId = null;
        $this->saleSearch = '';
        $this->saleSearchResults = [];
        $this->showSaleSearchDropdown = false;
        $this->showQuickSales = false;
        $this->items = [];
        $this->itemPhotos = [];
        $this->saleAgeWarning = false;
        $this->existingReturnWarning = null;
        $this->currentStep = 1;
    }

    // --- Step 2: Select Items from Sale ---

    public function toggleItem($saleItemId)
    {
        if (!$this->linkedSaleId) return;

        // Check if already selected
        $existingIndex = collect($this->items)->search(function ($item) use ($saleItemId) {
            return ($item['original_sale_item_id'] ?? null) == $saleItemId;
        });

        if ($existingIndex !== false) {
            // Remove it and its photo
            unset($this->items[$existingIndex]);
            unset($this->itemPhotos[$existingIndex]);
            $this->items = array_values($this->items);
            $this->itemPhotos = array_values($this->itemPhotos);
        } else {
            // Load sale fresh to get the item
            $sale = $this->getLinkedSale();
            if (!$sale) return;

            $saleItem = $sale->items->firstWhere('id', $saleItemId);
            if ($saleItem) {
                $this->items[] = [
                    'product_id' => $saleItem->product_id,
                    'product_name' => $saleItem->product->name,
                    'quantity_sold' => $saleItem->quantity_sold,
                    'quantity_returned' => $saleItem->quantity_sold,
                    'quantity_damaged' => 0,
                    'quantity_good' => $saleItem->quantity_sold,
                    'condition_notes' => '',
                    'is_replacement' => false,
                    'replacement_product_id' => null,
                    'original_sale_item_id' => $saleItem->id,
                    'unit_price' => $saleItem->unit_price ?? 0,
                ];
            }
        }
    }

    public function updateItemQuantity($index, $field, $value)
    {
        if (!isset($this->items[$index])) return;

        $value = max(0, (int) $value);

        if ($field === 'quantity_returned') {
            $maxQty = $this->items[$index]['quantity_sold'];
            $value = min($value, $maxQty);
            $value = max(1, $value);
            $this->items[$index]['quantity_returned'] = $value;

            if ($this->items[$index]['quantity_damaged'] > $value) {
                $this->items[$index]['quantity_damaged'] = $value;
            }
        } elseif ($field === 'quantity_damaged') {
            $maxDamaged = $this->items[$index]['quantity_returned'];
            $value = min($value, $maxDamaged);
            $this->items[$index]['quantity_damaged'] = $value;
        }

        $this->items[$index]['quantity_good'] = $this->items[$index]['quantity_returned'] - $this->items[$index]['quantity_damaged'];
    }

    public function updateConditionNotes($index, $value)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['condition_notes'] = $value;
        }
    }

    public function updatedItemPhotos($value, $key)
    {
        // Validate photo when uploaded
        $this->validateOnly("itemPhotos.{$key}");
    }

    public function removePhoto($index)
    {
        if (isset($this->itemPhotos[$index])) {
            unset($this->itemPhotos[$index]);
        }
    }

    public function isItemSelected($saleItemId): bool
    {
        return collect($this->items)->contains(function ($item) use ($saleItemId) {
            return ($item['original_sale_item_id'] ?? null) == $saleItemId;
        });
    }

    public function goToStep($step)
    {
        if ($step > 1 && !$this->linkedSaleId) {
            session()->flash('error', 'Please select a sale first.');
            return;
        }
        if ($step > 2 && count($this->items) === 0) {
            session()->flash('error', 'Please select at least one item to return.');
            return;
        }

        $this->currentStep = $step;
    }

    // --- Step 3: Confirm and Submit ---

    public function confirmSubmit()
    {
        $this->validate();
        $this->showConfirmation = true;
    }

    public function cancelSubmit()
    {
        $this->showConfirmation = false;
    }

    public function previewReceipt()
    {
        $this->validate();
        $this->showReceiptPreview = true;
    }

    public function closeReceiptPreview()
    {
        $this->showReceiptPreview = false;
    }

    public function submitReturn()
    {
        $this->validate();

        try {
            // Check if requires approval (large refunds)
            $estimatedRefund = $this->getEstimatedRefund();
            $requiresApproval = false;

            if ($estimatedRefund > 50000 && !auth()->user()->isOwner()) { // > 500 RWF
                $requiresApproval = true;
            }

            $returnService = app(ReturnService::class);

            // Process photos and add paths to items
            $itemsWithPhotos = $this->items;
            foreach ($itemsWithPhotos as $index => &$item) {
                if (isset($this->itemPhotos[$index]) && $this->itemPhotos[$index]) {
                    // Store photo in public/storage/returns folder
                    $photo = $this->itemPhotos[$index];
                    $path = $photo->store('returns', 'public');
                    $item['photo_path'] = $path;
                } else {
                    $item['photo_path'] = null;
                }
            }

            $data = [
                'shop_id' => $this->shopId,
                'sale_id' => $this->linkedSaleId,
                'reason' => $this->reason,
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'is_exchange' => $this->isExchange,
                'refund_method' => $this->isExchange ? null : $this->refundMethod,
                'notes' => $this->notes,
                'items' => $itemsWithPhotos,
                'requires_approval' => $requiresApproval,
                'estimated_refund' => $estimatedRefund,
            ];

            $return = $returnService->processReturn($data, auth()->user());

            // Create alert for owner if requires approval
            if ($requiresApproval) {
                $owner = \App\Models\User::where('role', 'owner')->first();
                if ($owner) {
                    \App\Models\Alert::create([
                        'title' => 'Large Return Requires Approval',
                        'message' => "Return {$return->return_number} with refund of RWF " . number_format($estimatedRefund) . " needs approval",
                        'severity' => \App\Enums\AlertSeverity::WARNING,
                        'entity_type' => 'return',
                        'entity_id' => $return->id,
                        'user_id' => $owner->id,
                        'action_url' => route('shop.returns.index'),
                        'action_label' => 'View Return',
                    ]);
                }

                session()->flash('success', "Return {$return->return_number} processed successfully. Awaiting owner approval due to large refund amount.");
            } else {
                session()->flash('success', "Return {$return->return_number} processed successfully.");
            }

            return redirect()->route('shop.returns.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to process return: ' . $e->getMessage());
            $this->showConfirmation = false;
        }
    }

    public function getEstimatedRefund(): int
    {
        if ($this->isExchange || count($this->items) === 0) {
            return 0;
        }

        $total = 0;
        foreach ($this->items as $item) {
            $total += ($item['unit_price'] ?? 0) * ($item['quantity_returned'] ?? 0);
        }
        return $total;
    }

    public function getSaleAgeDays()
    {
        $sale = $this->getLinkedSale();
        return $sale ? $sale->sale_date->diffInDays(now()) : 0;
    }

    public function render()
    {
        // Load the linked sale fresh for the view (not stored as public property)
        $linkedSale = $this->getLinkedSale();

        return view('livewire.shop.returns.process-return', [
            'linkedSale' => $linkedSale,
            'saleAgeDays' => $this->getSaleAgeDays(),
            'returnReasons' => ReturnReason::cases(),
            'totalItems' => count($this->items),
            'totalQuantity' => array_sum(array_column($this->items, 'quantity_returned')),
            'totalDamaged' => array_sum(array_column($this->items, 'quantity_damaged')),
            'totalGood' => array_sum(array_column($this->items, 'quantity_good')),
            'estimatedRefund' => $this->getEstimatedRefund(),
        ]);
    }
}
