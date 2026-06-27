<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerDirectoryController extends Controller
{
    /**
     * GET /api/workers
     * Browse the worker directory.
     * Only accessible by verified employers (ready_to_work_status = 'ready').
     *
     * Query params (all optional):
     *   - ready_to_work      : boolean (1/0)
     *   - verified_badge     : boolean (1/0)
     *   - no_sponsorship     : boolean (1/0) — filter workers that do NOT need sponsorship
     *   - sponsorship        : boolean (1/0) — filter workers that DO need sponsorship
     *   - self_check_required: boolean (1/0)
     *   - city               : string
     *   - worker_type        : string (slug)
     *   - available_date     : date (YYYY-MM-DD) — worker available on/before this date
     *   - language           : string (language code e.g. EN, ID)
     *   - per_page           : int (default 20, max 50)
     */
    public function index(Request $request): JsonResponse
    {
        $employer = $request->user();

        // Only verified employers can browse workers
        if (! $employer->isVerifiedEmployer()) {
            return response()->json([
                'success' => false,
                'error'   => 'employer_not_verified',
                'message' => 'Only verified employers can browse the worker directory.',
            ], 403);
        }

        $perPage = min((int) $request->input('per_page', 20), 50);

        $query = User::workers()
            ->with(['workerLanguages.language', 'workerJobTypes.jobType'])
            ->select([
                'id', 'full_name', 'nationality', 'current_city', 'avatar_url',
                'worker_type', 'worker_type_id',
                'verified_badge_status', 'ready_to_work_status',
                'sponsorship_required', 'employer_self_check_required',
                'available_date', 'expected_salary', 'is_cv_public', 'cv_url',
            ]);

        // ── Filters ────────────────────────────────────────

        if ($request->boolean('ready_to_work')) {
            $query->readyToWork();
        }

        if ($request->boolean('verified_badge')) {
            $query->verifiedBadge();
        }

        if ($request->boolean('no_sponsorship')) {
            $query->where('sponsorship_required', false);
        }

        if ($request->boolean('sponsorship')) {
            $query->where('sponsorship_required', true);
        }

        if ($request->boolean('self_check_required')) {
            $query->where('employer_self_check_required', true);
        }

        if ($request->filled('city')) {
            $query->where('current_city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('worker_type')) {
            $query->where('worker_type', $request->worker_type);
        }

        if ($request->filled('available_date')) {
            $query->whereDate('available_date', '<=', $request->available_date);
        }

        if ($request->filled('language')) {
            $langCode = strtoupper($request->language);
            $query->whereHas('workerLanguages.language', fn($q) =>
                $q->where('language_code', $langCode)
            );
        }

        // 🆕 PDF Section 5 & 6: nationality filter
        if ($request->filled('nationality')) {
            $query->where('nationality', 'like', '%' . $request->nationality . '%');
        }

        // 🆕 Salary expectation range filter
        if ($request->filled('salary_max')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('expected_salary')
                  ->orWhere('expected_salary', '<=', (float) $request->salary_max);
            });
        }

        $workers = $query->orderByRaw("CASE WHEN ready_to_work_status = 'ready' THEN 1 ELSE 0 END DESC")
                         ->orderByDesc('ready_to_work_updated_at')
                         ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'workers'      => collect($workers->items())->map->toPublicProfileArray()->values(),
                'current_page' => $workers->currentPage(),
                'last_page'    => $workers->lastPage(),
                'total'        => $workers->total(),
                'per_page'     => $workers->perPage(),
            ],
        ]);
    }

    /**
     * GET /api/workers/{id}
     * View a specific worker's public profile.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $employer = $request->user();

        if (! $employer->isVerifiedEmployer()) {
            return response()->json([
                'success' => false,
                'error'   => 'employer_not_verified',
                'message' => 'Only verified employers can view worker profiles.',
            ], 403);
        }

        $worker = User::workers()
            ->with(['workerLanguages.language', 'workerJobTypes.jobType'])
            ->find($id);

        if (! $worker) {
            return response()->json([
                'success' => false,
                'error'   => 'not_found',
                'message' => 'Worker not found.',
            ], 404);
        }

        // ── UAT #23: CV Privacy Guard ────────────────────────────────────────
        // Employer can only see cv_url if worker has applied to at least one
        // of their jobs (active or past applications).
        $hasApplied = \App\Models\JobApplication::where('user_id', $worker->id)
            ->whereHas('job', fn($q) => $q->where('employer_id', $employer->id))
            ->exists();

        $profile = $worker->toPublicProfileArray();
        if (! $hasApplied) {
            // Remove CV URL for workers who haven't applied to this employer
            unset($profile['cv_url']);
            $profile['cv_access'] = 'restricted'; // hint for frontend
        } else {
            $profile['cv_access'] = 'granted';
        }

        return response()->json([
            'success' => true,
            'data'    => $profile,
        ]);
    }
}
