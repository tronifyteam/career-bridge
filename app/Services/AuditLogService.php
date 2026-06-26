<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * Log an admin action.
     *
     * @param string $action E.g., 'created', 'updated', 'deleted', 'approved'
     * @param mixed|null $model The eloquent model being manipulated (optional)
     * @param string|null $description A human readable description
     * @param array|null $oldValues
     * @param array|null $newValues
     */
    public static function log(string $action, $model = null, ?string $description = null, ?array $oldValues = null, ?array $newValues = null)
    {
        // Get the currently authenticated user
        $admin = null;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && $user->is_admin) {
                $admin = $user;
            }
        }

        if (!$admin) {
            return;
        }

        AuditLog::create([
            'admin_id'    => $admin->id,
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model ? $model->id : null,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'description' => $description,
        ]);
    }
}
