<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerDocumentRequirement extends Model
{
    protected $fillable = [
        'user_id',
        'document_type_id',
        'upload_status',
        'worker_document_id',
        'required_by_date',
    ];

    protected function casts(): array
    {
        return [
            'required_by_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function workerDocument(): BelongsTo
    {
        return $this->belongsTo(WorkerDocument::class);
    }

    public function toApiArray(): array
    {
        return [
            'id'                  => $this->id,
            'document_type_id'    => $this->document_type_id,
            'document_type_name'  => $this->documentType?->document_type_name,
            'document_type_slug'  => $this->documentType?->slug,
            'required_for_badge'  => $this->documentType?->required_for_verified_badge,
            'required_for_ready'  => $this->documentType?->required_for_ready_to_work,
            'upload_status'       => $this->upload_status,
            'required_by_date'    => $this->required_by_date?->format('Y-m-d'),
            'document'            => $this->workerDocument?->toApiArray(),
        ];
    }
}
