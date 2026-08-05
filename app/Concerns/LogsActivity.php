<?php

namespace App\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->writeActivityLog('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $old = [];
            foreach (array_keys($changes) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            $model->writeActivityLog('updated', $old, $changes);
        });

        static::deleted(function ($model) {
            $model->writeActivityLog('deleted', $model->getAttributes(), null);
        });
    }

    protected function writeActivityLog(string $action, ?array $oldValues, ?array $newValues): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'user_id'           => $user?->id,
            'user_name'         => $user?->name ?? 'System',
            'action'            => $action,
            'entity_type'       => class_basename($this),
            'entity_id'         => $this->getKey(),
            'entity_identifier' => $this->activityLogIdentifier(),
            'old_values'        => $oldValues,
            'new_values'        => $newValues,
            'ip_address'        => request()->ip(),
            'user_agent'        => request()->userAgent(),
        ]);
    }

    protected function activityLogIdentifier(): ?string
    {
        foreach (['name', 'title', 'label', 'key', 'reference', 'code'] as $field) {
            if (!empty($this->attributes[$field])) {
                return (string) $this->attributes[$field];
            }
        }

        return (string) $this->getKey();
    }
}
