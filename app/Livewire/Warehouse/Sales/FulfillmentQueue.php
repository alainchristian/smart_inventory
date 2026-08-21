<?php

namespace App\Livewire\Warehouse\Sales;

use App\Models\ActivityLog;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\SettingsService;
use Livewire\Component;

class FulfillmentQueue extends Component
{
    public int $warehouseId;
    public string $warehouseName = '';
    public string $dispatchMethod = 'queue';
    // Which section tab is showing — 'pending' (scan/queue UI) or 'history'.
    // Unrelated to $dispatchMethod (the business setting for HOW pending
    // orders are found); this is purely which of the two tabs is visible.
    public string $activeTab = 'pending';
    public ?int $expandedHistoryId = null;

    // Confirm state
    public ?int $confirmingFulfillmentId = null;
    public string $recipientName = '';
    public ?string $signatureData = null;
    // Which UI surfaced the order being confirmed ('queue' or 'scan') — carried
    // into the ActivityLog detail string purely for audit traceability.
    public string $fulfillmentSource = 'scan';

    // Primary lookup: scan (or manually type) the receipt/sale number
    public string $receiptCode = '';
    public ?string $scannedCode = null;

    // Secondary fallback: search by customer name or phone
    public bool $showCustomerSearch = false;
    public string $customerSearch = '';

    // Queue (list) mode — browsable pending orders
    public string $queueDateFilter = 'all';
    public string $queueCustomerSearch = '';

    // Dispatch History filters (shared by both dispatch methods)
    public string $historyDateFilter = 'all';
    public string $historyCustomerSearch = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            $warehouse = Warehouse::first();
        } else {
            $warehouse = Warehouse::find($user->location_id);
        }

        if (!$warehouse) {
            abort(403, 'No warehouse assigned.');
        }

        $this->warehouseId   = $warehouse->id;
        $this->warehouseName = $warehouse->name;
        $this->dispatchMethod = app(SettingsService::class)->fulfillmentDispatchMethod();
    }

    /**
     * Shared period-filter resolver for both the pending queue (sale_date)
     * and dispatch history (fulfillment_confirmed_at) filters. Same preset
     * shape as SalesIndex::getDateRange().
     */
    protected function resolveDateRange(string $filter): array
    {
        $now = business_now();

        return match ($filter) {
            'today'      => [$now->copy()->startOfDay()->utc(),               $now->copy()->endOfDay()->utc()],
            'yesterday'  => [$now->copy()->subDay()->startOfDay()->utc(),     $now->copy()->subDay()->endOfDay()->utc()],
            'this_week'  => [$now->copy()->startOfWeek()->utc(),              $now->copy()->endOfDay()->utc()],
            'this_month' => [$now->copy()->startOfMonth()->utc(),             $now->copy()->endOfDay()->utc()],
            'last_30'    => [$now->copy()->subDays(29)->startOfDay()->utc(),  $now->copy()->endOfDay()->utc()],
            default      => [null, null],
        };
    }

    public function getPendingSalesProperty(): \Illuminate\Database\Eloquent\Collection
    {
        [$from, $to] = $this->resolveDateRange($this->queueDateFilter);
        $term = trim($this->queueCustomerSearch);

        return Sale::warehouseDirect()
            ->pendingFulfillment()
            ->where('source_warehouse_id', $this->warehouseId)
            ->when($from && $to, fn ($q) => $q->dateRange($from, $to))
            ->when($term !== '', fn ($q) => $q->where(function ($q2) use ($term) {
                $like = '%' . $term . '%';
                $q2->where('customer_name', 'ilike', $like)
                   ->orWhere('customer_phone', 'ilike', $like);
            }))
            ->with(['items.box', 'items.product', 'shop', 'fulfillmentTransporter', 'payments'])
            ->orderByDesc('sale_date')
            ->get();
    }

    public function getFulfilledHistoryProperty(): \Illuminate\Database\Eloquent\Collection
    {
        [$from, $to] = $this->resolveDateRange($this->historyDateFilter);
        $term = trim($this->historyCustomerSearch);

        return Sale::warehouseDirect()
            ->where('source_warehouse_id', $this->warehouseId)
            ->where('fulfillment_status', 'fulfilled')
            ->when($from && $to, fn ($q) => $q->whereBetween('fulfillment_confirmed_at', [$from, $to]))
            ->when($term !== '', fn ($q) => $q->where(function ($q2) use ($term) {
                $like = '%' . $term . '%';
                $q2->where('customer_name', 'ilike', $like)
                   ->orWhere('customer_phone', 'ilike', $like);
            }))
            ->with(['items.box', 'items.product', 'shop', 'fulfillmentTransporter', 'fulfillmentConfirmedBy', 'payments', 'soldBy'])
            ->latest('fulfillment_confirmed_at')
            ->limit(100)
            ->get();
    }

    /**
     * The sale matching the last scanned/typed receipt code, regardless of
     * its fulfillment status — a match that's already fulfilled or was
     * cancelled must still surface (with a clear status), not silently
     * disappear as "not found".
     */
    public function getScannedSaleProperty(): ?Sale
    {
        if (!$this->scannedCode) {
            return null;
        }

        return Sale::warehouseDirect()
            ->where('source_warehouse_id', $this->warehouseId)
            ->where('fulfillment_pickup_code', $this->scannedCode)
            ->with(['items.box', 'items.product', 'shop', 'fulfillmentTransporter', 'fulfillmentConfirmedBy', 'payments', 'soldBy'])
            ->first();
    }

    /**
     * Secondary fallback lookup: fuzzy match on customer name/phone only
     * (receipt number matching is the scan field's job). Same as above,
     * not restricted to pending — surfaces fulfilled/cancelled matches too.
     */
    public function getCustomerSearchResultsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        $term = trim($this->customerSearch);
        if ($term === '') {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        $like = '%' . $term . '%';

        return Sale::warehouseDirect()
            ->where('source_warehouse_id', $this->warehouseId)
            ->where(function ($q) use ($like) {
                $q->where('customer_name', 'ilike', $like)
                  ->orWhere('customer_phone', 'ilike', $like);
            })
            ->with(['items.box', 'items.product', 'shop', 'fulfillmentTransporter', 'fulfillmentConfirmedBy', 'payments', 'soldBy'])
            ->latest('sale_date')
            ->limit(10)
            ->get();
    }

    public function scanReceipt(): void
    {
        // Normalize so dashes/spacing/case from a manual type-in (the
        // no-scanner fallback) still matches the raw stored code.
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->receiptCode));
        $this->receiptCode = '';

        if ($code === '') {
            return;
        }

        $this->scannedCode   = $code;
        $this->customerSearch = '';
    }

    public function clearScan(): void
    {
        $this->scannedCode = null;
    }

    public function toggleCustomerSearch(): void
    {
        $this->showCustomerSearch = !$this->showCustomerSearch;
        $this->customerSearch     = '';
        $this->scannedCode        = null;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['pending', 'history'], true) ? $tab : 'pending';
    }

    public function toggleHistory(int $saleId): void
    {
        $this->expandedHistoryId = $this->expandedHistoryId === $saleId ? null : $saleId;
    }

    public function requestFulfillment(int $saleId, string $source = 'scan'): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isWarehouseManager()) {
            session()->flash('error', 'You are not authorized to confirm fulfillment.');
            return;
        }

        $this->confirmingFulfillmentId = $saleId;
        $this->fulfillmentSource = in_array($source, ['queue', 'scan'], true) ? $source : 'scan';
        $this->recipientName = '';
        $this->signatureData = null;
    }

    public function cancelFulfillment(): void
    {
        $this->confirmingFulfillmentId = null;
        $this->fulfillmentSource = 'scan';
        $this->recipientName = '';
        $this->signatureData = null;
    }

    public function markFulfilled(int $saleId): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isWarehouseManager()) {
            session()->flash('error', 'You are not authorized to confirm fulfillment.');
            return;
        }

        $sale = Sale::warehouseDirect()
            ->pendingFulfillment()
            ->where('source_warehouse_id', $this->warehouseId)
            ->findOrFail($saleId);

        $this->validate([
            'recipientName' => 'required|string|max:255',
            'signatureData' => 'required|string',
        ], [
            'recipientName.required' => 'Enter who is picking this up before confirming.',
            'signatureData.required' => 'Please capture a signature before confirming.',
        ]);
        $recipientName = trim($this->recipientName);
        $signature     = $this->signatureData;

        $sale->update([
            'fulfillment_status'         => 'fulfilled',
            'fulfillment_recipient_name' => $recipientName,
            'fulfillment_signature'      => $signature,
            'fulfillment_confirmed_at'   => now(),
            'fulfillment_confirmed_by'   => auth()->id(),
        ]);

        $viaLabel = $this->fulfillmentSource === 'queue' ? ' (via queue)' : ' (via scan)';

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'action'            => 'fulfillment_confirmed',
            'entity_type'       => 'Sale',
            'entity_id'         => $sale->id,
            'entity_identifier' => $sale->sale_number,
            'details'           => ($recipientName
                ? "Fulfillment confirmed at {$this->warehouseName} — handed to {$recipientName}"
                : "Fulfillment confirmed at {$this->warehouseName}") . $viaLabel,
        ]);

        $this->confirmingFulfillmentId = null;
        $this->fulfillmentSource = 'scan';
        $this->recipientName = '';
        $this->signatureData = null;
    }

    public function render()
    {
        return view('livewire.warehouse.sales.fulfillment-queue', [
            'fulfilledHistory'      => $this->fulfilledHistory,
            'scannedSale'           => $this->scannedSale,
            'customerSearchResults' => $this->customerSearchResults,
            'pendingSales'          => $this->dispatchMethod === 'queue' ? $this->pendingSales : null,
        ]);
    }
}
