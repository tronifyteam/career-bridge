<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Job;
use App\Models\Report;
use App\Models\User;
use App\Models\ViolationHistory;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    // Trust score deductions per severity
    private const DEDUCTIONS = [
        'low'      => 5,
        'medium'   => 10,
        'high'     => 20,
        'critical' => 35,
    ];

    public function index(Request $request)
    {
        $query = Report::with(['reporter', 'reported', 'job']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $reports = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    public function show(Report $report)
    {
        $report->load(['reporter', 'reported', 'job', 'chatMessage']);
        return view('admin.reports.show', compact('report'));
    }

    /**
     * Admin validates a report: update status, add admin note,
     * optionally deduct trust score and create violation history.
     */
    public function updateStatus(Request $request, Report $report)
    {
        $request->validate([
            'status'           => 'required|in:pending,in_review,resolved,rejected',
            'admin_note'       => 'nullable|string|max:2000',
            'apply_violation'  => 'nullable|boolean',
        ]);

        $oldStatus = $report->status;
        $newStatus = $request->status;

        $updateData = [
            'status'     => $newStatus,
            'admin_note' => $request->admin_note,
        ];

        if ($newStatus === 'resolved' && $oldStatus !== 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $report->update($updateData);

        // ── Apply violation to reported user (if resolved & valid) ─────────
        $violationApplied = false;
        if ($newStatus === 'resolved' && $request->boolean('apply_violation', true)) {
            $targetUserId = $this->resolveTargetUserId($report);
            if ($targetUserId) {
                $this->applyViolation($targetUserId, $report);
                $violationApplied = true;
            }
        }

        // ── Notify the reporter of the outcome ─────────────────────────────
        $this->notifyReporter($report, $newStatus);

        $message = "Report status updated to {$newStatus}.";
        if ($violationApplied) {
            $message .= ' Violation applied and trust score updated.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function suspendUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $reason = $request->reason ?? 'Suspended due to report violation.';

        $user->update([
            'is_suspended'          => true,
            'suspension_reason'     => $reason,
            'suspended_at'          => now(),
            'verification_status'   => 'rejected',
            'verification_note'     => $reason,
            'ready_to_work_status'  => 'rejected',
            'verified_badge_status' => 'rejected',
        ]);

        // Revoke all active tokens → immediately kicks user out of the app
        $user->tokens()->delete();

        // Push in-app notification
        AppNotification::create([
            'user_id'           => $user->id,
            'type'              => 'account_suspended',
            'title'             => '🚫 Account Suspended',
            'body'              => "Your account has been suspended. Reason: {$reason}",
            'data'              => ['action' => 'account_suspended'],
        ]);

        return redirect()->back()->with('success', 'User suspended and all active sessions revoked.');
    }

    public function suspendJob(Request $request, Job $job)
    {
        $job->update([
            'status'           => 'suspended',
            'rejection_reason' => 'Suspended by admin due to user reports.',
        ]);

        // Auto-reject pending applications since the job is now suspended
        $applications = \App\Models\JobApplication::where('job_id', $job->id)
            ->whereIn('status', ['pending', 'viewed', 'shortlisted'])
            ->get();

        foreach ($applications as $app) {
            $app->update([
                'status'         => 'rejected',
                'employer_notes' => 'Lowongan pekerjaan ini telah ditangguhkan oleh sistem.',
            ]);

            \App\Models\ApplicationStatusLog::create([
                'application_id' => $app->id,
                'status'         => 'rejected',
                'notes'          => 'Lowongan pekerjaan ini telah ditangguhkan oleh sistem.',
                'changed_by'     => 'system',
                'changed_at'     => now(),
            ]);

            // Notify worker
            if ($app->user_id) {
                AppNotification::create([
                    'user_id' => $app->user_id,
                    'type'    => 'application_status',
                    'title'   => 'Maaf, Lowongan Ditangguhkan',
                    'body'    => "Lamaran Anda untuk posisi \"{$job->title}\" dibatalkan karena lowongan telah ditangguhkan oleh sistem.",
                    'data'    => ['job_id' => $job->id, 'application_id' => $app->id],
                ]);
            }
        }

        // Notify employer
        if ($job->employer_id) {
            AppNotification::create([
                'user_id'           => $job->employer_id,
                'type'              => 'job_suspended',
                'title'             => 'Job Post Suspended',
                'body'              => "Your job post \"{$job->title}\" has been suspended after review.",
                'data'              => ['job_id' => $job->id],
            ]);
        }

        return redirect()->back()->with('success', 'Job suspended successfully, and pending applications have been rejected.');
    }

    // ── Private Helpers ─────────────────────────────────────────────────────

    private function resolveTargetUserId(Report $report): ?int
    {
        if ($report->reported_id) return $report->reported_id;
        if ($report->job_id && $report->job) return $report->job->employer_id;
        return null;
    }

    private function applyViolation(int $userId, Report $report): void
    {
        $severity   = $report->severity ?? 'medium';
        $deduction  = self::DEDUCTIONS[$severity] ?? 10;

        // Create violation record
        ViolationHistory::create([
            'user_id'         => $userId,
            'report_id'       => $report->id,
            'violation_type'  => $report->report_type . '_violation',
            'description'     => "Validated by admin: {$report->reason}",
            'points_deducted' => $deduction,
        ]);

        // Decrement trust score (floor at 0) + increment violation_count
        $user = User::find($userId);
        if ($user) {
            $newScore = max(0, ($user->trust_score ?? 100) - $deduction);
            $user->update([
                'trust_score'     => $newScore,
                'violation_count' => ($user->violation_count ?? 0) + 1,
            ]);

            // Auto-suspend user if trust_score hits 0
            if ($newScore <= 0) {
                $suspendReason = 'Auto-suspended: trust score reached 0 due to repeated violations.';
                $user->update([
                    'is_suspended'          => true,
                    'suspension_reason'     => $suspendReason,
                    'suspended_at'          => now(),
                    'verification_status'   => 'rejected',
                    'verification_note'     => $suspendReason,
                ]);

                // Immediately revoke all tokens
                $user->tokens()->delete();

                // Notify user
                AppNotification::create([
                    'user_id'           => $user->id,
                    'type'              => 'account_suspended',
                    'title'             => '🚫 Account Suspended',
                    'body'              => $suspendReason,
                    'data'              => ['action' => 'account_suspended'],
                ]);
            }
        }
    }

    private function notifyReporter(Report $report, string $newStatus): void
    {
        $messages = [
            'in_review' => [
                'title' => '🔍 Report Under Review',
                'body'  => 'Your report is being investigated by our team.',
            ],
            'resolved' => [
                'title' => '✅ Report Resolved',
                'body'  => 'Your report has been reviewed and action has been taken. ' .
                           ($report->admin_note ? 'Note: ' . $report->admin_note : ''),
            ],
            'rejected' => [
                'title' => 'ℹ️ Report Closed',
                'body'  => 'Your report was reviewed but did not meet our action criteria. ' .
                           ($report->admin_note ? 'Note: ' . $report->admin_note : ''),
            ],
        ];

        if (!isset($messages[$newStatus])) return;

        AppNotification::create([
            'user_id'           => $report->reporter_id,
            'type'              => 'report_update',
            'title'             => $messages[$newStatus]['title'],
            'body'              => $messages[$newStatus]['body'],
            'data'              => [
                'report_id' => $report->id,
                'status'    => $newStatus,
            ],
        ]);
    }
}
