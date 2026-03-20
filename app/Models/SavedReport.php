<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavedReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'created_by',
        'is_shared', 'config', 'last_run_at', 'run_count',
        'last_results', 'results_cached_at', 'results_stale_at',
        'schedule_cron', 'schedule_recipients', 'last_scheduled_run_at',
        'pinned_to_dashboard', 'dashboard_position',
    ];

    protected $casts = [
        'config'                => 'array',
        'is_shared'             => 'boolean',
        'last_run_at'           => 'datetime',
        'run_count'             => 'integer',
        'last_results'          => 'array',
        'results_cached_at'     => 'datetime',
        'results_stale_at'      => 'datetime',
        'schedule_recipients'   => 'array',
        'last_scheduled_run_at' => 'datetime',
        'pinned_to_dashboard'   => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Returns config with safe defaults applied */
    public function resolvedConfig(): array
    {
        return array_merge([
            'date_range'      => 'month',
            'date_from'       => null,
            'date_to'         => null,
            'location_filter' => 'all',
            'blocks'          => [],
        ], $this->config ?? []);
    }

    public function blockCount(): int
    {
        return count($this->resolvedConfig()['blocks'] ?? []);
    }

    public function hasFreshCache(): bool
    {
        return $this->results_stale_at !== null
            && $this->results_stale_at->isFuture()
            && $this->last_results !== null;
    }

    public function cacheResults(array $results, int $ttlHours = 4): void
    {
        $this->update([
            'last_results'      => $results,
            'results_cached_at' => now(),
            'results_stale_at'  => now()->addHours($ttlHours),
            'last_run_at'       => now(),
            'run_count'         => $this->run_count + 1,
        ]);
    }

    public function viewLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReportViewLog::class, 'report_id');
    }

    public function runHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReportRunHistory::class, 'report_id')
                    ->orderByDesc('run_at');
    }

    public function annotations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReportAnnotation::class, 'report_id')
                    ->orderByDesc('created_at');
    }
}
