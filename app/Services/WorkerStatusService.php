<?php

namespace App\Services;

use App\Models\DocumentType;
use App\Models\User;
use App\Models\VerificationLog;
use App\Models\WorkerDocument;
use App\Models\WorkerDocumentRequirement;
use App\Models\WorkerType;

class WorkerStatusService
{
    /**
     * Generate (or refresh) the document requirement checklist for a worker
     * based on their worker_type. Called when worker_type is set/changed.
     */
    public function generateDocumentChecklist(User $worker): void
    {
        if (! $worker->isWorker() || ! $worker->worker_type) {
            return;
        }

        $workerType = WorkerType::where('slug', $worker->worker_type)->first();

        // Documents required for ALL workers
        $universalSlugs = ['personal_id', 'selfie'];

        // Documents required per worker type
        $typeSpecificSlugs = match ($worker->worker_type) {
            'student'       => ['student_work_permit'],
            'blue_collar'   => ['transfer_document', 'work_contract', 'contract_ending_proof'],
            'white_collar'  => [],
            'arc_other', 'aprc', 'taiwanese', 'not_sure', 'other' => [],
            default         => [],
        };

        $allSlugs = array_merge($universalSlugs, $typeSpecificSlugs);

        $documentTypes = DocumentType::whereIn('slug', $allSlugs)->get();

        foreach ($documentTypes as $docType) {
            $uploadStatus = 'not_uploaded';
            $workerDocumentId = null;

            // SPECIAL CASE: if this is a selfie and the user has already uploaded a selfie
            if ($docType->slug === 'selfie' && $worker->selfie_file_url) {
                $uploadStatus = $worker->selfie_verified_at !== null ? 'verified' : 'uploaded';
                
                $document = WorkerDocument::updateOrCreate(
                    ['user_id' => $worker->id, 'document_type_id' => $docType->id],
                    [
                        'file_url'          => $worker->selfie_file_url,
                        'original_filename' => 'selfie.jpg',
                        'review_status'     => $worker->selfie_verified_at !== null ? 'approved' : 'pending',
                    ]
                );
                $workerDocumentId = $document->id;
            }

            // SPECIAL CASE: if this is cv and user already has cv_url
            if ($docType->slug === 'cv' && $worker->cv_url) {
                $uploadStatus = 'verified'; // CV doesn't require admin review (verification_required = false)
                $document = WorkerDocument::updateOrCreate(
                    ['user_id' => $worker->id, 'document_type_id' => $docType->id],
                    [
                        'file_url'          => $worker->cv_url,
                        'original_filename' => 'cv.pdf',
                        'review_status'     => 'approved',
                    ]
                );
                $workerDocumentId = $document->id;
            }

            WorkerDocumentRequirement::updateOrCreate(
                ['user_id' => $worker->id, 'document_type_id' => $docType->id],
                [
                    'upload_status'      => $uploadStatus,
                    'worker_document_id' => $workerDocumentId,
                ]
            );
        }
    }

    /**
     * Evaluate and update verified_badge_status for a user (worker or employer).
     * For workers: badge is verified when selfie is approved by admin.
     * For employers: badge is verified when their documents are approved.
     */
    public function evaluateVerifiedBadge(User $user): void
    {
        if ($user->isWorker()) {
            $this->evaluateWorkerBadge($user);
        } elseif ($user->isEmployer()) {
            $this->evaluateEmployerBadge($user);
        }
    }

    private function evaluateWorkerBadge(User $worker): void
    {
        $selfieApproved = $worker->selfie_verified_at !== null;

        $requiresPersonalId = $worker->worker_type !== 'not_sure';
        $personalIdApproved = true;

        if ($requiresPersonalId) {
            $personalIdApproved = $worker->workerDocuments()
                ->whereHas('documentType', fn($q) => $q->where('slug', 'personal_id'))
                ->where('review_status', 'approved')
                ->exists();
        }

        if ($selfieApproved && $personalIdApproved) {
            if ($worker->verified_badge_status !== 'verified') {
                $worker->update([
                    'verified_badge_status'     => 'verified',
                    'verified_badge_updated_at' => now(),
                ]);

                // After badge, auto-check if ready_to_work can be set
                $this->evaluateReadyToWork($worker->fresh());
            }
        } else {
            // Revoke verification if requirements are no longer met (e.g. document rejected)
            if ($worker->verified_badge_status === 'verified') {
                $worker->update([
                    'verified_badge_status'     => 'unverified',
                    'verified_badge_updated_at' => now(),
                    'ready_to_work_status'      => 'not_ready',
                    'ready_to_work_updated_at'  => now(),
                ]);
            }
        }
    }

    private function evaluateEmployerBadge(User $employer): void
    {
        // Employer verified badge = their employer documents all approved
        $pendingDocs = $employer->documents()->where('status', 'pending')->count();
        $approvedDocs = $employer->documents()->where('status', 'approved')->count();

        if ($approvedDocs > 0 && $pendingDocs === 0 && $employer->verified_badge_status !== 'verified') {
            $employer->update([
                'verified_badge_status'     => 'verified',
                'verified_badge_updated_at' => now(),
            ]);
        }
    }

    /**
     * Evaluate and update ready_to_work_status.
     * Logic differs by worker_type (from PDF matrix + ALUR.jpg).
     */
    public function evaluateReadyToWork(User $user): void
    {
        if (! $user->isWorker()) {
            // Employers: ready_to_work is set by admin directly
            return;
        }

        if ($user->verified_badge_status !== 'verified') {
            // Must have verified badge first
            if ($user->ready_to_work_status === 'ready' || $user->employer_self_check_required) {
                $user->update([
                    'ready_to_work_status'         => 'not_ready',
                    'ready_to_work_updated_at'     => now(),
                    'employer_self_check_required' => false,
                ]);
            }
            return;
        }

        $workerType = $user->worker_type;

        // Auto-ready types (no extra documents needed beyond selfie/badge)
        // 'other' is also included — same as arc_other in generateDocumentChecklist()
        $autoReadyTypes = ['arc_other', 'aprc', 'taiwanese', 'other', 'gold_card', 'spouse_roc'];
        if (in_array($workerType, $autoReadyTypes)) {
            if ($user->ready_to_work_status !== 'ready' || $user->employer_self_check_required) {
                $user->update([
                    'ready_to_work_status'         => 'ready',
                    'ready_to_work_updated_at'     => now(),
                    'employer_self_check_required' => false,
                ]);
            }
            return;
        }

        // Not eligible type
        if ($workerType === 'not_sure') {
            if ($user->ready_to_work_status === 'ready' || $user->employer_self_check_required) {
                $user->update([
                    'ready_to_work_status'         => 'not_ready',
                    'ready_to_work_updated_at'     => now(),
                    'employer_self_check_required' => false,
                ]);
            }
            return;
        }

        // Special case: blue_collar — either transfer_document OR work_contract OR contract_ending_proof is sufficient
        if ($workerType === 'blue_collar') {
            $docsQuery = $user->workerDocuments()
                ->whereHas('documentType', fn($q) =>
                    $q->whereIn('slug', ['transfer_document', 'work_contract', 'contract_ending_proof'])
                );

            $hasApprovedDoc = (clone $docsQuery)->where('review_status', 'approved')->exists();
            $hasPendingDoc  = (clone $docsQuery)->where('review_status', 'pending')->exists();

            if ($hasApprovedDoc) {
                $shouldReady = 'ready';
            } elseif ($hasPendingDoc) {
                $shouldReady = 'pending';
            } else {
                $shouldReady = 'not_ready';
            }

            if ($user->ready_to_work_status !== $shouldReady || $user->employer_self_check_required) {
                // If it's currently rejected, and should be not_ready, don't overwrite rejected with not_ready.
                // But if it's pending/ready, we must update.
                if ($shouldReady === 'not_ready' && $user->ready_to_work_status === 'rejected') {
                    // keep rejected
                } else {
                    $user->update([
                        'ready_to_work_status'         => $shouldReady,
                        'ready_to_work_updated_at'     => now(),
                        'employer_self_check_required' => false,
                    ]);
                }
            }
            return;
        }

        // Special case: white_collar NEVER gets ready_to_work automatically.
        // We just return here so we don't accidentally revoke an Admin's manual 'ready' approval.
        // (If the badge is lost, the block at the top already handles revoking 'ready')
        if ($workerType === 'white_collar') {
            return;
        }

        // Types requiring extra documents (student)
        $requiredDocSlugs = match ($workerType) {
            'student'      => ['student_work_permit'],
            default        => [],
        };

        if (empty($requiredDocSlugs)) {
            return;
        }

        // Check if all required docs are approved
        $approvedCount = $user->workerDocuments()
            ->whereHas('documentType', fn($q) => $q->whereIn('slug', $requiredDocSlugs))
            ->where('review_status', 'approved')
            ->count();

        $pendingCount = $user->workerDocuments()
            ->whereHas('documentType', fn($q) => $q->whereIn('slug', $requiredDocSlugs))
            ->where('review_status', 'pending')
            ->count();

        $allApproved = $approvedCount >= count($requiredDocSlugs);

        if ($allApproved) {
            $shouldReady = 'ready';
        } elseif ($pendingCount > 0) {
            $shouldReady = 'pending';
        } else {
            $shouldReady = 'not_ready';
        }

        if ($user->ready_to_work_status !== $shouldReady || $user->employer_self_check_required) {
            if ($shouldReady === 'not_ready' && $user->ready_to_work_status === 'rejected') {
                // keep rejected
            } else {
                $user->update([
                    'ready_to_work_status'         => $shouldReady,
                    'ready_to_work_updated_at'     => now(),
                    'employer_self_check_required' => false,
                ]);
            }
        }
    }

    /**
     * Called when admin approves a worker's selfie.
     * Triggers badge + ready-to-work evaluation.
     */
    public function onSelfieApproved(User $worker): void
    {
        $worker->update(['selfie_verified_at' => now()]);

        $docType = DocumentType::where('slug', 'selfie')->first();
        if ($docType) {
            $document = WorkerDocument::updateOrCreate(
                ['user_id' => $worker->id, 'document_type_id' => $docType->id],
                [
                    'file_url'          => $worker->selfie_file_url,
                    'original_filename' => 'selfie.jpg',
                    'review_status'     => 'approved',
                    'reviewed_by'       => auth()->id(),
                    'reviewed_at'       => now(),
                ]
            );

            WorkerDocumentRequirement::updateOrCreate(
                ['user_id' => $worker->id, 'document_type_id' => $docType->id],
                [
                    'upload_status'      => 'verified',
                    'worker_document_id' => $document->id,
                ]
            );
        }

        $this->evaluateVerifiedBadge($worker->fresh());

        $this->logVerification($worker, 'worker', 'approved', 'Selfie approved');
    }

    /**
     * Called when admin approves a worker document.
     * Triggers ready-to-work re-evaluation.
     */
    public function onDocumentApproved(WorkerDocument $document): void
    {
        $worker = $document->user;

        // Update the requirement record
        WorkerDocumentRequirement::where('user_id', $worker->id)
            ->where('document_type_id', $document->document_type_id)
            ->update([
                'upload_status'      => 'verified',
                'worker_document_id' => $document->id,
            ]);

        // Special case: open_work_permit approval removes sponsorship requirement
        $docSlug = $document->documentType?->slug;
        if ($docSlug === 'open_work_permit') {
            $worker->update([
                'open_work_right_status' => 'approved',
                'sponsorship_required'   => false,
            ]);
            return; // no need to re-evaluate ready_to_work for this doc type
        }

        // Re-evaluate Verified Badge first (in case it was personal_id)
        $this->evaluateVerifiedBadge($worker->fresh());

        $this->evaluateReadyToWork($worker->fresh());
    }

    /**
     * Called when admin rejects a worker document.
     */
    public function onDocumentRejected(WorkerDocument $document): void
    {
        WorkerDocumentRequirement::where('user_id', $document->user_id)
            ->where('document_type_id', $document->document_type_id)
            ->update(['upload_status' => 'rejected']);

        $worker = $document->user;

        // Special case: open_work_permit rejection resets open_work_right_status
        $docSlug = $document->documentType?->slug;
        if ($docSlug === 'open_work_permit') {
            $worker->update(['open_work_right_status' => 'rejected']);
            return;
        }

        // Re-evaluate Verified Badge first (in case it was personal_id)
        $this->evaluateVerifiedBadge($worker->fresh());

        // Re-evaluate Ready to Work status
        $this->evaluateReadyToWork($worker->fresh());
    }

    /**
     * Write an entry to verification_logs.
     */
    public function logVerification(mixed $entity, string $entityType, string $action, ?string $notes = null): void
    {
        $adminId = auth()->id();
        if (! $adminId) {
            return;
        }

        VerificationLog::create([
            'entity_type' => $entityType,
            'entity_id'   => $entity->id,
            'action'      => $action,
            'notes'       => $notes,
            'verified_by' => $adminId,
            'verified_at' => now(),
        ]);
    }

    /**
     * Get the document checklist status for a worker (for API response).
     */
    public function getChecklistStatus(User $worker): array
    {
        $requirements = $worker->documentRequirements()
            ->with(['documentType', 'workerDocument'])
            ->get();

        $totalRequired = $requirements->count();
        $uploaded      = $requirements->whereIn('upload_status', ['uploaded', 'verified'])->count();
        $verified      = $requirements->where('upload_status', 'verified')->count();
        $rejected      = $requirements->where('upload_status', 'rejected')->count();
        $notUploaded   = $requirements->where('upload_status', 'not_uploaded')->count();

        return [
            'total_required' => $totalRequired,
            'uploaded'       => $uploaded,
            'verified'       => $verified,
            'rejected'       => $rejected,
            'not_uploaded'   => $notUploaded,
            'requirements'   => $requirements->map->toApiArray()->values(),
        ];
    }
}
