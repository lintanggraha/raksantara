<?php

namespace App\Support;

/**
 * Records create/update/delete of a model in the audit trail, with only the
 * fields that changed (old value in before_state, new value in after_state).
 *
 * A model sets `protected string $auditEvent = 'incident';` to name its events
 * (incident.created, incident.updated, incident.deleted) and may list
 * `protected array $auditIgnore = [...]` for bookkeeping columns whose changes
 * alone are not worth an entry. Mass query-builder updates/deletes bypass
 * Eloquent events; callers doing bulk work log one summary event instead.
 */
trait Auditable
{
    private static bool $auditingDisabled = false;

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->recordAudit('created', null, $model->auditablePayload($model->getAttributes()));
        });

        static::updated(function ($model) {
            $changes = array_diff_key($model->getChanges(), array_flip($model->auditIgnoredColumns()));
            if ($changes === []) {
                return;
            }
            // getOriginal() omits columns that were never set; record them as null.
            $before = [];
            foreach (array_keys($changes) as $key) {
                $before[$key] = $model->getRawOriginal($key);
            }
            $model->recordAudit('updated', $model->auditablePayload($before) + $model->auditIdentity(), $model->auditablePayload($changes) + $model->auditIdentity());
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted', $model->auditablePayload($model->getAttributes()), null);
        });
    }

    /**
     * Run bulk work (imports, syncs) without one entry per row.
     */
    public static function withoutAuditing(callable $callback)
    {
        $previous = self::$auditingDisabled;
        self::$auditingDisabled = true;
        try {
            return $callback();
        } finally {
            self::$auditingDisabled = $previous;
        }
    }

    protected function recordAudit(string $action, ?array $before, ?array $after): void
    {
        if (self::$auditingDisabled) {
            return;
        }

        AuditLogger::logModelEvent($this->auditEventPrefix() . '.' . $action, $this, $before, $after);
    }

    protected function auditEventPrefix(): string
    {
        return property_exists($this, 'auditEvent') && $this->auditEvent
            ? $this->auditEvent
            : \Illuminate\Support\Str::snake(class_basename($this));
    }

    protected function auditIgnoredColumns(): array
    {
        $ignore = property_exists($this, 'auditIgnore') ? (array) $this->auditIgnore : [];

        return array_merge(['created_at', 'updated_at'], $ignore);
    }

    /**
     * Fields that identify the record in the audit trail even when they did
     * not change (e.g. the incident code on an "updated" entry).
     */
    protected function auditIdentity(): array
    {
        $identity = [];
        foreach (['incident_code', 'risk_id', 'code', 'name', 'title', 'metric_name', 'level', 'label'] as $key) {
            if (array_key_exists($key, $this->getAttributes()) && !array_key_exists($key, $this->getChanges())) {
                $identity[$key] = $this->getAttribute($key);
            }
        }

        return $identity;
    }

    protected function auditablePayload(array $attributes): array
    {
        $hidden = array_flip($this->getHidden());

        return array_diff_key($attributes, $hidden, array_flip(['created_at', 'updated_at']));
    }
}
