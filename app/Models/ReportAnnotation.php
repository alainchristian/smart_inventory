<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAnnotation extends Model
{
    protected $fillable = [
        'report_id', 'run_history_id', 'block_id', 'note', 'created_by',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class, 'report_id');
    }

    public function runHistory(): BelongsTo
    {
        return $this->belongsTo(ReportRunHistory::class, 'run_history_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
