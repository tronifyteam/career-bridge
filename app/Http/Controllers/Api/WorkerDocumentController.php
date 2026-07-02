<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\WorkerDocument;
use App\Services\WorkerStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerDocumentController extends Controller
{
    public function __construct(private WorkerStatusService $workerStatus) {}

    /**
     * GET /api/worker/documents
     * List all uploaded documents for the authenticated worker.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $documents = WorkerDocument::with('documentType')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $documents->map->toApiArray()->values(),
        ]);
    }

    /**
     * GET /api/worker/document-checklist
     * Get the document requirement checklist for the authenticated worker.
     * Documents are SKIPPABLE — this is just a guide.
     */
    public function checklist(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isWorker()) {
            return response()->json([
                'success' => false,
                'error'   => 'not_worker',
                'message' => 'Only workers have a document checklist.',
            ], 403);
        }

        // If no checklist yet (worker_type not set), return empty with hint
        if (! $user->worker_type) {
            return response()->json([
                'success' => true,
                'data' => [
                    'worker_type'    => null,
                    'total_required' => 0,
                    'requirements'   => [],
                    'hint'           => 'Please select your worker type first to see required documents.',
                ],
            ]);
        }

        $checklist = $this->workerStatus->getChecklistStatus($user);

        return response()->json([
            'success' => true,
            'data'    => array_merge(['worker_type' => $user->worker_type], $checklist),
        ]);
    }

    /**
     * POST /api/worker/documents
     * Upload a document (SKIPPABLE — worker can do this any time).
     *
     * Accepts:
     *   - document_type (string slug) — used by mobile wizard / DocumentCatalog
     *   - document_type_id (integer)  — used by legacy flows
     *   - file field name: 'document' (mobile) OR 'file' (legacy) — both accepted
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // ── Normalize file field: mobile sends 'document', legacy sends 'file' ──
        if ($request->hasFile('document') && ! $request->hasFile('file')) {
            $request->files->set('file', $request->file('document'));
        }

        $request->validate([
            'file'             => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            // Accept either slug OR integer id — one of them must be present
            'document_type'    => 'nullable|string',
            'document_type_id' => 'nullable|integer|exists:document_types,id',
        ]);

        // ── Slug alias map: mobile DocumentCatalog id → DB slug ───────────────
        // Mobile uses its own IDs from DocumentCatalog; some differ from DB slugs
        $slugAliases = [
            'student_permit'         => 'student_work_permit',  // mobile → DB
            'business_proof'         => 'business_registration', // mobile → DB
            'household_registration' => 'family_employer_id',    // mobile → DB
            'staff_authorization'    => 'agency_staff_card',     // mobile → DB
        ];

        // ── Resolve DocumentType from slug or id ──────────────────────────────
        if ($request->filled('document_type')) {
            $rawSlug = $request->document_type;
            $slug    = $slugAliases[$rawSlug] ?? $rawSlug; // normalize alias
            $docType = DocumentType::where('slug', $slug)->first();
            if (! $docType) {
                return response()->json([
                    'success' => false,
                    'error'   => 'invalid_document_type',
                    'message' => "Unknown document_type: '{$rawSlug}'. Check the slug mapping.",
                ], 422);
            }
        } elseif ($request->filled('document_type_id')) {
            $docType = DocumentType::find($request->document_type_id);
            if (! $docType) {
                return response()->json([
                    'success' => false,
                    'error'   => 'invalid_document_type_id',
                    'message' => 'document_type_id not found.',
                ], 422);
            }
        } else {
            return response()->json([
                'success' => false,
                'error'   => 'missing_document_type',
                'message' => 'Provide either document_type (slug) or document_type_id.',
            ], 422);
        }

        $file = $request->file('file');

        $filename = 'doc_' . $user->id . '_' . $docType->slug . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('worker_documents', $filename, 'public');
        $url      = asset(\Illuminate\Support\Facades\Storage::url($path));

        DB::beginTransaction();
        try {
            // Check if there is an existing pending document of this type
            $existing = WorkerDocument::where('user_id', $user->id)
                ->where('document_type_id', $docType->id)
                ->where('review_status', 'pending')
                ->first();

            if ($existing) {
                $existing->update([
                    'file_url'          => $url,
                    'original_filename' => $file->getClientOriginalName(),
                ]);
                $document = $existing;
            } else {
                // Create the document record
                $document = WorkerDocument::create([
                    'user_id'           => $user->id,
                    'document_type_id'  => $docType->id,
                    'file_url'          => $url,
                    'original_filename' => $file->getClientOriginalName(),
                    'review_status'     => 'pending',
                ]);
            }

            // Update or create requirement entry
            \App\Models\WorkerDocumentRequirement::updateOrCreate(
                ['user_id' => $user->id, 'document_type_id' => $docType->id],
                [
                    'upload_status'      => 'uploaded',
                    'worker_document_id' => $document->id,
                ]
            );

            // Special: if this is a selfie, update the selfie_file_url on the user
            if ($docType->slug === 'selfie') {
                $user->update(['selfie_file_url' => $url]);
            }

            // Update user verification status to pending
            if (in_array($docType->slug, ['selfie', 'personal_id', 'personal_document'])) {
                $user->update(['verified_badge_status' => 'pending']);
            } else {
                $user->update(['ready_to_work_status' => 'pending']);
            }

            // Advance onboarding step if applicable
            if (($user->onboarding_step ?? 1) < 6) {
                $user->update(['onboarding_step' => 6]);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => "Document '{$docType->document_type_name}' uploaded successfully. Awaiting admin review.",
            'data'    => $document->load('documentType')->toApiArray(),
        ], 201);
    }

    /**
     * POST /api/worker/personal-documents
     * Phase 1 document upload — personal identity documents (passport, ARC, etc.)
     * These trigger the "Verified Badge" review by admin.
     *
     * Accepted document_type slugs (mobile → DB mapping):
     *   - passport_arc → personal_id  (general personal identity)
     *   - selfie_photo → selfie        (already taken in Step 3, reused here)
     *   - any other slug passed through
     */
    public function storePersonalDocument(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isWorker()) {
            return response()->json([
                'success' => false,
                'error'   => 'not_worker',
                'message' => 'Only workers can upload personal identity documents.',
            ], 403);
        }

        // ── Normalize file field: mobile sends 'document', legacy sends 'file' ──
        if ($request->hasFile('document') && ! $request->hasFile('file')) {
            $request->files->set('file', $request->file('document'));
        }

        $request->validate([
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'document_type' => 'nullable|string',
        ]);

        // Slug alias map: mobile personal doc IDs → DB slugs
        $slugAliases = [
            'passport_arc'       => 'personal_id',
            'passport_or_arc'    => 'personal_id',
            'selfie_photo'       => 'selfie',
            'other_id'           => 'personal_id',
        ];

        $rawSlug = $request->input('document_type', 'personal_id');
        $slug    = $slugAliases[$rawSlug] ?? $rawSlug;

        $docType = DocumentType::where('slug', $slug)->first();

        // Fallback: if slug not found in DB, use the first available personal ID type
        if (! $docType) {
            $docType = DocumentType::where('slug', 'personal_id')->first();
        }

        if (! $docType) {
            return response()->json([
                'success' => false,
                'error'   => 'document_type_not_configured',
                'message' => "Document type '{$rawSlug}' is not configured in the database.",
            ], 422);
        }

        $file     = $request->file('file');
        $filename = 'personal_' . $user->id . '_' . $docType->slug . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('worker_personal_documents', $filename, 'public');
        $url      = asset(\Illuminate\Support\Facades\Storage::url($path));

        DB::beginTransaction();
        try {
            // Create or replace the document record for this type
            $existing = WorkerDocument::where('user_id', $user->id)
                ->where('document_type_id', $docType->id)
                ->where('review_status', 'pending')
                ->first();

            if ($existing) {
                $existing->update([
                    'file_url'          => $url,
                    'original_filename' => $file->getClientOriginalName(),
                ]);
                $document = $existing;
            } else {
                $document = WorkerDocument::create([
                    'user_id'           => $user->id,
                    'document_type_id'  => $docType->id,
                    'file_url'          => $url,
                    'original_filename' => $file->getClientOriginalName(),
                    'review_status'     => 'pending',
                ]);
            }

            // Update the requirement entry
            \App\Models\WorkerDocumentRequirement::updateOrCreate(
                ['user_id' => $user->id, 'document_type_id' => $docType->id],
                [
                    'upload_status'      => 'uploaded',
                    'worker_document_id' => $document->id,
                ]
            );

            // Special: selfie doc also updates selfie_file_url
            if ($docType->slug === 'selfie') {
                $user->update(['selfie_file_url' => $url]);
            }

            // Set badge status to pending for admin review (Phase 1: Verified Badge)
            $user->update(['verified_badge_status' => 'pending']);

            // AI Verification for selfie/KTP
            $selfieDoc = WorkerDocument::where('user_id', $user->id)
                ->whereHas('documentType', fn($q) => $q->where('slug', 'selfie'))
                ->first();

            $ktpDoc = WorkerDocument::where('user_id', $user->id)
                ->whereHas('documentType', fn($q) => $q->where('slug', 'personal_id'))
                ->first();

            if ($selfieDoc && $ktpDoc && ($selfieDoc->review_status === 'pending' || $ktpDoc->review_status === 'pending')) {
                $aiService = new \App\Services\AiVerificationService();
                $result = $aiService->verifyIdentity($selfieDoc->file_url, $ktpDoc->file_url);

                if ($result) {
                    $isMatch = $result['is_match'] ?? false;
                    $isValidId = $result['is_valid_id'] ?? false;
                    $reason = $result['reason'] ?? 'AI Verification completed';
                    
                    if ($isMatch && $isValidId) {
                        $selfieDoc->update(['review_status' => 'approved', 'rejection_reason' => null]);
                        $ktpDoc->update(['review_status' => 'approved', 'rejection_reason' => null]);
                        $user->update(['verified_badge_status' => 'approved']);
                        $statusMsg = "AI Auto-Approved: {$reason}";
                    } else {
                        $selfieDoc->update(['review_status' => 'rejected', 'rejection_reason' => $reason]);
                        $ktpDoc->update(['review_status' => 'rejected', 'rejection_reason' => $reason]);
                        $user->update(['verified_badge_status' => 'rejected']);
                        $statusMsg = "AI Auto-Rejected: {$reason}";
                    }

                    \Illuminate\Support\Facades\Log::info("User {$user->id} Verified Badge processed via AI: {$statusMsg}");
                    \App\Services\AuditLogService::log(
                        action: 'ai_verification',
                        model: $user,
                        description: $statusMsg
                    );
                }
            }

            // Advance onboarding step
            if (($user->onboarding_step ?? 1) < 6) {
                $user->update(['onboarding_step' => 6]);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => "Personal identity document uploaded. Awaiting Verified Badge review.",
            'data'    => $document->load('documentType')->toApiArray(),
        ], 201);
    }


    /**
     * DELETE /api/worker/documents/{id}
     * Delete a document (only if pending or rejected).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user     = $request->user();
        $document = WorkerDocument::where('user_id', $user->id)->find($id);

        if (! $document) {
            return response()->json([
                'success' => false,
                'error'   => 'not_found',
                'message' => 'Document not found.',
            ], 404);
        }

        if ($document->review_status === 'approved') {
            return response()->json([
                'success' => false,
                'error'   => 'cannot_delete_approved',
                'message' => 'You cannot delete an approved document.',
            ], 422);
        }

        // Reset requirement entry
        \App\Models\WorkerDocumentRequirement::where('user_id', $user->id)
            ->where('document_type_id', $document->document_type_id)
            ->update(['upload_status' => 'not_uploaded', 'worker_document_id' => null]);

        // Delete the file from storage
        $relativePath = (str_contains($document->file_url, 'worker_personal_documents') ? 'worker_personal_documents/' : 'worker_documents/') . basename($document->file_url);
        \Illuminate\Support\Facades\Storage::delete($relativePath);

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }
}
