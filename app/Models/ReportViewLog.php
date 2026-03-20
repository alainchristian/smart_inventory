<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportViewLog extends Model
{
    protected $table = 'report_view_log';
    public $timestamps = false;
    protected $fillable = ['report_id', 'viewed_by', 'viewed_at', 'was_run'];
    protected $casts = ['viewed_at' => 'datetime', 'was_run' => 'boolean'];
}
