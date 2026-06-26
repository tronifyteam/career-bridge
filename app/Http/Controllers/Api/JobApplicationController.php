<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationStatusLog;
use App\Models\Job;
use App\Models\JobApplication;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    /**
     * POST /api/jobs/{jobId}/apply
     */
    public function apply(Request $request, string $jobId): JsonResponse
    {
        $user = $request->user();

        // PRD Rule: Worker tidak bisa apply jika profil tidak memenuhi standar minimum
        if (! $user->profile_completed) {
            return response()->json([
                'success' => false,
                'error'   => 'incomplete_profile',
                'message' => 'Silakan lengkapi profil minimum Anda terlebih dahulu sebelum melamar pekerjaan.',
            ], 400);
        }

        // PRD Rule: Worker tidak bisa apply jika belum mengunggah CV (PDF)
        if (empty($user->cv_url)) {
            return response()->json([
                'success' => false,
                'error'   => 'missing_cv',
                'message' => 'Silakan unggah CV (PDF) Anda terlebih dahulu sebelum melamar pekerjaan.',
            ], 400);
        }

        $job = Job::active()->find($jobId);
        if (! $job) {
            return response()->json([
                'success' => false,
                'error'   => 'not_found',
                'message' => 'Job not found or no longer active',
            ], 404);
        }

        // PRD Rule: Block apply only if eligibility is explicitly 'Unknown'
        // AND the job was never published by admin (no posted_at).
        // Jobs with status='published' are trusted — admin has reviewed them.
        if ($job->eligibility === 'Unknown' && empty($job->posted_at)) {
            return response()->json([
                'success' => false,
                'error'   => 'ineligible_job',
                'message' => 'This job listing is not open for applications yet.',
            ], 422);
        }

        // Check if already applied
        $existing = JobApplication::where('job_id', $jobId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'error'   => 'already_applied',
                'message' => 'You have already applied to this job',
            ], 409);
        }

        $validated = $request->validate([
            'cover_letter' => 'nullable|string|max:2000',
        ]);

        $application = JobApplication::create([
            'job_id'       => $jobId,
            'user_id'      => $user->id,
            'status'       => 'pending',
            'cover_letter' => $validated['cover_letter'] ?? null,
            'applied_at'   => now(),
        ]);

        // ── Create status snapshot ──────────────────────
        $snapshot = ApplicationStatusHistory::create([
            'application_id'              => $application->id,
            'verified_badge_status'       => $user->verified_badge_status ?? 'unverified',
            'ready_to_work_status'        => $user->ready_to_work_status ?? 'not_ready',
            'sponsorship_required'        => (bool) $user->sponsorship_required,
            'employer_self_check_required'=> (bool) $user->employer_self_check_required,
            'worker_nationality'          => $user->nationality,
            'worker_type_slug'            => $user->worker_type,
            'recorded_at'                 => now(),
        ]);

        $application->update(['status_snapshot_id' => $snapshot->id]);

        // ── Log initial status ───────────────────────────────────────
        ApplicationStatusLog::create([
            'application_id' => $application->id,
            'status'         => 'pending',
            'notes'          => 'Application submitted by worker.',
            'changed_by'     => 'worker',
            'changed_at'     => now(),
        ]);

        $application->load('job.employer');

        // Send push + in-app notification to employer
        if ($application->job->employer) {
            $employer   = $application->job->employer;
            $workerName = $user->full_name ?? $user->name;
            app(NotificationService::class)->notify(
                $employer,
                'job_application',
                'Pelamar Baru!',
                "{$workerName} telah melamar untuk posisi {$application->job->title}.",
                [
                    'job_id'         => (string) $jobId,
                    'application_id' => (string) $application->id,
                ]
            );

            // Send Email Notification via Queue
            if (!empty($employer->email)) {
                \Illuminate\Support\Facades\Mail::to($employer->email)->queue(new \App\Mail\JobApplicationReceivedMail($application, $application->job));
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $application->toApiArray(),
        ], 201);
    }

    /**
     * GET /api/applications
     * Get all applications for the authenticated worker.
     */
    public function myApplications(Request $request): JsonResponse
    {
        $applications = JobApplication::with(['job', 'statusSnapshot', 'statusLogs'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('applied_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $applications->map->toApiArray()->values(),
        ]);
    }

    /**
     * GET /api/jobs/{jobId}/applicants
     * Get all applicants for a job (employer view).
     */
    public function jobApplicants(Request $request, string $jobId): JsonResponse
    {
        $user = $request->user();
        $job  = Job::find($jobId);

        if (! $job) {
            return response()->json([
                'success' => false, 'error' => 'not_found', 'message' => 'Job not found',
            ], 404);
        }

        if ($job->employer_id !== $user->id) {
            return response()->json([
                'success' => false, 'error' => 'forbidden',
                'message' => 'You can only view applicants for your own jobs',
            ], 403);
        }

        $applications = JobApplication::with(['user.workerLanguages.language', 'statusSnapshot'])
            ->where('job_id', $jobId)
            ->orderByDesc('applied_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $applications->map->toApiArray()->values(),
        ]);
    }

    /**
     * GET /api/applications/employer
     * All applications across all employer's jobs.
     */
    public function employerApplications(Request $request): JsonResponse
    {
        $user = $request->user();

        $applications = JobApplication::with(['job', 'user', 'statusSnapshot'])
            ->whereHas('job', fn($q) => $q->where('employer_id', $user->id))
            ->orderByDesc('applied_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $applications->map->toApiArray()->values(),
        ]);
    }

    /**
     * PUT /api/applications/{id}/status
     * Update application status (employer action).
     * Supports: pending, viewed, shortlisted, accepted, rejected, cancelled
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status'         => 'required|in:pending,viewed,shortlisted,accepted,rejected,cancelled',
            'employer_notes' => 'nullable|string|max:1000',
        ]);

        $application = JobApplication::with('job')->find($id);

        if (! $application) {
            return response()->json([
                'success' => false, 'error' => 'not_found', 'message' => 'Application not found',
            ], 404);
        }

        if ($application->job->employer_id !== $request->user()->id) {
            return response()->json([
                'success' => false, 'error' => 'forbidden',
                'message' => 'You can only manage applications for your own jobs',
            ], 403);
        }

        $application->update([
            'status'         => $request->status,
            'employer_notes' => $request->employer_notes,
        ]);

        // ── Log the status change ────────────────────────────────────
        ApplicationStatusLog::create([
            'application_id' => $application->id,
            'status'         => $request->status,
            'notes'          => $request->employer_notes,
            'changed_by'     => 'employer',
            'changed_at'     => now(),
        ]);

        // Send push + in-app notification to worker
        if ($application->user) {
            $worker     = $application->user;
            $jobTitle   = $application->job->title;
            $statusText = match ($request->status) {
                'accepted'    => 'diterima',
                'rejected'    => 'ditolak',
                'shortlisted' => 'masuk shortlist',
                'viewed'      => 'dilihat oleh employer',
                'cancelled'   => 'dibatalkan',
                default       => 'diperbarui',
            };
            app(NotificationService::class)->notify(
                $worker,
                'application_status',
                'Status Lamaran Diperbarui',
                "Lamaran Anda untuk posisi {$jobTitle} telah {$statusText}.",
                [
                    'job_id'         => (string) $application->job_id,
                    'application_id' => (string) $application->id,
                    'status'         => $request->status,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data'    => $application->fresh()->load('job', 'user', 'statusSnapshot', 'statusLogs')->toApiArray(),
        ]);
    }
}
