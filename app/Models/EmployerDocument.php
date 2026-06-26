<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_url',
        'status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── API Serialization ─────────────────────────────────

    public function toApiArray(): array
    {
        return [
            'id'            => (string) $this->id,
            'user_id'       => (string) $this->user_id,
            'document_type' => $this->document_type,
            'document_url'  => $this->document_url,
            'status'        => $this->status,
            'review_note'   => $this->review_note,
            'reviewed_by'   => $this->reviewed_by ? (string) $this->reviewed_by : null,
            'reviewed_at'   => $this->reviewed_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
