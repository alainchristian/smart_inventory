<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Routine platform-support entries the business doesn't need in its feed.
     * Everything support CHANGES (accounts, roles, passwords, 2FA, failed
     * sign-ins) stays visible; only this day-to-day noise is hidden from them.
     */
    public const SUPPORT_ROUTINE_ACTIONS = [
        'signed_in', 'signed_out', 'permission_denied',
        'support_view_as_started', 'support_view_as_stopped',
    ];

    /**
     * Rows the current viewer may see. Platform support sees everything; the
     * business doesn't see support's routine entries. Use this on every
     * business-facing read of the log.
     */
    public function scopeVisibleToViewer($query, ?User $viewer = null)
    {
        $viewer ??= auth()->user();

        if ($viewer?->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->whereRaw("COALESCE(activity_logs.actor_role_snapshot, '') <> 'super_admin'")
              ->orWhereNotIn('activity_logs.action', self::SUPPORT_ROUTINE_ACTIONS);
        });
    }

    /**
     * Fill in what a caller didn't say, for EVERY write path (AuditLogger, the
     * Auditable trait and the many direct ActivityLog::create calls):
     *  - the actor's role at the time (was missing on ~30% of entries)
     *  - where it happened: an explicit location, else a shop_id / warehouse_id
     *    carried in the entry, else the actor's own assigned location.
     * Never allowed to break the action being logged.
     */
    protected static function booted(): void
    {
        static::creating(function (self $log) {
            try {
                $actor = $log->user_id ? User::find($log->user_id) : null;

                if ($log->actor_role_snapshot === null && $actor) {
                    $log->actor_role_snapshot = $actor->role?->value;
                }

                if ($log->location_id === null) {
                    [$type, $id] = self::locationFromEntry($log) ?? self::locationOf($actor) ?? [null, null];
                    if ($type && $id) {
                        $log->location_type = $type;
                        $log->location_id   = $id;
                    }
                }

                if ($log->location_id !== null && $log->location_name === null) {
                    $log->location_name = match ($log->location_type) {
                        'shop'      => \App\Models\Shop::whereKey($log->location_id)->value('name'),
                        'warehouse' => \App\Models\Warehouse::whereKey($log->location_id)->value('name'),
                        default     => null,
                    };
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    /** A shop_id / warehouse_id carried in the entry's own values wins over the actor's assignment. */
    private static function locationFromEntry(self $log): ?array
    {
        foreach ([$log->details, $log->new_values, $log->old_values] as $values) {
            if (! is_array($values)) {
                continue;
            }
            if (! empty($values['shop_id']) && is_numeric($values['shop_id'])) {
                return ['shop', (int) $values['shop_id']];
            }
            if (! empty($values['warehouse_id']) && is_numeric($values['warehouse_id'])) {
                return ['warehouse', (int) $values['warehouse_id']];
            }
        }

        return null;
    }

    /** [type, id] of a user's assigned shop or warehouse (owners and support have none). */
    public static function locationOf(?User $user): ?array
    {
        if (! $user || ! $user->location_id || ! $user->location_type) {
            return null;
        }

        return [$user->location_type->value, (int) $user->location_id];
    }

    protected $fillable = [
        'location_type',
        'location_id',
        'location_name',
        'user_id',
        'user_name',
        'actor_type',
        'actor_role_snapshot',
        'action',
        'module',
        'entity_type',
        'entity_id',
        'entity_identifier',
        'old_values',
        'new_values',
        'details',
        'status',
        'severity',
        'session_id',
        'device_fingerprint',
        'trace_id',
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
            'mixed_sale_created'     => 'New Sale',
            'warehouse_direct_sale'  => 'New Sale',
            'sale_voided'            => 'Sale Voided',
            'price_modified'         => 'Price Modified',
            'transfer_requested'     => 'Transfer Requested',
            'transfer_approved'      => 'Transfer Approved',
            'transfer_rejected'      => 'Transfer Rejected',
            'transfer_packed'        => 'Transfer Packed',
            'transfer_received'      => 'Transfer Received',
            'transfer_discrepancy'   => 'Transfer Discrepancy',
            'transfer_ready'         => 'Transfer Packed, Ready to Dispatch',
            'transfer_dispatched'    => 'Transfer On Its Way',
            'transfer_arrived'       => 'Transfer Arrived at the Shop',
            'transfer_cancelled'     => 'Transfer Cancelled',
            'transfer_issue_resolved'=> 'Missing Box Resolved',
            'transfer_closed'        => 'Transfer Closed',
            'transfer_box_unpacked'  => 'Box Removed from Transfer',
            'daily_session_opened'   => 'Day Opened',
            'daily_session_closed'   => 'Day Closed',
            'return'                 => 'Return Submitted',
            'return_approved'        => 'Return Approved',
            'box_damaged'            => 'Box Damaged',
            'box_adjustment'         => 'Stock Adjusted',
            'credit_writeoff'        => 'Credit Written Off',
            'held_sale_approved'     => 'Price Override Approved',
            'held_sale_rejected'     => 'Price Override Rejected',
            'price_override_approved' => 'Price Override Approved',
            default                  => ucwords(str_replace('_', ' ', $this->action)),
        };
    }

    public function iconKey(): string
    {
        return match($this->entity_type) {
            'Transfer'     => 'transfer',
            'Sale'         => 'sale',
            'HeldSale'     => 'tag',
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
            'transfer_rejected', 'sale_voided', 'transfer_discrepancy', 'box_damaged', 'held_sale_rejected', 'transfer_cancelled' => 'red',
            'transfer_approved', 'transfer_received', 'daily_session_closed', 'held_sale_approved', 'price_override_approved', 'transfer_closed' => 'green',
            'transfer_dispatched', 'transfer_ready', 'transfer_arrived' => 'violet',
            'transfer_requested', 'return', 'price_modified'                           => 'amber',
            default                                                                     => 'accent',
        };
    }

    public function actionUrl(User $viewer): ?string
    {
        if ($this->entity_type === 'Transfer' && $this->entity_id) {
            try {
                if ($viewer->isOwner() || $viewer->isAdmin()) {
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

        if ($this->entity_type === 'HeldSale' && $this->entity_id) {
            try {
                if ($viewer->isShopManager()) {
                    return route('shop.pos');
                }
                return route('owner.reports.sales') . '?activeTab=audit';
            } catch (\Exception) {}
        }

        if ($this->entity_type === 'Sale' && $this->entity_id) {
            try {
                if ($viewer->isOwner() || $viewer->isAdmin()) {
                    return route('owner.sales.show', $this->entity_id);
                }
                return route('shop.sales.index');
            } catch (\Exception) {}
        }

        if (in_array($this->entity_type, ['DailySession', 'daily_session'])) {
            try {
                if ($viewer->isOwner() || $viewer->isAdmin()) return route('owner.finance.daily');
                return route('shop.session.history');
            } catch (\Exception) {}
        }

        return null;
    }

    public function subtitle(): string
    {
        $who = $this->user_name ?? 'Unknown';

        if ($this->entity_type === 'Transfer' && $this->entity_identifier) {
            // On its way: when to expect it is what the shop needs.
            if ($this->action === 'transfer_dispatched' && ! empty($this->details['expected_arrival_at'])) {
                return "{$this->entity_identifier} · expected " . local_time($this->details['expected_arrival_at'])->format('D H:i');
            }

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
