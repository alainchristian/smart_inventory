<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportRunHistory extends Model
{
    protected $table = 'report_run_history';

    protected $fillable = [
        'report_id', 'run_by', 'run_at', 'config_snapshot',
        'results', 'duration_ms', 'was_scheduled',
    ];

    protected $casts = [
        'config_snapshot' => 'array',
        'results'         => 'array',
        'run_at'          => 'datetime',
        'was_scheduled'   => 'boolean',
        'duration_ms'     => 'integer',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class, 'report_id');
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(ReportAnnotation::class, 'run_history_id');
    }
}
