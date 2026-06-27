<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AdminReportController — Manage incoming reports.
 *
 * UAT #79: Admin can view, invalidate, and resolve reports.
 */
class AdminReportController extends Controller
{
    /**
     * GET /api/admin/reports
     * List all reports with optional filtering.
     *
     * Query params:
     *   - status    : string (pending|reviewing|resolved|invalid)
     *   - type      : string (job|chat_message|employer)
     *   - per_page  : int (default 30, max 100)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::query()
            ->with([
                'reporter:id,full_name,email',
                'reportedUser:id,full_name,email,role',
            ])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('reportable_type', $request->type);
        }

        $perPage = min((int) $request->input('per_page', 30), 100);
        $reports = $query->paginate($perPage);

        return response()->json([
            'success'      => true,
            'data'         => $reports->items(),
            'current_page' => $reports->currentPage(),
            'last_page'    => $reports->lastPage(),
            'total'        => $reports->total(),
        ]);
    }

    /**
     * POST /api/admin/reports/{id}/invalidate
     *
     * UAT #79: Mark a report as invalid (false report / no violation found).
     * Body: { reason?: string }
     */
    public function invalidate(Request $request, string $id): JsonResponse
    {
        $report = Report::findOrFail($id);

        if ($report->status === 'resolved' || $report->status === 'invalid') {
            return response()->json([
                'success' => false,
                'error'   => 'already_closed',
                'message' => 'This report has already been closed.',
            ], 422);
        }

        $report->update([
            'status'       => 'invalid',
            'admin_note'   => $request->input('reason', 'No violation found after review.'),
            'resolved_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report marked as invalid.',
            'data'    => $report->fresh(),
        ]);
    }

    /**
     * POST /api/admin/reports/{id}/resolve
     *
     * Mark a report as resolved (action taken against the reported entity).
     * Body: { action_taken?: string }
     */
    public function resolve(Request $request, string $id): JsonResponse
    {
        $report = Report::findOrFail($id);

        if ($report->status === 'resolved' || $report->status === 'invalid') {
            return response()->json([
                'success' => false,
                'error'   => 'already_closed',
                'message' => 'This report has already been closed.',
            ], 422);
        }

        $report->update([
            'status'       => 'resolved',
            'admin_note'   => $request->input('action_taken', 'Action has been taken.'),
            'resolved_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report resolved.',
            'data'    => $report->fresh(),
        ]);
    }
}
