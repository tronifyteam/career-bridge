<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\JobType;
use App\Models\WorkerLanguage;
use App\Models\WorkerJobType;
use App\Services\WorkerStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WorkerStatusController extends Controller
{
    public function __construct(private WorkerStatusService $workerStatus) {}

    /**
     * GET /api/worker/status
     * Returns current verified_badge_status, ready_to_work_status, and document summary.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isWorker()) {
            return response()->json([
                'success' => false,
                'error'   => 'not_worker',
                'message' => 'This endpoint is for workers only.',
            ], 403);
        }

        $checklist = $user->worker_type
            ? $this->workerStatus->getChecklistStatus($user)
            : ['total_required' => 0, 'requirements' => []];

        // Determine which phase the worker is currently in
        // Phase 1: awaiting Verified Badge (personal identity docs)
        // Phase 2: awaiting Ready to Work Badge (work-status docs)
        $verifiedBadge  = $user->verified_badge_status ?? 'unverified';
        $readyStatus    = $user->ready_to_work_status ?? 'not_ready';

        // Auto-ready types: once verified badge granted, ready_to_work is automatic
        $autoReadyTypes = ['arc_other', 'aprc', 'taiwanese'];
        $isAutoReady    = in_array($user->worker_type, $autoReadyTypes);
        // Not-eligible type: not_sure can never auto-get ready to work
        $isNotEligible  = $user->worker_type === 'not_sure';

        return response()->json([
            'success' => true,
            'data' => [
                'verified_badge_status'        => $verifiedBadge,
                'verified_badge_updated_at'    => $user->verified_badge_updated_at?->toIso8601String(),
                'ready_to_work_status'         => $readyStatus,
                'ready_to_work_updated_at'     => $user->ready_to_work_updated_at?->toIso8601String(),
                'employer_self_check_required' => (bool) $user->employer_self_check_required,
                'selfie_uploaded'              => (bool) $user->selfie_file_url,
                'selfie_verified'              => $user->selfie_verified_at !== null,
                'worker_type'                  => $user->worker_type,
                // Badge phase context for mobile routing
                'auto_ready_to_work'           => $isAutoReady,
                'not_eligible_ready_to_work'   => $isNotEligible,
                'checklist'                    => $checklist,
            ],
        ]);
    }

    /**
     * POST /api/worker/languages
     * Replace worker's language list with the provided one.
     */
    public function setLanguages(Request $request): JsonResponse
    {
        $request->validate([
            'languages'                     => 'required|array|min:1',
            'languages.*.language_id'       => 'required|integer|exists:languages,id',
            'languages.*.proficiency_level' => 'required|in:basic,intermediate,advanced,fluent',
        ]);

        $user = $request->user();

        // Delete existing and re-insert
        WorkerLanguage::where('user_id', $user->id)->delete();

        $inserted = [];
        foreach ($request->languages as $lang) {
            $wl = WorkerLanguage::create([
                'user_id'           => $user->id,
                'language_id'       => $lang['language_id'],
                'proficiency_level' => $lang['proficiency_level'],
            ]);
            $inserted[] = $wl->load('language')->toApiArray();
        }

        return response()->json([
            'success' => true,
            'message' => count($inserted) . ' language(s) saved.',
            'data'    => $inserted,
        ]);
    }

    /**
     * POST /api/worker/job-types
     * Replace worker's job type preferences.
     */
    public function setJobTypes(Request $request): JsonResponse
    {
        $request->validate([
            'job_types'                        => 'required|array|min:1',
            'job_types.*.job_type_id'          => 'required|integer|exists:job_types,id',
            'job_types.*.years_of_experience'  => 'nullable|integer|min:0|max:50',
        ]);

        $user = $request->user();

        WorkerJobType::where('user_id', $user->id)->delete();

        $inserted = [];
        foreach ($request->job_types as $jt) {
            $wjt = WorkerJobType::create([
                'user_id'             => $user->id,
                'job_type_id'         => $jt['job_type_id'],
                'years_of_experience' => $jt['years_of_experience'] ?? 0,
            ]);
            $inserted[] = $wjt->load('jobType')->toApiArray();
        }

        return response()->json([
            'success' => true,
            'message' => count($inserted) . ' job type(s) saved.',
            'data'    => $inserted,
        ]);
    }

    /**
     * GET /api/languages
     * Public: list all available languages.
     */
    public function listLanguages(): JsonResponse
    {
        $data = Cache::remember('meta.languages', 86400, fn() => Language::all()->map->toApiArray()->values());
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/job-types
     * Public: list all job types.
     */
    public function listJobTypes(): JsonResponse
    {
        $data = Cache::remember('meta.job_types', 86400, fn() => JobType::all()->map->toApiArray()->values());
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
