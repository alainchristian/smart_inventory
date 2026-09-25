<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReturnBox extends Model
{
    public const RECEIVED = 'received';
    public const DAMAGED  = 'damaged';
    public const MISSING  = 'missing';

    protected $fillable = ['stock_return_id', 'box_id', 'items_sent', 'previous_status', 'outcome'];

    protected $casts = ['items_sent' => 'integer'];

    public function stockReturn(): BelongsTo { return $this->belongsTo(StockReturn::class); }
    public function box(): BelongsTo         { return $this->belongsTo(Box::class); }
}
