<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Services\JobScreeningService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    /**
     * GET /api/jobs
     * List jobs with search + filters.
     * Filters: search, city, category, job_type_id, eligibility, language, salary_min, salary_max, urgent
     */
    public function index(Request $request): JsonResponse
    {
        $query = Job::active()->with('jobType');

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('city')) {
            $query->byCity($request->city);
        }
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        if ($request->filled('job_type_id')) {
            $query->byJobType((int) $request->job_type_id);
        }
        if ($request->filled('eligibility')) {
            $query->byEligibility($request->eligibility);
        }
        if ($request->filled('language')) {
            $query->byLanguage($request->language);
        }
        if ($request->filled('salary_min') || $request->filled('salary_max')) {
            $query->bySalaryRange(
                $request->filled('salary_min') ? (float) $request->salary_min : null,
                $request->filled('salary_max') ? (float) $request->salary_max : null,
            );
        }
        if ($request->boolean('urgent')) {
            $query->urgent();
        }

        // Sort: sponsored first, then urgent, then newest
        $query->orderByDesc('is_sponsored')
              ->orderByDesc('is_urgent')
              ->orderByDesc('posted_at');

        $perPage = min((int) $request->input('per_page', 20), 100);
        $jobs    = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($jobs->items())->map->toApiArray()->values(),
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page'    => $jobs->lastPage(),
                'per_page'     => $jobs->perPage(),
                'total'        => $jobs->total(),
            ],
        ]);
    }

    /**
     * GET /api/jobs/{id}
     */
    public function show(string $id): JsonResponse
    {
        $job = Job::with('employer')->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => 'Job not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $job->toApiArray(isOwner: true),
        ]);
    }

    /**
     * POST /api/jobs
     * Create a new job posting (employer only).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isEmployer()) {
            return response()->json([
                'success' => false,
                'error' => 'forbidden',
                'message' => 'Only employers can create job postings',
            ], 403);
        }

        // PRD Rule: Employer dengan status "Unverified" tidak diizinkan mempublikasikan lowongan.
        // New: check ready_to_work_status (badge system) OR legacy verification_status OR verified_badge_status
        $isVerified = $user->isVerifiedEmployer();

        $requestedStatus = $request->input('status', 'published');

        if (($requestedStatus === 'published' || $requestedStatus === 'submitted_for_review') && !$isVerified) {
            return response()->json([
                'success' => false,
                'error'   => 'unverified_employer',
                'message' => 'Employer dengan status "Unverified" atau "Pending" tidak diizinkan mempublikasikan lowongan.',
            ], 403);
        }

        // PRD Rule: Agensi tanpa nomor lisensi (license number) yang terverifikasi tidak bisa memposting lowongan.
        if ($user->role === 'agency' && empty($user->license_number)) {
            return response()->json([
                'success' => false,
                'error' => 'missing_license',
                'message' => 'Agensi wajib mencantumkan nomor lisensi (License Number) yang terverifikasi untuk memposting lowongan.',
            ], 403);
        }

        $validated = $request->validate([
            'title'                => 'required|string|max:255',
            'location'             => 'required|string|max:100',
            'salary'               => 'required|string|max:100',
            'salary_period'        => 'nullable|in:Month,Day,Hour',
            'tags'                 => 'nullable|array',
            'tags.*'               => 'string',
            'category'             => 'required|string|max:100',
            'job_type_id'          => 'nullable|integer|exists:job_types,id',
            'description'          => 'nullable|string',
            'duties'               => 'nullable|string',
            'requirements'         => 'nullable|string',
            'benefits'             => 'nullable|string',
            'hours'                => 'nullable|string',
            'language'             => 'nullable|string',
            'legal_status'         => 'nullable|string',
            'eligibility'          => 'nullable|string',
            'verification_required'=> 'nullable|boolean',
            'is_urgent'            => 'nullable|boolean',
            'status'               => 'nullable|in:draft,submitted_for_review,published,paused,closed',
            'expires_at'           => 'nullable|date',
            'employment_type'      => 'required|string',
            'working_hours_and_rest_days' => 'required|string',
            'worker_count'         => 'nullable|integer',
            'employment_period'    => 'nullable|string',
            'dormitory_meals_deductions' => 'nullable|string',
            'contact_method'       => 'required|string',
            'mask_contact_info'    => 'nullable|boolean',
            'employer_authorization_url' => $user->role === 'agency' ? 'required|string|max:500' : 'nullable|string|max:500',
            'job_source_proof_url'       => 'nullable|string|max:500',
            'fee_table_url'              => 'nullable|string|max:500',
        ]);

        // Anti-XSS Sanitization
        $textFields = ['description', 'duties', 'requirements', 'benefits', 'hours'];
        foreach ($textFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = strip_tags($validated[$field]);
            }
        }

        $requestedStatus = $validated['status'] ?? null;
        
        // PRD Rule: Job dengan eligibility Unknown tidak bisa publish
        if ($requestedStatus === 'published' && ($validated['eligibility'] ?? 'Unknown') === 'Unknown') {
            return response()->json([
                'success' => false,
                'error' => 'invalid_eligibility',
                'message' => 'Lowongan dengan eligibility Unknown tidak dapat dipublish. Silakan ubah ke draft atau perbaiki eligibility.',
            ], 422);
        }

        // PRD Rule: Lowongan kerja untuk jenis Family Care, Factory, dan Agency/Agency Staff WAJIB melalui manual review oleh Admin.
        if (in_array($user->role, ['factory', 'family_care', 'agency', 'agency_staff'])) {
            // Jika request ingin mempublish, paksa menjadi submitted_for_review
            $status = ($requestedStatus === 'published') ? 'submitted_for_review' : ($requestedStatus ?? 'draft');
        } else {
            $status = $requestedStatus ?? 'published';
        }

        // Proteksi agar role non-admin tidak bypass review 
        // Jika status yang di-pass adalah submitted_for_review, dan employer ingin langsung publish? 
        // Kode di atas sudah memastikan jika role wajib review ingin publish, jadi review.
        // Jika dia role company (tidak wajib review), dia boleh set ke publish atau draft.
        if ($status === 'published' && ($validated['eligibility'] ?? 'Unknown') === 'Unknown') {
             $status = 'draft'; // fallback jika terlewat (tapi sudah di block di awal)
        }

        if (isset($validated['salary'])) {
            $salaryInput = $validated['salary'];
            preg_match_all('/\d+/', str_replace(',', '', $salaryInput), $matches);
            $maxSalary = 0;
            if (!empty($matches[0])) {
                $maxSalary = max(array_map('intval', $matches[0]));
            }

            $hiddenKeywords = ['nego', 'negotiable', '面議', 'discuss'];
            $isHidden = false;
            foreach ($hiddenKeywords as $kw) {
                if (stripos($salaryInput, $kw) !== false) {
                    $isHidden = true;
                    break;
                }
            }

            // UAT #18: NTS minimum wage enforcement - block hidden salary
            if ($isHidden || empty($matches[0])) {
                return response()->json(['success'=>false,'error'=>'salary_hidden_not_allowed','message'=>'Cantumkan gaji eksplisit minimal NT$40,000/bulan. Gaji Nego/Negotiable tidak diperbolehkan.'], 422);
            }
            // Block salary below NT$40,000/month
            if ($maxSalary < 40000) {
                return response()->json([
                    'success' => false,
                    'error'   => 'salary_below_minimum',
                    'message' => "Gaji NT\${$maxSalary} di bawah minimum platform NT\$40,000/bulan.",
                ], 422);
            }
        }

        // Auto-screening logic for M5
        // Keywords that indicate scam / illegal job offers
        $illegalKeywords = [
            'registration fee', 'administration fee', 'recruitment fee',
            'deposit wajib', 'biaya pendaftaran', 'biaya administrasi',
            'agen liar', 'illegal broker',
            'transfer uang', 'transfer money',
        ];
        $riskLevel = 'low';
        $contentToCheck = strtolower(
            ($validated['title']        ?? '') . ' ' .
            ($validated['duties']       ?? '') . ' ' .
            ($validated['description']  ?? '') . ' ' .
            ($validated['requirements'] ?? '')
        );

        foreach ($illegalKeywords as $keyword) {
            if (str_contains($contentToCheck, $keyword)) {
                $riskLevel = 'critical';
                break;
            }
        }

        // M5 Acceptance Criteria: critical risk → auto-reject (not just review)
        if ($riskLevel === 'critical') {
            $status = 'rejected';
        }


        DB::beginTransaction();
        try {
            $job = Job::create([
                ...$validated,
                'employer_id'      => $user->id,
                'employer_name'    => $user->company_name ?? $user->full_name ?? $user->name,
                'employer_type'    => $user->role,
                'salary_period'    => $validated['salary_period'] ?? 'Month',
                'is_urgent'        => $validated['is_urgent'] ?? false,
                'status'           => $status,
                'risk_level'       => 'low', // will be updated by screening below
                'posted_at'        => now(),
            ]);

            // M5: Run full rule-based screening (saves red_flags, missing_fields, risk_level)
            $screenResult = app(JobScreeningService::class)->screenAndSave($job);
            $job->refresh();

            // If auto-rejected by screening, override status
            if ($screenResult['auto_rejected'] && $status !== 'rejected') {
                $job->update(['status' => 'rejected']);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        if ($job->status === 'published' && $job->is_urgent) {
            $tokens = User::workers()->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            if (!empty($tokens)) {
                app(NotificationService::class)->sendMulticast(
                    $tokens,
                    'URGENT: Lowongan Baru!',
                    "Lowongan {$job->title} baru saja dibuka. Cek sekarang!",
                    [
                        'type' => 'urgent_job',
                        'job_id' => (string) $job->id,
                    ]
                );
            }
        }

        $message = match($status) {
            'rejected'            => 'Job automatically rejected due to prohibited content. Please review and resubmit.',
            'submitted_for_review' => 'Job posting submitted successfully and is pending manual review by Admin.',
            default               => 'Job posting published successfully.',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $job->toApiArray(isOwner: true),
        ], 201);
    }

    /**
     * PUT /api/jobs/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $job = Job::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => 'Job not found',
            ], 404);
        }

        if ($job->employer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'forbidden',
                'message' => 'You can only edit your own job postings',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:100',
            'salary' => 'sometimes|string|max:100',
            'salary_period' => 'sometimes|in:Month,Day,Hour',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'category' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'duties' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'hours' => 'nullable|string',
            'language' => 'nullable|string',
            'legal_status' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'is_urgent' => 'nullable|boolean',
            'status' => 'sometimes|in:draft,submitted_for_review,published,paused,closed,rejected',
            'expires_at' => 'nullable|date',
            'employment_type'      => 'sometimes|required|string',
            'working_hours_and_rest_days' => 'sometimes|required|string',
            'worker_count'         => 'nullable|integer',
            'employment_period'    => 'nullable|string',
            'dormitory_meals_deductions' => 'nullable|string',
            'contact_method'       => 'sometimes|required|string',
            'mask_contact_info'    => 'nullable|boolean',
            'employer_authorization_url' => $user->role === 'agency' ? 'required|string|max:500' : 'nullable|string|max:500',
            'job_source_proof_url'       => 'nullable|string|max:500',
            'fee_table_url'              => 'nullable|string|max:500',
        ]);

        // Anti-XSS Sanitization
        $textFields = ['description', 'duties', 'requirements', 'benefits', 'hours'];
        foreach ($textFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = strip_tags($validated[$field]);
            }
        }

        if (isset($validated['salary'])) {
            $salaryInput = $validated['salary'];
            preg_match_all('/\d+/', str_replace(',', '', $salaryInput), $matches);
            $maxSalary = 0;
            if (!empty($matches[0])) {
                $maxSalary = max(array_map('intval', $matches[0]));
            }

            $hiddenKeywords = ['nego', 'negotiable', '面議', 'discuss'];
            $isHidden = false;
            foreach ($hiddenKeywords as $kw) {
                if (stripos($salaryInput, $kw) !== false) {
                    $isHidden = true;
                    break;
                }
            }

            if ($maxSalary < 40000 && ($isHidden || empty($matches[0]))) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_salary_hidden',
                    'message' => 'Menurut hukum Taiwan (UU Layanan Ketenagakerjaan Pasal 5), Anda harus mencantumkan kisaran gaji secara eksplisit jika gaji di bawah NT$40.000/bulan.',
                ], 422);
            }
        }

        $isVerified = $user->isVerifiedEmployer();

        if (isset($validated['status']) && in_array($validated['status'], ['published', 'submitted_for_review'])) {
            if (!$isVerified) {
                return response()->json([
                    'success' => false,
                    'error'   => 'unverified_employer',
                    'message' => 'Employer dengan status "Unverified" atau "Pending" tidak diizinkan mempublikasikan lowongan.',
                ], 403);
            }

            $eligibility = $validated['eligibility'] ?? $job->eligibility;
            if ($eligibility === 'Unknown') {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_eligibility',
                    'message' => 'Lowongan dengan eligibility Unknown tidak dapat dipublish.',
                ], 422);
            }

            if (in_array($user->role, ['factory', 'family_care', 'agency', 'agency_staff'])) {
                $validated['status'] = 'submitted_for_review';
            }
        }

        // Auto-screening on update — reset first, then re-check
        $illegalKeywords = [
            'registration fee', 'administration fee', 'recruitment fee',
            'deposit wajib', 'biaya pendaftaran', 'biaya administrasi',
            'agen liar', 'illegal broker',
            'transfer uang', 'transfer money',
        ];
        $contentToCheck = strtolower(
            ($validated['title']        ?? $job->title) . ' ' .
            ($validated['duties']       ?? $job->duties) . ' ' .
            ($validated['description']  ?? $job->description) . ' ' .
            ($validated['requirements'] ?? $job->requirements)
        );

        // Default: reset risk (in case the employer fixed the content)
        $validated['risk_level']       = 'low';
        $validated['rejection_reason'] = null;
        if (isset($validated['status']) && $validated['status'] === 'rejected') {
            $validated['status'] = in_array($user->role, ['factory', 'family_care', 'agency', 'agency_staff']) ? 'submitted_for_review' : 'published';
        }

        foreach ($illegalKeywords as $keyword) {
            if (str_contains($contentToCheck, $keyword)) {
                $validated['risk_level']       = 'critical';
                $validated['status']           = 'rejected';
                $validated['rejection_reason'] = 'Job automatically rejected: content contains prohibited keywords indicating potential scam or illegal activity.';
                break;
            }
        }

        DB::beginTransaction();
        try {
            $job->update($validated);

            // M5: Re-run full screening after update
            app(JobScreeningService::class)->screenAndSave($job);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'data' => $job->fresh()->toApiArray(isOwner: true),
        ]);
    }

    /**
     * DELETE /api/jobs/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $job  = Job::find($id);

        if (! $job) {
            return response()->json([
                'success' => false, 'error' => 'not_found', 'message' => 'Job not found',
            ], 404);
        }

        if ($job->employer_id !== $user->id) {
            return response()->json([
                'success' => false, 'error' => 'forbidden',
                'message' => 'You can only delete your own job postings',
            ], 403);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully',
        ]);
    }

    /**
     * POST /api/jobs/{id}/publish
     * Employer publishes a draft or paused job.
     */
    public function publish(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $job  = Job::where('employer_id', $user->id)->find($id);

        if (! $job) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Job not found.'], 404);
        }

        $isVerified = $user->isVerifiedEmployer();

        if (! $isVerified) {
            return response()->json([
                'success' => false,
                'error'   => 'unverified_employer',
                'message' => 'Employer dengan status "Unverified" atau "Pending" tidak diizinkan mempublikasikan lowongan.',
            ], 403);
        }

        if ($job->eligibility === 'Unknown') {
            return response()->json([
                'success' => false, 'error' => 'invalid_eligibility',
                'message' => 'Set eligibility before publishing.'
            ], 422);
        }

        $newStatus = in_array($user->role, ['factory', 'family_care', 'agency', 'agency_staff'])
            ? 'submitted_for_review'
            : 'published';

        $job->update(['status' => $newStatus, 'posted_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'published' ? 'Job published.' : 'Job submitted for review.',
            'data'    => $job->fresh()->toApiArray(isOwner: true),
        ]);
    }

    /**
     * POST /api/jobs/{id}/pause
     */
    public function pause(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $job  = Job::where('employer_id', $user->id)->find($id);

        if (! $job) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Job not found.'], 404);
        }

        $job->update(['status' => 'paused']);

        return response()->json([
            'success' => true,
            'message' => 'Job paused.',
            'data'    => $job->fresh()->toApiArray(isOwner: true),
        ]);
    }

    /**
     * POST /api/jobs/{id}/close
     */
    public function close(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $job  = Job::where('employer_id', $user->id)->find($id);

        if (! $job) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Job not found.'], 404);
        }

        $job->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'message' => 'Job closed.',
            'data'    => $job->fresh()->toApiArray(isOwner: true),
        ]);
    }

    /**
     * POST /api/jobs/{id}/duplicate
     * Duplicate an existing job as a new draft (employer only).
     * M4 Gap: Duplicate job feature.
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $job  = Job::where('employer_id', $user->id)->find($id);

        if (! $job) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Job not found.'], 404);
        }

        // Create a copy with status=draft, reset risk_level and rejection_reason
        $duplicate = Job::create([
            'employer_id'                  => $user->id,
            'title'                        => $job->title . ' (Copy)',
            'employer_name'                => $job->employer_name,
            'employer_type'                => $job->employer_type,
            'location'                     => $job->location,
            'salary'                       => $job->salary,
            'salary_period'                => $job->salary_period,
            'employment_type'              => $job->employment_type,
            'working_hours_and_rest_days'  => $job->working_hours_and_rest_days,
            'worker_count'                 => $job->worker_count,
            'employment_period'            => $job->employment_period,
            'dormitory_meals_deductions'   => $job->dormitory_meals_deductions,
            'contact_method'               => $job->contact_method,
            'mask_contact_info'            => $job->mask_contact_info,
            'tags'                         => $job->tags,
            'category'                     => $job->category,
            'job_type_id'                  => $job->job_type_id,
            'description'                  => $job->description,
            'duties'                       => $job->duties,
            'requirements'                 => $job->requirements,
            'benefits'                     => $job->benefits,
            'hours'                        => $job->hours,
            'language'                     => $job->language,
            'legal_status'                 => $job->legal_status,
            'eligibility'                  => $job->eligibility,
            'verification_required'        => $job->verification_required,
            'employer_authorization_url'   => $job->employer_authorization_url, // fix: was missing
            'job_source_proof_url'         => $job->job_source_proof_url,        // fix: was missing
            'fee_table_url'                => $job->fee_table_url,               // fix: was missing
            'is_urgent'                    => false,
            'status'                       => 'draft',
            'risk_level'                   => 'low',
            'rejection_reason'             => null,
            'posted_at'                    => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job duplicated as draft. You can edit and publish it.',
            'data'    => $duplicate->toApiArray(isOwner: true),
        ], 201);
    }

    /**
     * GET /api/employer/jobs
     * List all job listings created by the authenticated employer (with filters).
     */
    public function employerJobs(Request $request): JsonResponse
    {
        $user = $request->user();

        // Base query: query jobs belonging to the employer.
        // For agency_staff, we also allow listing jobs of the parent agency company.
        $employerIds = [$user->id];
        if ($user->role === 'agency_staff' && $user->employerStaff) {
            $employerIds[] = $user->employerStaff->agency_employer_id;
        }

        $query = Job::whereIn('employer_id', $employerIds)->with('jobType');

        // Filter by status if provided (supports string, array, or comma-separated string)
        if ($request->has('status')) {
            $statusInput = $request->status;
            if (is_array($statusInput)) {
                $query->whereIn('status', $statusInput);
            } elseif (is_string($statusInput) && trim($statusInput) !== '') {
                $statuses = array_map('trim', explode(',', $statusInput));
                $query->whereIn('status', $statuses);
            }
        }

        // Optional search filter (title, description, etc.)
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Order by latest created_at
        $query->latest();

        // Paginate results (default: 20, max: 100)
        $perPage = min((int) $request->input('per_page', 20), 100);
        $jobs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($jobs->items())->map(fn($j) => $j->toApiArray(isOwner: true))->values(),
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page'    => $jobs->lastPage(),
                'per_page'     => $jobs->perPage(),
                'total'        => $jobs->total(),
            ],
        ]);
    }
}



