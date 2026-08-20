<?php

namespace App\Livewire\Owner\Customers;

use App\Models\Customer;
use App\Models\Shop;
use App\Services\Sales\CustomerService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $outstandingOnly = false;

    // ── Drawer state ─────────────────────────────────────────────────────────
    public bool $showDrawer = false;
    public bool $isEditing  = false;
    public ?int $editingId  = null;

    // ── Form fields ──────────────────────────────────────────────────────────
    public string $form_name  = '';
    public string $form_phone = '';
    public string $form_email = '';
    public string $form_notes = '';
    public ?int $form_shop_id = null;

    protected $queryString = [
        'search'          => ['except' => ''],
        'outstandingOnly' => ['except' => false],
    ];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user->isOwner() && !$user->isAdmin()) {
            abort(403, 'Access denied. Owner and admin only.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingOutstandingOnly(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'outstandingOnly']);
        $this->resetPage();
    }

    // ── Drawer: open for create ─────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing  = false;
        $this->editingId  = null;
        $this->showDrawer = true;
    }

    // ── Drawer: open for edit ───────────────────────────────────────────────

    public function openEdit(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $customerId;

        $this->form_name    = $customer->name;
        $this->form_phone   = $customer->phone;
        $this->form_email   = $customer->email ?? '';
        $this->form_notes   = $customer->notes ?? '';
        $this->form_shop_id = $customer->shop_id;

        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->resetForm();
    }

    // ── Save (create or update) ─────────────────────────────────────────────

    public function save(CustomerService $customerService): void
    {
        $rules = [
            'form_name'    => 'required|string|min:2|max:100',
            'form_phone'   => ['required', 'string', 'min:10', 'max:20'],
            'form_email'   => 'nullable|email|max:100',
            'form_notes'   => 'nullable|string|max:500',
            // "Not shop-specific" (null) is intentional — see the drawer's
            // hint text. Every shop-scoped query reading Customer.shop_id
            // must treat NULL as "visible from any shop", not required
            // here. See CreditRepayments::getCustomersProperty().
            'form_shop_id' => 'nullable|exists:shops,id',
        ];

        $rules['form_phone'][] = $this->isEditing
            ? Rule::unique('customers', 'phone')->ignore($this->editingId)
            : Rule::unique('customers', 'phone');

        $data = $this->validate($rules);

        if ($this->isEditing) {
            $customer = Customer::findOrFail($this->editingId);
            $customer->update([
                'name'    => $data['form_name'],
                'phone'   => $data['form_phone'],
                'email'   => $data['form_email'] ?: null,
                'notes'   => $data['form_notes'] ?: null,
                'shop_id' => $data['form_shop_id'],
            ]);

            $this->dispatch('notification', ['type' => 'success', 'message' => "Customer \"{$customer->name}\" updated."]);
        } else {
            $customer = $customerService->create([
                'name'  => $data['form_name'],
                'phone' => $data['form_phone'],
                'email' => $data['form_email'] ?: null,
                'notes' => $data['form_notes'] ?: null,
            ], $data['form_shop_id']);

            $this->dispatch('notification', ['type' => 'success', 'message' => "Customer \"{$customer->name}\" registered."]);
        }

        $this->closeDrawer();
    }

    private function resetForm(): void
    {
        $this->form_name    = '';
        $this->form_phone   = '';
        $this->form_email   = '';
        $this->form_notes   = '';
        $this->form_shop_id = null;
        $this->resetValidation();
    }

    // ── Computed properties ─────────────────────────────────────────────────

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        $customers = Customer::query()
            ->with('shop')
            ->when($this->search, fn ($q) => $q
                ->where('phone', 'like', "%{$this->search}%")
                ->orWhere('name', 'ilike', "%{$this->search}%"))
            ->when($this->outstandingOnly, fn ($q) => $q->where('outstanding_balance', '>', 0))
            ->orderBy('name')
            ->paginate(25);

        $stats = [
            'total'       => Customer::count(),
            'outstanding' => Customer::where('outstanding_balance', '>', 0)->count(),
        ];

        return view('livewire.owner.customers.customer-list', [
            'customers' => $customers,
            'stats'     => $stats,
        ]);
    }
}
