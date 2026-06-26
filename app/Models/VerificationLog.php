<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function toApiArray(): array
    {
        return [
            'id'          => $this->id,
            'entity_type' => $this->entity_type,
            'entity_id'   => $this->entity_id,
            'action'      => $this->action,
            'notes'       => $this->notes,
            'verified_by' => $this->verifiedBy?->full_name ?? $this->verifiedBy?->name,
            'verified_at' => $this->verified_at?->toIso8601String(),
        ];
    }
}
