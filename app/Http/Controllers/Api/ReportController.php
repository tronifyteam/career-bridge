<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Report;
use App\Models\ViolationHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Services\NotificationService;

class ReportController extends Controller
{
    // ── Thresholds ──────────────────────────────────────────────────────────
    private const REVIEW_THRESHOLD  = 3;  // reports → job moves to in_review
    private const SUSPEND_THRESHOLD = 5;  // reports → job auto-suspended

    /**
     * POST /api/reports
     * Submit a new report (Job, Employer, User, Chat).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'report_type'     => 'required|in:user,employer,job,chat',
            'reported_id'     => 'nullable|exists:users,id',
            'job_id'          => 'nullable|exists:job_listings,id',
            'chat_message_id' => 'nullable|exists:chat_messages,id',
            'reason'          => 'required|string|max:255',
            'severity'        => 'nullable|in:low,medium,high,critical',
            'description'     => 'nullable|string|max:2000',
            'evidence'        => 'nullable|image|max:10240',
        ]);

        $user = $request->user();

        // ── Type-specific validation ──────────────────────────────────────
        if (in_array($request->report_type, ['user', 'employer']) && !$request->reported_id) {
            return response()->json(['success' => false, 'message' => 'reported_id is required for user/employer report.'], 422);
        }
        if ($request->report_type === 'job' && !$request->job_id) {
            return response()->json(['success' => false, 'message' => 'job_id is required for job report.'], 422);
        }
        if ($request->report_type === 'chat' && !$request->chat_message_id) {
            return response()->json(['success' => false, 'message' => 'chat_message_id is required for chat report.'], 422);
        }

        $evidenceUrl = null;
        if ($request->hasFile('evidence')) {
            $path = $request->file('evidence')->store('reports', 'public');
            $evidenceUrl = url('/storage/' . $path);
        }

        $severity = $request->severity ?? 'medium';

        $report = Report::create([
            'reporter_id'     => $user->id,
            'reported_id'     => $request->reported_id,
            'job_id'          => $request->job_id,
            'chat_message_id' => $request->chat_message_id,
            'report_type'     => $request->report_type,
            'reason'          => $request->reason,
            'severity'        => $severity,
            'description'     => $request->description,
            'evidence_url'    => $evidenceUrl,
            'status'          => 'pending',
        ]);

        // ── Auto-action for job reports ────────────────────────────────────
        if ($request->report_type === 'job' && $request->job_id) {
            $this->handleJobReportAutoAction($request->job_id, $severity);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully. Our team will review it shortly.',
            'data'    => $report->toApiArray(),
        ], 201);
    }

    /**
     * GET /api/my-reports
     * List all reports submitted by the current user with full status + resolution.
     */
    public function myReports(Request $request): JsonResponse
    {
        $reports = Report::where('reporter_id', $request->user()->id)
            ->with(['job'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $reports->map(fn($r) => $this->reportWithDetails($r))->values(),
            'meta'    => [
                'current_page' => $reports->currentPage(),
                'last_page'    => $reports->lastPage(),
                'total'        => $reports->total(),
            ],
        ]);
    }

    /**
     * GET /api/reports/violations-against-me
     *
     * UAT #80: Worker can see their own violation history —
     * reports filed against them by others (employers/workers).
     * Only shows resolved/actioned reports (not pending ones to avoid gaming).
     */
    public function violationHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $reports = Report::where('reported_id', $user->id)
            ->whereIn('status', ['resolved', 'invalid', 'reviewing'])
            ->with(['job'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $reports->map(fn($r) => [
                'id'          => (string) $r->id,
                'reason'      => $r->reason,
                'description' => $r->description,
                'report_type' => $r->report_type,
                'severity'    => $r->severity,
                'status'      => $r->status,
                'admin_note'  => $r->status === 'invalid' ? $r->admin_note : null, // only show note if invalid
                'reported_at' => $r->created_at->toIso8601String(),
                'resolved_at' => $r->resolved_at?->toIso8601String(),
                // For job-related violations
                'job' => $r->job_id ? [
                    'id'    => (string) $r->job_id,
                    'title' => $r->job->title ?? 'Unknown Job',
                ] : null,
            ])->values(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page'    => $reports->lastPage(),
                'total'        => $reports->total(),
            ],
        ]);
    }

    // ── Private Helpers ────────────────────────────────────────────────────

    private function handleJobReportAutoAction(int $jobId, string $severity): void
    {
        $job = Job::find($jobId);
        if (!$job || $job->status === 'suspended') return;

        // Critical severity → immediately move to in_review
        if ($severity === 'critical' && $job->status === 'published') {
            $job->update(['status' => 'in_review']);
            return;
        }

        $reportCount = Report::where('job_id', $jobId)->count();

        if ($reportCount >= self::SUSPEND_THRESHOLD) {
            $job->update([
                'status'           => 'suspended',
                'rejection_reason' => "Auto-suspended: received {$reportCount} reports from users.",
            ]);
        } elseif ($reportCount >= self::REVIEW_THRESHOLD && $job->status === 'published') {
            $job->update(['status' => 'in_review']);
        }
    }

    private function reportWithDetails(Report $report): array
    {
        $arr = $report->toApiArray();

        // Add resolution info
        $arr['admin_note']   = $report->admin_note;
        $arr['resolved_at']  = $report->resolved_at?->toIso8601String();
        $arr['severity']     = $report->severity;

        // Add target summary
        if ($report->job_id && $report->job) {
            $arr['target'] = [
                'type'  => 'job',
                'id'    => (string) $report->job_id,
                'title' => $report->job->title ?? 'Unknown Job',
            ];
        } elseif ($report->reported_id) {
            $arr['target'] = [
                'type'  => $report->report_type, // 'user' or 'employer'
                'id'    => (string) $report->reported_id,
                'title' => optional($report->reported)->full_name ?? 'Unknown User',
            ];
        } else {
            $arr['target'] = null;
        }

        return $arr;
    }
}
