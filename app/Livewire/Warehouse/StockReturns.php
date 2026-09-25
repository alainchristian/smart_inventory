<?php

namespace App\Livewire\Warehouse;

use App\Models\StockReturn;
use App\Models\StockReturnBox;
use App\Services\Inventory\StockReturnService;
use Livewire\Component;

/**
 * Warehouse side of Return to warehouse (warehouse.stock-returns): incoming
 * returns and receiving them box by box (received / damaged / missing).
 */
class StockReturns extends Component
{
    public ?int $receivingId = null;
    /** box_id => received | damaged | missing */
    public array $outcomes = [];
    public string $notes = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isWarehouseManager()) {
            abort(403);
        }
    }

    private function scoped()
    {
        $user = auth()->user();

        return StockReturn::query()->when(! $user->isOwner(), fn ($q) => $q->where('warehouse_id', $user->location_id));
    }

    public function openReceive(int $id): void
    {
        $return = $this->scoped()->with('boxes')->findOrFail($id);
        $this->receivingId = $return->id;
        $this->outcomes    = $return->boxes->mapWithKeys(fn ($b) => [$b->box_id => StockReturnBox::RECEIVED])->all();
        $this->notes       = '';
        $this->resetErrorBag();
    }

    public function closeReceive(): void
    {
        $this->receivingId = null;
        $this->outcomes    = [];
    }

    public function confirmReceive(): void
    {
        $return = $this->scoped()->findOrFail($this->receivingId);

        $problems = collect($this->outcomes)->filter(fn ($o) => $o !== StockReturnBox::RECEIVED)->count();
        if ($problems > 0 && trim($this->notes) === '') {
            $this->addError('notes', 'Add a note explaining the damaged or missing boxes.');
            return;
        }

        try {
            app(StockReturnService::class)->receive($return, $this->outcomes, trim($this->notes), auth()->user());
            $this->dispatch('notification', ['type' => 'success', 'message' => "{$return->return_number} received"
                . ($problems ? " — {$problems} box(es) flagged." : ' — boxes are back in warehouse stock.')]);
            $this->closeReceive();
        } catch (\Throwable $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $receiving = $this->receivingId
            ? $this->scoped()->with(['shop:id,name', 'boxes.box.product:id,name,sku'])->find($this->receivingId)
            : null;

        return view('livewire.warehouse.stock-returns', [
            'incoming'  => $this->scoped()->where('status', StockReturn::IN_TRANSIT)->with(['shop:id,name', 'sentBy:id,name', 'boxes.box.product:id,name'])->oldest('sent_at')->get(),
            'history'   => $this->scoped()->where('status', '!=', StockReturn::IN_TRANSIT)->with(['shop:id,name', 'receivedBy:id,name', 'cancelledBy:id,name'])->withCount('boxes')->latest('updated_at')->limit(30)->get(),
            'receiving' => $receiving,
        ]);
    }
}
