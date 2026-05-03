<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'entity_type',
        'entity_id',
        'entity_identifier',
        'old_values',
        'new_values',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    // No updates or soft deletes - immutable log
    public const UPDATED_AT = null;

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getChanges(): array
    {
        $changes = [];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;

                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return $changes;
    }

    // ── Notification helpers ─────────────────────────────────────────────────

    public function humanLabel(): string
    {
        return match($this->action) {
            'sale_created'           => 'New Sale',
            'sale_voided'            => 'Sale Voided',
            'price_modified'         => 'Price Modified',
            'transfer_requested'     => 'Transfer Requested',
            'transfer_approved'      => 'Transfer Approved',
            'transfer_rejected'      => 'Transfer Rejected',
            'transfer_packed'        => 'Transfer Packed',
            'transfer_received'      => 'Transfer Received',
            'transfer_discrepancy'   => 'Transfer Discrepancy',
            'daily_session_opened'   => 'Day Opened',
            'daily_session_closed'   => 'Day Closed',
            'return'                 => 'Return Submitted',
            'return_approved'        => 'Return Approved',
            'box_damaged'            => 'Box Damaged',
            'box_adjustment'         => 'Stock Adjusted',
            'credit_writeoff'        => 'Credit Written Off',
            default                  => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    public function iconKey(): string
    {
        return match($this->entity_type) {
            'Transfer'     => 'transfer',
            'Sale'         => 'sale',
            'DailySession' => 'session',
            'Return'       => 'return',
            'Expense'      => 'expense',
            'Box'          => 'box',
            default        => 'activity',
        };
    }

    public function colorKey(): string
    {
        return match($this->action) {
            'transfer_rejected', 'sale_voided', 'transfer_discrepancy', 'box_damaged' => 'red',
            'transfer_approved', 'transfer_received', 'daily_session_closed'           => 'green',
            'transfer_requested', 'return', 'price_modified'                           => 'amber',
            default                                                                     => 'accent',
        };
    }

    public function actionUrl(User $viewer): ?string
    {
        if ($this->entity_type === 'Transfer' && $this->entity_id) {
            try {
                if ($viewer->isOwner()) {
                    return route('owner.transfers.show', $this->entity_id);
                }
                if ($viewer->isWarehouseManager()) {
                    return route('warehouse.transfers.show', $this->entity_id);
                }
                if ($viewer->isShopManager()) {
                    return route('shop.transfers.show', $this->entity_id);
                }
            } catch (\Exception) {}
        }

        if ($this->entity_type === 'Sale' && $this->entity_id) {
            try {
                if ($viewer->isOwner()) {
                    return route('owner.sales.show', $this->entity_id);
                }
                return route('shop.sales.index');
            } catch (\Exception) {}
        }

        if (in_array($this->entity_type, ['DailySession', 'daily_session'])) {
            try {
                if ($viewer->isOwner()) return route('owner.finance.daily');
                return route('shop.session.history');
            } catch (\Exception) {}
        }

        return null;
    }

    public function subtitle(): string
    {
        $who = $this->user_name ?? 'Unknown';

        if ($this->entity_type === 'Transfer' && $this->entity_identifier) {
            return "{$who} · {$this->entity_identifier}";
        }

        if ($this->entity_type === 'Sale') {
            $total = isset($this->details['total'])
                ? number_format($this->details['total']) . ' RWF'
                : ($this->entity_identifier ?? '');
            return "{$who} · {$total}";
        }

        if ($this->entity_identifier) {
            return "{$who} · {$this->entity_identifier}";
        }

        return $who;
    }

    // ── Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
                    ->where('entity_id', $entityId);
    }

    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
