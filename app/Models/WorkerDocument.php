<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document_type_id',
        'file_url',
        'original_filename',
        'review_status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'expiry_date' => 'date',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function toApiArray(): array
    {
        return [
            'id'                  => $this->id,
            'document_type_id'    => $this->document_type_id,
            'document_type_name'  => $this->documentType?->document_type_name,
            'document_type_slug'  => $this->documentType?->slug,
            'file_url'            => $this->file_url,
            'original_filename'   => $this->original_filename,
            'review_status'       => $this->review_status,
            'review_note'         => $this->review_note,
            'reviewed_at'         => $this->reviewed_at?->toIso8601String(),
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}
