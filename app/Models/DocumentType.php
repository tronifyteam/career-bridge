<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $fillable = [
        'document_type_name',
        'slug',
        'description',
        'worker_type_id',
        'required_for_verified_badge',
        'required_for_ready_to_work',
        'verification_required',
    ];

    protected function casts(): array
    {
        return [
            'required_for_verified_badge' => 'boolean',
            'required_for_ready_to_work'  => 'boolean',
            'verification_required'       => 'boolean',
        ];
    }

    public function workerType(): BelongsTo
    {
        return $this->belongsTo(WorkerType::class);
    }

    public function workerDocuments(): HasMany
    {
        return $this->hasMany(WorkerDocument::class);
    }

    public function toApiArray(): array
    {
        return [
            'id'                          => $this->id,
            'document_type_name'          => $this->document_type_name,
            'slug'                        => $this->slug,
            'description'                 => $this->description,
            'worker_type_id'              => $this->worker_type_id,
            'required_for_verified_badge' => $this->required_for_verified_badge,
            'required_for_ready_to_work'  => $this->required_for_ready_to_work,
            'verification_required'       => $this->verification_required,
        ];
    }
}
