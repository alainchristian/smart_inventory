<?php

namespace App\Livewire\Owner\Transporters;

use App\Models\ActivityLog;
use App\Models\Transporter;
use Livewire\Component;
use Livewire\WithPagination;

class TransporterManager extends Component
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
    public string $form_name           = '';
    public string $form_company_name   = '';
    public string $form_phone          = '';
    public string $form_vehicle_number = '';
    public string $form_license_number = '';
    public string $form_notes          = '';
    public bool   $form_is_active      = true;

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

        $tr = Transporter::findOrFail($id);
        $this->form_name           = $tr->name;
        $this->form_company_name   = $tr->company_name ?? '';
        $this->form_phone          = $tr->phone ?? '';
        $this->form_vehicle_number = $tr->vehicle_number ?? '';
        $this->form_license_number = $tr->license_number ?? '';
        $this->form_notes          = $tr->notes ?? '';
        $this->form_is_active      = $tr->is_active;

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
            'form_name'           => 'required|string|max:120',
            'form_company_name'   => 'nullable|string|max:120',
            'form_phone'          => 'nullable|string|max:30',
            'form_vehicle_number' => 'nullable|string|max:50',
            'form_license_number' => 'nullable|string|max:50',
            'form_notes'          => 'nullable|string|max:500',
            'form_is_active'      => 'boolean',
        ], [
            'form_name.required' => 'Transporter name is required.',
        ]);

        $data = [
            'name'           => trim($this->form_name),
            'company_name'   => trim($this->form_company_name) ?: null,
            'phone'          => trim($this->form_phone) ?: null,
            'vehicle_number' => trim($this->form_vehicle_number) ?: null,
            'license_number' => trim($this->form_license_number) ?: null,
            'notes'          => trim($this->form_notes) ?: null,
            'is_active'      => $this->form_is_active,
        ];

        if ($this->isEditing) {
            $tr = Transporter::findOrFail($this->editingId);
            $tr->update($data);
            $action = 'updated';
            $msg    = 'Transporter updated successfully.';
        } else {
            $tr     = Transporter::create($data);
            $action = 'created';
            $msg    = 'Transporter created successfully.';
        }

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => $action,
            'entity_type'       => 'Transporter',
            'entity_id'         => $tr->id,
            'entity_identifier' => $tr->name,
            'ip_address'        => request()->ip(),
        ]);

        $this->dispatch('notification', ['type' => 'success', 'message' => $msg]);
        $this->closeDrawer();
    }

    public function confirmToggle(int $id): void
    {
        $tr = Transporter::findOrFail($id);
        $this->confirmToggleId     = $id;
        $this->confirmToggleActive = $tr->is_active;
        $this->confirmToggleName   = $tr->name;
    }

    public function executeToggle(): void
    {
        if (!$this->confirmToggleId) return;

        $tr = Transporter::findOrFail($this->confirmToggleId);
        $newState = !$tr->is_active;
        $tr->update(['is_active' => $newState]);

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => $newState ? 'activated' : 'deactivated',
            'entity_type'       => 'Transporter',
            'entity_id'         => $tr->id,
            'entity_identifier' => $tr->name,
            'ip_address'        => request()->ip(),
        ]);

        $label = $newState ? 'activated' : 'deactivated';
        $this->dispatch('notification', [
            'type'    => $newState ? 'success' : 'warning',
            'message' => "{$tr->name} has been {$label}.",
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

    public function deleteTransporter(): void
    {
        if (!$this->confirmDeleteId) return;

        $tr = Transporter::findOrFail($this->confirmDeleteId);
        
        if ($tr->transfers()->count() > 0) {
            $this->dispatch('notification', ['type' => 'error', 'message' => 'Cannot delete - transporter has transfer records.']);
            $this->confirmDeleteId = null;
            return;
        }

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => 'deleted',
            'entity_type'       => 'Transporter',
            'entity_id'         => $tr->id,
            'entity_identifier' => $tr->name,
            'ip_address'        => request()->ip(),
        ]);

        $tr->delete();
        $this->confirmDeleteId = null;
        $this->dispatch('notification', ['type' => 'success', 'message' => 'Transporter deleted successfully.']);
    }
    
    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    private function resetForm(): void
    {
        $this->form_name           = '';
        $this->form_company_name   = '';
        $this->form_phone          = '';
        $this->form_vehicle_number = '';
        $this->form_license_number = '';
        $this->form_notes          = '';
        $this->form_is_active      = true;
        $this->resetValidation();
    }

    public function render()
    {
        $stats = [
            'total'  => Transporter::count(),
            'active' => Transporter::where('is_active', true)->count(),
        ];

        $rows = Transporter::query()
            ->withCount('transfers')
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('name', 'ilike', "%{$this->search}%")
                       ->orWhere('company_name', 'ilike', "%{$this->search}%")
                       ->orWhere('vehicle_number', 'ilike', "%{$this->search}%")
                       ->orWhere('phone', 'ilike', "%{$this->search}%")
                )
            )
            ->when($this->statusFilter === 'active',   fn($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderByRaw('is_active DESC, name ASC')
            ->paginate(20);

        return view('livewire.owner.transporters.transporter-manager', compact('rows', 'stats'));
    }
}
