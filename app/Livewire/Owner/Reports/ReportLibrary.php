<?php
namespace App\Livewire\Owner\Reports;

use App\Models\SavedReport;
use Livewire\Component;
use Livewire\WithPagination;

class ReportLibrary extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all'; // all | mine | shared

    public function mount(): void
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilter(): void  { $this->resetPage(); }

    public function deleteReport(int $id): void
    {
        $report = SavedReport::findOrFail($id);
        if ($report->created_by !== auth()->id()) abort(403);
        $report->delete();
        session()->flash('success', 'Report deleted.');
    }

    public function duplicateReport(int $id): void
    {
        $source = SavedReport::findOrFail($id);
        SavedReport::create([
            'name'        => $source->name . ' (copy)',
            'description' => $source->description,
            'created_by'  => auth()->id(),
            'is_shared'   => false,
            'config'      => $source->config,
        ]);
        session()->flash('success', 'Report duplicated.');
    }

    public function render()
    {
        $user  = auth()->user();
        $query = SavedReport::with('creator')->withCount('viewLogs')->whereNull('deleted_at');

        if ($this->filter === 'mine') {
            $query->where('created_by', $user->id);
        } elseif ($this->filter === 'shared') {
            $query->where('is_shared', true);
        } else {
            $query->where(fn ($q) =>
                $q->where('created_by', $user->id)
                  ->orWhere('is_shared', true)
            );
        }

        if ($this->search) {
            $query->where('name', 'ilike', '%' . $this->search . '%');
        }

        $reports = $query->orderByDesc('last_run_at')->orderByDesc('created_at')->paginate(20);

        return view('livewire.owner.reports.report-library', [
            'reports' => $reports,
        ]);
    }
}
