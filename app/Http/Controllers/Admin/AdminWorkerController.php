<?php

namespace App\Http\Controllers\Admin;

use App\Models\Job;
use App\Models\User;
use App\Models\WorkerDocument;
use App\Models\EmployerDocument;
use App\Services\WorkerStatusService;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * AdminApiController — JSON API for admin actions.
 * Handles: worker badge management, employer approval, job/user moderation.
 * Access: auth:sanctum + role:admin middleware (set in api.php)
 *
 * PDF Section 9 requirements:
 * - Approve/reject Verified Badge
 * - Approve/reject Ready to Work label
 * - Manually change worker status
 * - Approve/reject employer permission to publish jobs
 * - Hide/suspend suspicious user or job posting
 */
class AdminWorkerController extends Controller
{
    public function __construct(
        private WorkerStatusService $workerStatus,
        private FcmService $fcmService
    ) {}

    // ──────────────────────────────────────────────────────────────
    // EMPLOYER MANAGEMENT (Blade — web routes)
    // ──────────────────────────────────────────────────────────────

    public function employerIndex(Request $request)
    {
        $query = User::employers()
            ->whereNotIn('role', ['agency', 'agency_staff'])
            ->with(['documents'])
            ->withCount('jobs')
            ->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('unified_business_number', 'like', "%{$search}%")
                ->orWhere('license_number', 'like', "%{$search}%")
            );
        }
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }
        if ($vs = $request->get('verification_status')) {
            $query->where('verification_status', $vs);
        }
        if ($bs = $request->get('badge_status')) {
            $query->where('verified_badge_status', $bs);
        }

        $employers = $query->paginate(20);
        return view('admin.employers.index', compact('employers'));
    }

    public function employerShow(Request $request, $id)
    {
        $employer = User::find($id);
        if (! $employer || ! $employer->isEmployer()) {
            abort(404);
        }

        $documents = $employer->documents()->orderByDesc('created_at')->get();
        $logs = \App\Models\VerificationLog::where('entity_type', 'employer')
                    ->where('entity_id', $employer->id)
                    ->with('verifiedBy')
                    ->latest('verified_at')
                    ->take(20)
                    ->get();

        return view('admin.employers.show', compact('employer', 'documents', 'logs'));
    }

    // ──────────────────────────────────────────────────────────────
    // WORKER MANAGEMENT (Blade — web routes)
    // ──────────────────────────────────────────────────────────────


    public function index(Request $request)
    {
        // Detect JSON request (API) vs Blade (web)
        if ($request->expectsJson() || $request->is('api/*')) {
            $workers = User::workers()
                ->with(['workerLanguages.language', 'workerJobTypes.jobType', 'workerDocuments'])
                ->orderByDesc('created_at')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data'    => collect($workers->items())->map->toApiArray()->values(),
                'meta'    => [
                    'current_page' => $workers->currentPage(),
                    'last_page'    => $workers->lastPage(),
                    'total'        => $workers->total(),
                ],
            ]);
        }

        $query = User::workers()
            ->with(['workerLanguages.language', 'workerJobTypes.jobType', 'workerDocuments'])
            ->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('nationality', 'like', "%{$search}%")
            );
        }
        if ($bs = $request->get('badge_status')) {
            $query->where('verified_badge_status', $bs);
        }
        if ($rs = $request->get('ready_status')) {
            $query->where('ready_to_work_status', $rs);
        }
        if ($hs = $request->get('has_selfie')) {
            $hs === 'yes'
                ? $query->whereNotNull('selfie_file_url')
                : $query->whereNull('selfie_file_url');
        }

        $workers = $query->paginate(20);

        return view('admin.workers.index', compact('workers'));
    }

    public function show(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user || ! $user->isWorker()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Worker not found.'], 404);
            }
            abort(404);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            $documents    = $user->workerDocuments()->with('documentType')->get();
            $requirements = $user->documentRequirements()->with(['documentType', 'workerDocument'])->get();

            return response()->json([
                'success' => true,
                'data'    => array_merge($user->toApiArray(), [
                    'documents'    => $documents->map->toApiArray()->values(),
                    'requirements' => $requirements->map(fn($r) => [
                        'id'                 => $r->id,
                        'document_type_slug' => $r->documentType->slug ?? null,
                        'document_type_name' => $r->documentType->document_type_name ?? null,
                        'upload_status'      => $r->workerDocument?->review_status ?? 'not_uploaded',
                    ])->values(),
                ]),
            ]);
        }

        $documents    = $user->workerDocuments()->with('documentType')->get();
        $requirements = $user->documentRequirements()->with(['documentType', 'workerDocument'])->get();
        $logs         = \App\Models\VerificationLog::where('entity_type', 'worker')
                            ->where('entity_id', $user->id)
                            ->with('verifiedBy')
                            ->latest('verified_at')
                            ->take(20)
                            ->get();

        return view('admin.workers.show', compact('user', 'documents', 'requirements', 'logs'));
    }

    // ──────────────────────────────────────────────────────────────
    // SELFIE MANAGEMENT
    // ──────────────────────────────────────────────────────────────

    public function approveSelfie(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Worker not found.'], 404);
        }
        if (! $user->selfie_file_url) {
            return response()->json(['success' => false, 'error' => 'no_selfie', 'message' => 'Worker has not uploaded a selfie yet.'], 422);
        }

        DB::beginTransaction();
        try {
            $this->workerStatus->onSelfieApproved($user);
            $this->fcmService->sendToUser($user, 'Verifikasi Wajah Disetujui', 'Foto verifikasi wajah Anda telah disetujui oleh admin.');

            \App\Services\AuditLogService::log('approve_selfie', $user, 'Admin approved selfie');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response()->json([
            'success' => true,
            'message' => "Selfie approved. Verified badge: {$user->fresh()->verified_badge_status}",
            'data'    => $user->fresh()->toApiArray(),
        ]);
    }

    public function rejectSelfie(Request $request, $id): JsonResponse
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Worker not found.'], 404);
        }

        DB::beginTransaction();
        try {
            $user->forceFill([
                'verified_badge_status'     => 'rejected',
                'verified_badge_updated_at' => now(),
                'selfie_verified_at'        => null,
            ])->save();

            // Sync selfie document requirement status to rejected
            $docType = \App\Models\DocumentType::where('slug', 'selfie')->first();
            if ($docType) {
                \App\Models\WorkerDocumentRequirement::where('user_id', $user->id)
                    ->where('document_type_id', $docType->id)
                    ->update(['upload_status' => 'rejected']);

                \App\Models\WorkerDocument::where('user_id', $user->id)
                    ->where('document_type_id', $docType->id)
                    ->update([
                        'review_status' => 'rejected',
                        'review_note'   => $request->note,
                        'reviewed_by'   => auth()->id(),
                        'reviewed_at'   => now(),
                    ]);
            }

            $this->workerStatus->logVerification($user, 'worker', 'rejected', $request->note ?? 'Selfie rejected by admin');
            $this->fcmService->sendToUser($user, 'Verifikasi Wajah Ditolak', 'Foto verifikasi wajah Anda ditolak. ' . ($request->note ?? 'Silakan upload ulang.'));

            \App\Services\AuditLogService::log('reject_selfie', $user, 'Admin rejected selfie with note: ' . $request->note);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response()->json([
            'success' => true,
            'message' => 'Selfie rejected.',
            'data'    => $user->fresh()->toApiArray(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // DOCUMENT MANAGEMENT
    // ──────────────────────────────────────────────────────────────

    public function approveDocument(Request $request, $userId, $docId = null): JsonResponse
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        // Support both /workers/{user}/documents/{document}/approve AND old route
        if ($docId === null) { $docId = $userId; }
        $document = WorkerDocument::find($docId);
        if (! $document) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Document not found.'], 404);
        }

        $document->update([
            'review_status' => 'approved',
            'review_note'   => $request->note,
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        $this->workerStatus->onDocumentApproved($document);
        $this->workerStatus->logVerification($document, 'document', 'approved', $request->note);
        if ($document->user) {
            $docName = $document->documentType?->document_type_name ?? 'Pendukung';
            $this->fcmService->sendToUser($document->user, 'Dokumen Disetujui', "Dokumen {$docName} Anda telah disetujui.");
        }

        
        \App\Services\AuditLogService::log('approve_document', $document, 'Admin approved worker document');
        return response()->json([
            'success' => true,
            'message' => "Document approved.",
            'data'    => $document->load('documentType')->toApiArray(),
        ]);
    }

    public function rejectDocument(Request $request, $userId, $docId = null): JsonResponse
    {
        $request->validate(['note' => 'required|string|max:500']);

        // Support both /workers/{user}/documents/{document}/reject AND old route
        if ($docId === null) { $docId = $userId; }
        $document = WorkerDocument::find($docId);
        if (! $document) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Document not found.'], 404);
        }

        $document->update([
            'review_status' => 'rejected',
            'review_note'   => $request->note,
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);
        $this->workerStatus->onDocumentRejected($document);
        $this->workerStatus->logVerification($document, 'document', 'rejected', $request->note);
        if ($document->user) {
            $docName = $document->documentType?->document_type_name ?? 'Pendukung';
            $this->fcmService?->sendToUser($document->user, 'Dokumen Ditolak', "Dokumen {$docName} Anda ditolak. Alasan: {$request->note}");
        }

        
        \App\Services\AuditLogService::log('reject_document', $document, 'Admin rejected worker document with note: ' . $request->note);
        return response()->json([
            'success' => true,
            'message' => 'Document rejected.',
            'data'    => $document->load('documentType')->toApiArray(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // BADGE OVERRIDE
    // ──────────────────────────────────────────────────────────────

    public function overrideBadge(Request $request, $id): JsonResponse
    {
        $request->validate([
            'verified_badge_status' => 'nullable|in:unverified,pending,verified,rejected',
            'ready_to_work_status'  => 'nullable|in:not_ready,pending,ready,rejected',
            'current_work_status'   => 'nullable|in:blue_collar,white_collar,not_sure', // UAT #46
            'note'                  => 'nullable|string|max:500',
        ]);

        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Worker not found.'], 404);
        }

        $updates = [];
        if ($request->filled('verified_badge_status')) {
            $updates['verified_badge_status']     = $request->verified_badge_status;
            $updates['verified_badge_updated_at'] = now();
        }
        if ($request->filled('ready_to_work_status')) {
            $updates['ready_to_work_status']     = $request->ready_to_work_status;
            $updates['ready_to_work_updated_at'] = now();
        }
        // ── UAT #46: Admin force override current_work_status ───────────────
        if ($request->filled('current_work_status')) {
            $updates['current_work_status'] = $request->current_work_status;
        }

        if ($updates) {
            $user->update($updates);
            $this->workerStatus->logVerification(
                $user, 'worker', 'manual_override',
                $request->note ?? 'Manual override by admin'
            );
            $this->fcmService?->sendToUser($user, 'Status Diperbarui', 'Status profil Anda telah diperbarui oleh admin.');
        }

        \App\Services\AuditLogService::log('override_badge', $user, 'Admin overrode worker status: ' . json_encode($updates));
        return response()->json([
            'success' => true,
            'message' => 'Worker status overridden.',
            'data'    => $user->fresh()->toApiArray(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // EMPLOYER DOCUMENT MANAGEMENT (per-document review)
    // ──────────────────────────────────────────────────────────────

    public function approveEmployerDocument(Request $request, $docId)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $document = \App\Models\EmployerDocument::find($docId);
        if (! $document) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Document not found.'], 404);
            }
            return redirect()->back()->with('error', 'Document not found.');
        }

        DB::beginTransaction();
        try {
            $document->update([
                'status'      => 'approved',
                'review_note' => $request->note,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Auto-set verified_badge if ALL employer documents are approved
            $employer  = $document->user;
            $allDocs   = \App\Models\EmployerDocument::where('user_id', $employer->id)->get();
            $allApproved = $allDocs->every(fn($d) => $d->status === 'approved');

            if ($allApproved && $allDocs->isNotEmpty()) {
                $employer->forceFill([
                    'verified_badge_status'     => 'verified',
                    'verified_badge_updated_at' => now(),
                    'verification_status'       => 'basic_verified',
                ])->save();
                $this->workerStatus->logVerification($employer, 'employer', 'approved', 'All documents approved — badge granted');
                $this->fcmService?->sendToUser($employer, 'Akun Terverifikasi', 'Selamat! Akun perusahaan/majikan Anda telah terverifikasi penuh.');
            } else {
                $this->fcmService?->sendToUser($employer, 'Dokumen Disetujui', 'Salah satu dokumen Anda telah disetujui.');
            }

            \App\Services\AuditLogService::log('approve_employer_document', $document, 'Admin approved employer document');
            if ($allApproved) {
                \App\Services\AuditLogService::log('approve_employer', $employer, 'Admin approved employer registration (all docs approved)');
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response()->json([
                'success'      => true,
                'message'      => 'Employer document approved.' . ($allApproved ? ' Verified badge granted.' : ''),
                'data'         => $document->fresh()->toApiArray(),
                'badge_granted'=> $allApproved,
            ]);
        }

        $msg = 'Employer document approved.';
        if ($allApproved) {
            $msg .= ' Verified badge granted.';
        }
        return redirect()->back()->with('success', $msg);
    }

    public function rejectEmployerDocument(Request $request, $docId)
    {
        $request->validate(['note' => 'required|string|max:500']);

        $document = \App\Models\EmployerDocument::find($docId);
        if (! $document) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Document not found.'], 404);
            }
            return redirect()->back()->with('error', 'Document not found.');
        }

        DB::beginTransaction();
        try {
            $document->update([
                'status'      => 'rejected',
                'review_note' => $request->note,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Ensure employer badge stays unverified
            $document->user->update([
                'verified_badge_status' => 'rejected',
                'verification_status'   => 'rejected',
            ]);

            $this->workerStatus->logVerification($document->user, 'employer', 'rejected', $request->note);
            $this->fcmService->sendToUser($document->user, 'Dokumen Ditolak', "Dokumen Anda ditolak. Alasan: {$request->note}");

            \App\Services\AuditLogService::log('reject_employer_document', $document, 'Admin rejected employer document with note: ' . $request->note);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response()->json([
                'success' => true,
                'message' => 'Employer document rejected.',
                'data'    => $document->fresh()->toApiArray(),
            ]);
        }

        return redirect()->back()->with('success', 'Employer document rejected.');
    }

    // ──────────────────────────────────────────────────────────────
    // EMPLOYER MANAGEMENT (PDF Section 9)
    // ──────────────────────────────────────────────────────────────

    public function approveEmployer(Request $request, $id): JsonResponse
    {
        $employer = User::employers()->find($id);
        if (! $employer) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Employer not found.'], 404);
        }

        DB::beginTransaction();
        try {
            $employer->forceFill([
                'verification_status'      => 'basic_verified',
                'verified_badge_status'    => 'verified',
                'verified_badge_updated_at'=> now(),
                'ready_to_work_status'     => 'ready',  // allows publishing jobs
                'ready_to_work_updated_at' => now(),
            ])->save();

            $this->workerStatus->logVerification($employer, 'employer', 'approved', 'Employer approved by admin');
            $this->fcmService->sendToUser($employer, 'Akun Disetujui', 'Akun Anda telah disetujui oleh admin. Anda sekarang dapat mempublikasikan lowongan kerja.');
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Employer approved. They can now publish jobs.',
            'data'    => $employer->fresh()->toApiArray(),
        ]);
    }

    public function rejectEmployer(Request $request, $id): JsonResponse
    {
        $request->validate(['note' => 'required|string|max:500']);

        $employer = User::employers()->find($id);
        if (! $employer) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Employer not found.'], 404);
        }

        DB::beginTransaction();
        try {
            $employer->forceFill(['verification_status' => 'rejected'])->save();
            $this->workerStatus->logVerification($employer, 'employer', 'rejected', $request->note);
            $this->fcmService->sendToUser($employer, 'Akun Ditolak', 'Pengajuan akun Anda ditolak. Alasan: ' . $request->note);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Employer rejected.',
            'data'    => $employer->fresh()->toApiArray(),
        ]);
    }

    public function suspendEmployer(Request $request, $id): JsonResponse
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $employer = User::employers()->find($id);
        if (! $employer) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Employer not found.'], 404);
        }

        DB::beginTransaction();
        try {
            $employer->forceFill([
                'verification_status'  => 'suspended',
                'ready_to_work_status' => 'rejected',
            ])->save();

            // Pause all active jobs from this employer
            $employer->jobs()->where('status', 'published')->update(['status' => 'paused']);

            $this->workerStatus->logVerification($employer, 'employer', 'suspended', $request->note ?? 'Suspended by admin');
            $this->fcmService->sendToUser($employer, 'Akun Ditangguhkan', 'Akun Anda telah ditangguhkan oleh admin. Semua lowongan aktif Anda dijeda.');

            \App\Services\AuditLogService::log('suspend_employer', $employer, 'Admin suspended employer with note: ' . ($request->note ?? 'N/A'));
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response()->json([
            'success' => true,
            'message' => 'Employer suspended. All active jobs paused.',
            'data'    => $employer->fresh()->toApiArray(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // JOB MODERATION (PDF Section 9)
    // ──────────────────────────────────────────────────────────────

    public function suspendJob(Request $request, $id): JsonResponse
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $job = Job::find($id);
        if (! $job) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Job not found.'], 404);
        }

        $job->update(['status' => 'closed', 'risk_level' => 'high']);
        $this->fcmService->sendToUser($job->employer, 'Lowongan Ditangguhkan', "Lowongan '{$job->title}' Anda telah ditangguhkan oleh admin karena terindikasi risiko tinggi.");

        
        \App\Services\AuditLogService::log('suspend_job', $job, 'Admin suspended job');
        return response()->json([
            'success' => true,
            'message' => 'Job suspended by admin.',
            'data'    => $job->fresh()->toApiArray(),
        ]);
    }

    public function restoreJob(Request $request, $id): JsonResponse
    {
        $job = Job::find($id);
        if (! $job) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'Job not found.'], 404);
        }

        $job->update(['status' => 'published']);
        $this->fcmService->sendToUser($job->employer, 'Lowongan Dipulihkan', "Lowongan '{$job->title}' Anda telah dipulihkan dan kembali aktif.");

        
        \App\Services\AuditLogService::log('restore_job', $job, 'Admin restored job');
        return response()->json([
            'success' => true,
            'message' => 'Job restored.',
            'data'    => $job->fresh()->toApiArray(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // USER MODERATION (PDF Section 9)
    // ──────────────────────────────────────────────────────────────

    public function suspendUser(Request $request, $id): JsonResponse
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'User not found.'], 404);
        }

        $user->forceFill([
            'verification_status'  => 'suspended',
            'ready_to_work_status' => 'rejected',
        ])->save();

        // Revoke all tokens
        $user->tokens()->delete();

        $this->workerStatus->logVerification($user, 'user', 'suspended', $request->note ?? 'Suspended by admin');
        $this->fcmService->sendToUser($user, 'Akun Ditangguhkan', 'Akun Anda telah ditangguhkan oleh sistem/admin.');

        
        \App\Services\AuditLogService::log('suspend_user', $user, 'Admin suspended user with reason: ' . ($request->reason ?? 'N/A'));
        return response()->json([
            'success' => true,
            'message' => 'User suspended and logged out from all devices.',
        ]);
    }

    public function restoreUser(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'not_found', 'message' => 'User not found.'], 404);
        }

        $user->forceFill(['verification_status' => 'unverified'])->save();
        $this->workerStatus->logVerification($user, 'user', 'restored', 'Restored by admin');
        $this->fcmService->sendToUser($user, 'Akun Dipulihkan', 'Akun Anda telah dipulihkan oleh admin.');

        
        \App\Services\AuditLogService::log('restore_user', $user, 'Admin restored user');
        return response()->json([
            'success' => true,
            'message' => 'User restored.',
            'data'    => $user->fresh()->toApiArray(),
        ]);
    }
}
