<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * AdminAuditController — Exposes audit log browsing and CSV export for Admins.
 *
 * UAT #45: Admin can view login/action audit logs.
 * UAT #47: Admin can export audit logs to CSV.
 */
class AdminAuditController extends Controller
{
    /**
     * GET /api/admin/audit-logs
     *
     * Query params:
     *   - action     : string  — filter by action type (e.g. 'login', 'apply', 'suspend')
     *   - admin_id   : int     — filter by acting admin user
     *   - start_date : date    — from YYYY-MM-DD (inclusive)
     *   - end_date   : date    — to YYYY-MM-DD (inclusive)
     *   - per_page   : int     — default 50, max 200
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->with(['admin:id,full_name,email,role'])
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $logs    = $query->paginate($perPage);

        return response()->json([
            'success'      => true,
            'data'         => $logs->items(),
            'current_page' => $logs->currentPage(),
            'last_page'    => $logs->lastPage(),
            'total'        => $logs->total(),
            'per_page'     => $logs->perPage(),
        ]);
    }

    /**
     * GET /api/admin/audit-logs/export
     *
     * Returns a CSV file download containing audit logs for the given filters.
     * UAT #47: Admin can export audit log to CSV / Excel.
     */
    public function export(Request $request): Response
    {
        $query = AuditLog::query()
            ->with(['admin:id,full_name,email,role'])
            ->oldest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . now()->format('Y-m-d') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // CSV header row
            fputcsv($handle, [
                'ID', 'Admin ID', 'Admin Name', 'Admin Email',
                'Action', 'Model Type', 'Model ID', 'Description',
                'Old Values', 'New Values', 'IP Address', 'User Agent', 'Created At',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->admin_id,
                    $log->admin?->full_name ?? '',
                    $log->admin?->email ?? '',
                    $log->action,
                    $log->model_type ?? '',
                    $log->model_id ?? '',
                    $log->description ?? '',
                    is_array($log->old_values) ? json_encode($log->old_values) : '',
                    is_array($log->new_values) ? json_encode($log->new_values) : '',
                    $log->ip_address ?? '',
                    $log->user_agent ?? '',
                    $log->created_at?->toIso8601String() ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
