<?php

namespace App\Livewire\Owner\Categories;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
    public string $form_parent_id   = ''; // '' = top-level
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
        if (!auth()->user()->isOwner() && !auth()->user()->isAdmin()) abort(403);
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
        $this->form_parent_id   = (string) ($cat->parent_id ?? '');
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
            'form_code'        => 'nullable|string|max:20|unique:categories,code' . ($this->isEditing ? ',' . $this->editingId : ''),
            'form_description' => 'nullable|string|max:500',
            'form_parent_id'   => 'nullable|integer|exists:categories,id',
            'form_is_active'   => 'boolean',
        ], [
            'form_name.required' => 'Category name is required.',
            'form_code.unique'   => 'Another category already uses this code.',
        ]);

        $parentId = $this->form_parent_id !== '' ? (int) $this->form_parent_id : null;

        // A category can't sit under itself or under one of its own sub-categories
        if ($this->isEditing && $parentId !== null
            && in_array($parentId, [$this->editingId, ...$this->descendantIds($this->editingId)], true)) {
            $this->addError('form_parent_id', "A category can't be placed under itself or one of its own sub-categories.");
            return;
        }

        $data = [
            'name'        => trim($this->form_name),
            'code'        => trim($this->form_code) ?: $this->codeFromName(trim($this->form_name)),
            'description' => trim($this->form_description) ?: null,
            'parent_id'   => $parentId,
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

        if ($cat->children()->exists()) {
            $this->dispatch('notification', ['type' => 'error', 'message' => "Cannot delete - {$cat->name} has sub-categories. Move or delete them first."]);
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
        $this->form_parent_id   = '';
        $this->form_is_active   = true;
        $this->resetValidation();
    }

    /**
     * categories.code is required + unique; the form leaves it optional, so
     * derive one from the name ("Bags & Accessories" → BAGS-ACCESSORIES).
     */
    private function codeFromName(string $name): string
    {
        $base = substr(strtoupper(\Illuminate\Support\Str::slug($name)), 0, 16) ?: 'CAT';
        $code = $base;
        for ($n = 2; Category::withTrashed()->where('code', $code)->where('id', '!=', $this->editingId ?? 0)->exists(); $n++) {
            $code = $base . '-' . $n;
        }

        return $code;
    }

    /** Ids of every category below $id (any depth). */
    private function descendantIds(int $id): array
    {
        $all = Category::pluck('parent_id', 'id');
        $found = [];
        $queue = [$id];
        while ($queue) {
            $current = array_shift($queue);
            foreach ($all as $childId => $parentId) {
                if ((int) $parentId === $current && ! in_array($childId, $found, true)) {
                    $found[] = $childId;
                    $queue[] = $childId;
                }
            }
        }

        return $found;
    }

    /**
     * Categories in tree order (each parent followed by its sub-categories),
     * each with a `level` attribute for indenting.
     */
    private function treeOrder(Collection $cats): Collection
    {
        $byParent = $cats->groupBy(fn ($c) => $c->parent_id ?? 0);
        $ids = $cats->pluck('id')->all();
        $out = collect();
        $walk = function ($parentId, int $level) use (&$walk, $byParent, $out) {
            foreach ($byParent->get($parentId, collect())->sortBy(fn ($c) => [! $c->is_active, strtolower($c->name)]) as $cat) {
                $cat->level = $level;
                $out->push($cat);
                $walk($cat->id, $level + 1);
            }
        };
        // Roots: top-level categories, plus any whose parent is filtered out (search / status)
        foreach ($cats->filter(fn ($c) => $c->parent_id === null || ! in_array($c->parent_id, $ids, true))
                      ->sortBy(fn ($c) => [! $c->is_active, strtolower($c->name)]) as $root) {
            $root->level = 0;
            $out->push($root);
            $walk($root->id, 1);
        }

        return $out;
    }

    public function getParentOptionsProperty(): Collection
    {
        $exclude = $this->isEditing && $this->editingId
            ? [$this->editingId, ...$this->descendantIds($this->editingId)]
            : [];

        return $this->treeOrder(Category::orderBy('name')->get(['id', 'name', 'parent_id', 'is_active']))
            ->reject(fn ($c) => in_array($c->id, $exclude, true))
            ->values();
    }

    public function render()
    {
        $stats = [
            'total'  => Category::count(),
            'active' => Category::where('is_active', true)->count(),
        ];

        $matching = Category::query()
            ->with('parent:id,name')
            ->withCount(['products', 'children'])
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('name', 'ilike', "%{$this->search}%")
                       ->orWhere('code', 'ilike', "%{$this->search}%")
                )
            )
            ->when($this->statusFilter === 'active',   fn($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
            ->get();

        $tree = $this->treeOrder($matching);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator($tree->forPage($page, 20)->values(), $tree->count(), 20, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return view('livewire.owner.categories.category-manager', compact('rows', 'stats'));
    }
}
