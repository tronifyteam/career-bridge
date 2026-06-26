<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkerType;
use App\Services\WorkerStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserOnboardingController extends Controller
{
    public function __construct(private WorkerStatusService $workerStatus) {}

    /**
     * GET /api/onboarding/status
     * Returns the current onboarding progress and what step the user is on.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $steps = [
            1 => ['label' => 'Registered',          'done' => true],
            2 => ['label' => 'Contact Verified',     'done' => $user->email_verified_at || $user->phone_verified_at],
            3 => ['label' => 'Selfie Uploaded',      'done' => (bool) $user->selfie_file_url],
            4 => ['label' => 'Role Selected',        'done' => (bool) $user->role],
            5 => ['label' => 'Basic Info Filled',    'done' => (bool) $user->profile_completed],
            6 => ['label' => 'Documents Uploaded',   'done' => $user->workerDocuments()->exists()],
        ];

        $currentStep = $user->onboarding_step ?? 1;

        return response()->json([
            'success' => true,
            'data' => [
                'current_step' => $currentStep,
                'steps' => $steps,
                'user' => $user->toApiArray(),
            ],
        ]);
    }

    /**
     * POST /api/onboarding/selfie
     * Step 3: Upload selfie photo (required for ALL users before role selection).
     */
    public function uploadSelfie(Request $request): JsonResponse
    {
        $request->validate([
            'selfie' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            'is_face_match' => 'nullable|boolean',
            'face_match_score' => 'nullable|numeric',
        ]);

        $user = $request->user();

        $file = $request->file('selfie');
        $filename = 'selfie_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('selfies', $filename, 'public');
        $url = asset('storage/'.$path);

        $isMatch = filter_var($request->is_face_match, FILTER_VALIDATE_BOOLEAN);
        $selfieVerifiedAt = $isMatch ? now() : null;

        $user->update([
            'selfie_file_url' => $url,
            'selfie_verified_at' => $selfieVerifiedAt,
            'onboarding_step' => max($user->onboarding_step ?? 1, 3),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Selfie uploaded successfully. Awaiting admin verification.',
            'data' => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * POST /api/onboarding/role
     * Step 4: Select worker or employer role.
     *
     * @deprecated Role is now set automatically by /onboarding/worker-type (worker)
     *             and /onboarding/employer-category (employer).
     *             This endpoint is kept for backward compatibility but no longer required.
     */
    public function selectRole(Request $request): JsonResponse
    {
        // Mobile wizard sends: 'WORKER' or 'EMPLOYER'
        // Legacy flow sends:   'ROLE_WORKER', 'ROLE_COMPANY', etc.
        // Accept both formats.
        $request->validate([
            'role_id' => 'required|string',
        ]);

        $roleIdRaw = strtoupper(trim($request->role_id));

        // Normalize short form → full role_id
        $roleMap = [
            'WORKER' => 'ROLE_WORKER',
            'EMPLOYER' => 'ROLE_COMPANY',  // default employer type
        ];
        $roleId = $roleMap[$roleIdRaw] ?? $roleIdRaw;

        $validRoles = ['ROLE_WORKER', 'ROLE_COMPANY', 'ROLE_FACTORY', 'ROLE_FAMILY_CARE', 'ROLE_AGENCY', 'ROLE_AGENCY_STAFF'];
        if (! in_array($roleId, $validRoles)) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_role',
                'message' => 'Invalid role. Accepted: WORKER, EMPLOYER, or full ROLE_* values.',
            ], 422);
        }

        $user = $request->user();
        $role = User::roleFromRoleId($roleId);

        $user->update([
            'role' => $role,
            'onboarding_step' => max($user->onboarding_step ?? 1, 4),
        ]);

        return response()->json([
            'success' => true,
            'data' => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * aa
     * aa
     * POST /api/onboarding/worker-type
     * Step 5W: Select worker type (Student ARC, Blue Collar, etc.)
     * Also generates the document requirement checklist.
     */
    public function selectWorkerType(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isWorker()) {
            return response()->json([
                'success' => false,
                'error' => 'not_worker',
                'message' => 'Only workers can select a worker type.',
            ], 403);
        }

        $workerTypes = WorkerType::pluck('slug')->toArray();

        // Mobile sends uppercase (e.g. 'STUDENT', 'BLUE_COLLAR')
        // DB slugs are lowercase. Normalize before validation.
        $workerTypeRaw = strtolower(trim($request->worker_type ?? ''));
        $request->merge(['worker_type' => $workerTypeRaw]);

        $request->validate([
            'worker_type' => 'required|string|in:'.implode(',', $workerTypes),
        ]);

        // Also accept optional profile fields in same call (convenience)
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'preferred_language' => 'nullable|string|max:20',
            'expected_salary' => 'nullable|numeric|min:0',
            'available_date' => 'nullable|date',
        ]);

        $workerType = WorkerType::where('slug', $workerTypeRaw)->firstOrFail();

        $profileUpdates = array_filter([
            'full_name' => $request->full_name,
            'nationality' => $request->nationality,
            'current_city' => $request->city,
            'preferred_language' => $request->preferred_language,
            'expected_salary' => $request->expected_salary,
            'available_date' => $request->available_date,
        ], fn ($v) => $v !== null);

        $user->update(array_merge($profileUpdates, [
            'role' => 'worker',  // implicitly set role — no need to call /onboarding/role
            'worker_type' => $workerType->slug,
            'worker_type_id' => $workerType->id,
            'onboarding_step' => max($user->onboarding_step ?? 1, 5),
            'sponsorship_required' => false, // $request->worker_type === 'white_collar',
            // verified_badge_status remains 'unverified' until admin approves personal docs (Phase 1)
        ]));

        // Generate document checklist for this worker type
        $this->workerStatus->generateDocumentChecklist($user->fresh());

        return response()->json([
            'success' => true,
            'message' => "Worker type set to '{$workerType->worker_type_name}'. Document checklist generated.",
            'data' => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * POST /api/onboarding/employer-category
     * Step 5E: Employer selects their category (company, agency, etc.)
     * Separate from role — sets verification to pending.
     */
    public function selectEmployerCategory(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isEmployer()) {
            return response()->json([
                'success' => false,
                'error' => 'not_employer',
                'message' => 'Only employers can set employer category.',
            ], 403);
        }

        $request->validate([
            // Mobile sends 'ubn', legacy sends 'unified_business_number' — accept both
            'ubn' => 'nullable|string|max:50',
            'unified_business_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',  // nullable: employer may set via profile
            'company_info' => 'nullable|string|max:1000', // mobile field
            'industry' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',  // mobile field
            'phone' => 'nullable|string|max:20',
            'employer_type' => 'nullable|string|in:COMPANY,FACTORY,AGENCY_COMPANY,AGENCY_STAFF,FAMILY_EMPLOYER',
        ]);

        // Normalize: prefer 'ubn' if sent, fallback to 'unified_business_number'
        $ubn = $request->ubn ?? $request->unified_business_number;
        $companyName = $request->company_name ?? $user->company_name;

        if (! $companyName) {
            return response()->json([
                'success' => false,
                'error' => 'missing_company_name',
                'message' => 'company_name is required.',
            ], 422);
        }

        // Map mobile employer_type → role if provided
        $roleMap = [
            'COMPANY' => 'company',
            'FACTORY' => 'factory',
            'AGENCY_COMPANY' => 'agency',
            'AGENCY_STAFF' => 'agency_staff',
            'FAMILY_EMPLOYER' => 'family_care',
        ];

        $role = null;
        if ($request->employer_type) {
            $role = $roleMap[strtoupper($request->employer_type)] ?? null;
        }

        $updateData = [
            'company_name' => $companyName,
            'industry' => $request->industry ?? $user->industry,
            'unified_business_number' => $ubn,
            'company_info' => $request->company_info,
            'verification_status' => 'pending',
            'onboarding_step' => max($user->onboarding_step ?? 1, 5),
        ];
        if ($role) {
            $updateData['role'] = $role;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Employer information saved. Please upload verification documents.',
            'data' => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * GET /api/worker-types
     * Public: list all worker types.
     */
    public function workerTypes(): JsonResponse
    {
        $types = WorkerType::all();

        return response()->json([
            'success' => true,
            'data' => $types->map->toApiArray()->values(),
        ]);
    }
}
