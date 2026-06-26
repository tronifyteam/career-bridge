<?php

namespace App\Traits;

use App\Services\AuditLogService;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            AuditLogService::log('created', $model, null, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            // Only log if attributes actually changed
            if ($model->wasChanged()) {
                AuditLogService::log('updated', $model, null, $model->getOriginal(), $model->getChanges());
            }
        });

        static::deleted(function ($model) {
            AuditLogService::log('deleted', $model, null, $model->getAttributes(), null);
        });
    }
}
