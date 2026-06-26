<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerStaff extends Model
{
    protected $fillable = [
        'user_id',
        'agency_employer_id',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_employer_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function toApiArray(): array
    {
        return [
            'id'                 => $this->id,
            'user_id'            => $this->user_id,
            'staff_name'         => $this->user?->full_name ?? $this->user?->name,
            'staff_email'        => $this->user?->email,
            'agency_employer_id' => $this->agency_employer_id,
            'agency_name'        => $this->agency?->company_name ?? $this->agency?->full_name,
            'status'             => $this->status,
            'approved_at'        => $this->approved_at?->toIso8601String(),
            'created_at'         => $this->created_at->toIso8601String(),
        ];
    }
}
