<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminJobController extends Controller
{
    /**
     * GET /api/admin/jobs
     * Returns jobs with status 'submitted_for_review' (pending admin review).
     * Access: protected by role:admin middleware in api.php
     */
    public function pendingJobs(Request $request): JsonResponse
    {
        $jobs = Job::with('employer')
            ->where('status', 'submitted_for_review')
            ->orderByDesc('posted_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $jobs->map->toApiArray()->values(),
        ]);
    }

    /**
     * PUT /api/admin/jobs/{id}/review
     * Approve or reject a job that is submitted for review.
     * Access: protected by role:admin middleware in api.php
     */
    public function reviewJob(Request $request, string $id): JsonResponse
    {
        $job = Job::find($id);
        if (! $job) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Job not found.'], 404);
        }

        $validated = $request->validate([
            'status'           => 'required|in:published,rejected',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === 'rejected' && empty($validated['rejection_reason'])) {
            return response()->json(['success' => false, 'message' => 'Rejection reason is required when rejecting a job.'], 422);
        }

        $job->update([
            'status'           => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'risk_level'       => $validated['status'] === 'published' ? 'low' : $job->risk_level,
        ]);

        \App\Services\AuditLogService::log(
            action: 'review_job',
            model: $job,
            description: "Admin updated job status to {$validated['status']}" . 
                         ($validated['rejection_reason'] ? " with reason: {$validated['rejection_reason']}" : "")
        );

        return response()->json([
            'success' => true,
            'message' => "Job status updated to {$validated['status']}.",
            'data'    => $job->fresh()->toApiArray(),
        ]);
    }
}
