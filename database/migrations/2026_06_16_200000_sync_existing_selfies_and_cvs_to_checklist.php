<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\DocumentType;
use App\Models\WorkerDocument;
use App\Models\WorkerDocumentRequirement;

return new class extends Migration
{
    public function up(): void
    {
        $selfieType = DocumentType::where('slug', 'selfie')->first();
        $cvType     = DocumentType::where('slug', 'cv')->first();

        // Process all workers
        $workers = User::where('role', 'worker')->get();

        foreach ($workers as $worker) {
            // Fix Selfie checklist mapping if a selfie is present
            if ($selfieType && $worker->selfie_file_url) {
                $uploadStatus = $worker->selfie_verified_at !== null ? 'verified' : 'uploaded';

                $document = WorkerDocument::updateOrCreate(
                    ['user_id' => $worker->id, 'document_type_id' => $selfieType->id],
                    [
                        'file_url'          => $worker->selfie_file_url,
                        'original_filename' => 'selfie.jpg',
                        'review_status'     => $worker->selfie_verified_at !== null ? 'approved' : 'pending',
                    ]
                );

                WorkerDocumentRequirement::updateOrCreate(
                    ['user_id' => $worker->id, 'document_type_id' => $selfieType->id],
                    [
                        'upload_status'      => $uploadStatus,
                        'worker_document_id' => $document->id,
                    ]
                );
            }

            // Fix CV checklist mapping if a CV is present
            if ($cvType && $worker->cv_url) {
                $document = WorkerDocument::updateOrCreate(
                    ['user_id' => $worker->id, 'document_type_id' => $cvType->id],
                    [
                        'file_url'          => $worker->cv_url,
                        'original_filename' => 'cv.pdf',
                        'review_status'     => 'approved',
                    ]
                );

                WorkerDocumentRequirement::updateOrCreate(
                    ['user_id' => $worker->id, 'document_type_id' => $cvType->id],
                    [
                        'upload_status'      => 'verified',
                        'worker_document_id' => $document->id,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // No down migration logic needed for data sync
    }
};
