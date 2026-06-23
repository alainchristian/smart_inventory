<?php

namespace App\Livewire\Owner\Categories;

use App\Models\ActivityLog;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────────────────────
    public string $search       = '';
    public string $statusFilter = 'all'; // all | active | inactive

    // ── Drawer ────────────────────────────────────────────────────────────────
    public bool  $showDrawer = false;
    public bool  $isEditing  = false;
    public ?int  $editingId  = null;

    // ── Form fields ───────────────────────────────────────────────────────────
    public string $form_name        = '';
    public string $form_code        = '';
    public string $form_description = '';
    public bool   $form_is_active   = true;

    // ── Toggle/Delete confirmation ────────────────────────────────────────────
    public ?int   $confirmToggleId      = null;
    public bool   $confirmToggleActive  = false;
    public string $confirmToggleName    = '';

    public ?int   $confirmDeleteId      = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (!auth()->user()->isOwner()) abort(403);
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing  = false;
        $this->editingId  = null;
        $this->showDrawer = true;
    }

    public function openEdit(int $id): void
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $cat = Category::findOrFail($id);
        $this->form_name        = $cat->name;
        $this->form_code        = $cat->code ?? '';
        $this->form_description = $cat->description ?? '';
        $this->form_is_active   = $cat->is_active;

        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'form_name'        => 'required|string|min:2|max:120',
            'form_code'        => 'nullable|string|max:20',
            'form_description' => 'nullable|string|max:500',
            'form_is_active'   => 'boolean',
        ], [
            'form_name.required' => 'Category name is required.',
        ]);

        $data = [
            'name'        => trim($this->form_name),
            'code'        => trim($this->form_code) ?: null,
            'description' => trim($this->form_description) ?: null,
            'is_active'   => $this->form_is_active,
        ];

        if ($this->isEditing) {
            $cat = Category::findOrFail($this->editingId);
            $cat->update($data);
            $action = 'updated';
            $msg    = 'Category updated successfully.';
        } else {
            $cat    = Category::create($data);
            $action = 'created';
            $msg    = 'Category created successfully.';
        }

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => $action,
            'entity_type'       => 'Product Category',
            'entity_id'         => $cat->id,
            'entity_identifier' => $cat->code ?? $cat->name,
            'ip_address'        => request()->ip(),
        ]);

        $this->dispatch('notification', ['type' => 'success', 'message' => $msg]);
        $this->closeDrawer();
    }

    public function confirmToggle(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->confirmToggleId     = $id;
        $this->confirmToggleActive = $cat->is_active;
        $this->confirmToggleName   = $cat->name;
    }

    public function executeToggle(): void
    {
        if (!$this->confirmToggleId) return;

        $cat = Category::findOrFail($this->confirmToggleId);
        $newState = !$cat->is_active;
        $cat->update(['is_active' => $newState]);

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => $newState ? 'activated' : 'deactivated',
            'entity_type'       => 'Product Category',
            'entity_id'         => $cat->id,
            'entity_identifier' => $cat->name,
            'ip_address'        => request()->ip(),
        ]);

        $label = $newState ? 'activated' : 'deactivated';
        $this->dispatch('notification', [
            'type'    => $newState ? 'success' : 'warning',
            'message' => "{$cat->name} has been {$label}.",
        ]);

        $this->cancelToggle();
    }

    public function cancelToggle(): void
    {
        $this->confirmToggleId   = null;
        $this->confirmToggleName = '';
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function deleteCategory(): void
    {
        if (!$this->confirmDeleteId) return;

        $cat = Category::findOrFail($this->confirmDeleteId);
        
        if ($cat->products()->count() > 0) {
            $this->dispatch('notification', ['type' => 'error', 'message' => 'Cannot delete - category has products.']);
            $this->confirmDeleteId = null;
            return;
        }

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => 'deleted',
            'entity_type'       => 'Product Category',
            'entity_id'         => $cat->id,
            'entity_identifier' => $cat->name,
            'ip_address'        => request()->ip(),
        ]);

        $cat->delete();
        $this->confirmDeleteId = null;
        $this->dispatch('notification', ['type' => 'success', 'message' => 'Category deleted successfully.']);
    }
    
    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    private function resetForm(): void
    {
        $this->form_name        = '';
        $this->form_code        = '';
        $this->form_description = '';
        $this->form_is_active   = true;
        $this->resetValidation();
    }

    public function render()
    {
        $stats = [
            'total'  => Category::count(),
            'active' => Category::where('is_active', true)->count(),
        ];

        $rows = Category::query()
            ->withCount('products')
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('name', 'ilike', "%{$this->search}%")
                       ->orWhere('code', 'ilike', "%{$this->search}%")
                )
            )
            ->when($this->statusFilter === 'active',   fn($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderByRaw('is_active DESC, name ASC')
            ->paginate(20);

        return view('livewire.owner.categories.category-manager', compact('rows', 'stats'));
    }
}
