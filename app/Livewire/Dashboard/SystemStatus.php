<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
use App\Models\Alert;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SystemStatus extends Component
{
    public bool $dbOk          = true;
    public bool $queueOk       = true;
    public bool $posOk         = true;
    public bool $allOk         = true;
    public int  $criticalCount = 0;

    public function mount(): void
    {
        $this->runChecks();
    }

    public function runChecks(): void
    {
        try {
            DB::select('SELECT 1');
            $this->dbOk = true;
        } catch (\Exception $e) {
            $this->dbOk = false;
        }

        $this->queueOk = !DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHour())->exists();

        $this->posOk = Sale::where('created_at', '>=', now()->subHours(4))->exists();

        $this->criticalCount = Alert::where('severity', 'critical')
            ->where('is_resolved', false)->count();

        $this->allOk = $this->dbOk && $this->queueOk;
    }

    public function render()
    {
        return view('livewire.dashboard.system-status');
    }
}
