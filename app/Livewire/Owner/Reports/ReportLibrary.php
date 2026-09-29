<?php
namespace App\Livewire\Owner\Reports;

use App\Models\SavedReport;
use App\Services\Reports\ReportTemplates;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The list of custom reports the owner can open: their own and those
 * other owners shared. Edit, share and delete are for the creator only.
 */
class ReportLibrary extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';
    #[Url(except: 'all')]
    public string $filter = 'all';        // all | mine | shared
    #[Url(except: 'last_run')]
    public string $sortBy = 'last_run';   // last_run | run_count | alpha | created

    /** Row showing the inline delete confirmation */
    public ?int $confirmDeleteId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->isAdmin(), 403);
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilter(): void { $this->resetPage(); }
    public function updatingSortBy(): void { $this->resetPage(); }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'mine', 'shared'], true) ? $filter : 'all';
        $this->resetPage();
    }

    public function askDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function deleteReport(int $id): void
    {
        $report = SavedReport::findOrFail($id);
        abort_unless($report->created_by === auth()->id(), 403);

        $report->delete();
        $this->confirmDeleteId = null;
        $this->toast('success', "Deleted \"{$report->name}\".");
    }

    public function duplicateReport(int $id): void
    {
        $source = SavedReport::findOrFail($id);
        abort_unless($source->isVisibleTo(auth()->user()), 403);

        $copy = SavedReport::create([
            'name'        => \Illuminate\Support\Str::limit($source->name . ' (copy)', 120, ''),
            'description' => $source->description,
            'created_by'  => auth()->id(),
            'is_shared'   => false,
            'config'      => $source->resolvedConfig(),
        ]);
        $this->toast('success', "Made a copy: \"{$copy->name}\". It's private until you share it.");
    }

    public function toggleShare(int $id): void
    {
        $report = SavedReport::findOrFail($id);
        abort_unless($report->created_by === auth()->id(), 403);

        $report->update(['is_shared' => ! $report->is_shared]);
        $this->toast('success', $report->is_shared ? 'Shared with the other owners.' : 'No longer shared.');
    }

    private function toast(string $type, string $message): void
    {
        $this->dispatch('notification', ['type' => $type, 'message' => $message]);
    }

    public function render()
    {
        $userId = auth()->id();
        $base = SavedReport::query()->where(fn ($q) => $q->where('created_by', $userId)->orWhere('is_shared', true));

        $counts = (clone $base)->selectRaw('COUNT(*) AS all_count')
            ->selectRaw('COUNT(*) FILTER (WHERE created_by = ?) AS mine_count', [$userId])
            ->selectRaw('COUNT(*) FILTER (WHERE is_shared AND created_by <> ?) AS shared_count', [$userId])
            ->first();

        $query = (clone $base)->with('creator:id,name');
        match ($this->filter) {
            'mine'   => $query->where('created_by', $userId),
            'shared' => $query->where('is_shared', true)->where('created_by', '<>', $userId),
            default  => null,
        };

        if ($term = trim($this->search)) {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
            $query->where(fn ($q) => $q->where('name', 'ilike', $like)->orWhere('description', 'ilike', $like));
        }

        match ($this->sortBy) {
            'run_count' => $query->orderByDesc('run_count')->orderBy('name'),
            'alpha'     => $query->orderBy('name'),
            'created'   => $query->orderByDesc('created_at'),
            default     => $query->orderByRaw('last_run_at DESC NULLS LAST')->orderByDesc('created_at'),
        };

        return view('livewire.owner.reports.report-library', [
            'reports'   => $query->paginate(20),
            'counts'    => $counts,
            'templates' => app(ReportTemplates::class)->list(),
        ]);
    }
}
