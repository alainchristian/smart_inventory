<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Models\Box;
use App\Models\Product;
use App\Services\Sales\SaleService;
use Livewire\Component;

class PointOfSale extends Component
{
    public int $shopId;
    public string $scanInput = '';
    public array $cart = [];
    public ?string $customerName = null;
    public ?string $customerPhone = null;
    public string $paymentMethod = 'cash';
    public int $discount = 0;
    public int $tax = 0;
    public bool $scannerEnabled = true;

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScan',
    ];

    protected $rules = [
        'paymentMethod' => 'required|in:cash,card,mobile_money,bank_transfer,credit',
        'customerName' => 'nullable|string|max:255',
        'customerPhone' => 'nullable|string|max:20',
        'discount' => 'integer|min:0',
        'tax' => 'integer|min:0',
    ];

    public function mount()
    {
        $user = auth()->user();

        if ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        } else {
            abort(403, 'Only shop managers can access POS');
        }
    }

    public function handleBarcodeScan($barcode)
    {
        $this->scanInput = $barcode;
        $this->scanProduct();
    }

    public function scanProduct()
    {
        $code = trim($this->scanInput);

        if (empty($code)) {
            return;
        }

        // Try to find product by barcode
        $product = Product::where('barcode', $code)->first();

        if ($product) {
            $this->addProductToCart($product->id);
        } else {
            // Try as box code
            $box = Box::where('box_code', $code)
                ->where('location_type', 'shop')
                ->where('location_id', $this->shopId)
                ->first();

            if ($box) {
                $this->addBoxToCart($box->id);
            } else {
                session()->flash('scan_error', 'Product or box not found');
                $this->dispatch('scan-error', message: "Not found: {$code}");
            }
        }

        $this->scanInput = '';
    }

    public function addProductToCart($productId, int $quantity = 1)
    {
        $product = Product::findOrFail($productId);

        // Find an available box
        $box = Box::where('location_type', 'shop')
            ->where('location_id', $this->shopId)
            ->where('product_id', $product->id)
            ->whereIn('status', ['full', 'partial'])
            ->where('items_remaining', '>', 0)
            ->orderBy('status') // Full boxes first
            ->first();

        if (!$box) {
            session()->flash('error', 'No stock available for this product');
            $this->dispatch('stock-error', productName: $product->name);
            return;
        }

        if ($quantity > $box->items_remaining) {
            session()->flash('error', "Only {$box->items_remaining} items available in stock");
            return;
        }

        $cartKey = "product_{$product->id}_box_{$box->id}";

        if (isset($this->cart[$cartKey])) {
            $newQuantity = $this->cart[$cartKey]['quantity'] + $quantity;
            if ($newQuantity > $box->items_remaining) {
                session()->flash('error', "Cannot add more items. Only {$box->items_remaining} available.");
                return;
            }
            $this->cart[$cartKey]['quantity'] = $newQuantity;
        } else {
            $this->cart[$cartKey] = [
                'type' => 'item',
                'product_id' => $product->id,
                'box_id' => $box->id,
                'product_name' => $product->name,
                'box_code' => $box->box_code,
                'quantity' => $quantity,
                'unit_price' => $product->selling_price,
                'is_full_box' => false,
                'available' => $box->items_remaining,
            ];
        }

        $this->dispatch('item-added', productName: $product->name);
    }

    public function addBoxToCart($boxId)
    {
        $box = Box::with('product')->findOrFail($boxId);

        if ($box->location_type->value !== 'shop' || $box->location_id !== $this->shopId) {
            session()->flash('error', 'Box is not at this shop location');
            return;
        }

        if (!in_array($box->status->value, ['full', 'partial'])) {
            session()->flash('error', 'Box is not available for sale');
            return;
        }

        $cartKey = "box_{$box->id}";

        if (isset($this->cart[$cartKey])) {
            session()->flash('warning', 'Box already in cart');
            return;
        }

        $this->cart[$cartKey] = [
            'type' => 'box',
            'product_id' => $box->product_id,
            'box_id' => $box->id,
            'product_name' => $box->product->name,
            'box_code' => $box->box_code,
            'quantity' => 1,
            'unit_price' => $box->product->calculateBoxPrice(),
            'is_full_box' => true,
            'available' => 1,
        ];

        $this->dispatch('box-added', boxCode: $box->box_code);
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if (!isset($this->cart[$cartKey])) {
            return;
        }

        $item = $this->cart[$cartKey];

        if ($quantity > $item['available']) {
            session()->flash('error', 'Quantity exceeds available stock');
            return;
        }

        if ($quantity <= 0) {
            $this->removeItem($cartKey);
        } else {
            $this->cart[$cartKey]['quantity'] = $quantity;
        }
    }

    public function removeItem($cartKey)
    {
        unset($this->cart[$cartKey]);
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->customerName = null;
        $this->customerPhone = null;
        $this->discount = 0;
        $this->tax = 0;
        session()->flash('info', 'Cart cleared');
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->tax - $this->discount;
    }

    public function completeSale()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty');
            return;
        }

        $this->validate();

        try {
            $saleService = app(SaleService::class);

            $items = collect($this->cart)->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'box_id' => $item['box_id'],
                    'quantity' => $item['quantity'],
                    'is_full_box' => $item['is_full_box'],
                    'price' => $item['unit_price'],
                ];
            })->values()->toArray();

            $sale = $saleService->createSale([
                'shop_id' => $this->shopId,
                'type' => SaleType::MIXED,
                'payment_method' => PaymentMethod::from($this->paymentMethod),
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'items' => $items,
                'tax' => $this->tax,
                'discount' => $this->discount,
            ]);

            session()->flash('success', "Sale {$sale->sale_number} completed successfully");

            // Reset cart
            $this->clearCart();

            $this->dispatch('sale-completed', saleNumber: $sale->sale_number);

            return redirect()->route('sales.receipt', $sale);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sales.point-of-sale', [
            'products' => Product::active()->with('category')->orderBy('name')->get(),
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'itemCount' => count($this->cart),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }
}
