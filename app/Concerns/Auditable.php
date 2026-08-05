<?php

namespace App\Concerns;

use App\Services\AuditLogger;
use Illuminate\Support\Str;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLogger::log([
                'action'     => 'created',
                'module'     => $model->auditModule(),
                'entity'     => $model,
                'new_values' => $model->getAttributes(),
            ]);
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

            AuditLogger::log([
                'action'     => 'updated',
                'module'     => $model->auditModule(),
                'entity'     => $model,
                'old_values' => $old,
                'new_values' => $changes,
            ]);
        });

        static::deleted(function ($model) {
            AuditLogger::log([
                'action'     => 'deleted',
                'module'     => $model->auditModule(),
                'entity'     => $model,
                'old_values' => $model->getAttributes(),
            ]);
        });
    }

    /**
     * Default module name derived from the model (e.g. Product -> 'product',
     * ExpenseRequest -> 'expense_request'). Override for a different label.
     */
    protected function auditModule(): string
    {
        return Str::snake(class_basename($this));
    }

    /**
     * Public so AuditLogger (an external service class) can read it. Default
     * probes common label fields; override per-model for a better fit.
     */
    public function activityLogIdentifier(): ?string
    {
        foreach (['name', 'title', 'label', 'key', 'reference', 'code'] as $field) {
            if (!empty($this->attributes[$field])) {
                return (string) $this->attributes[$field];
            }
        }

        return (string) $this->getKey();
    }
}
