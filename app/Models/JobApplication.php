<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplication extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $fillable = [
        'job_id',
        'user_id',
        'status',
        'cover_letter',
        'employer_notes',
        'status_snapshot_id',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusSnapshot(): BelongsTo
    {
        return $this->belongsTo(ApplicationStatusHistory::class, 'status_snapshot_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ApplicationStatusLog::class, 'application_id')->orderBy('changed_at');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->whereIn('status', ['accepted', 'hired', 'approved']);
    }

    // ── API Serialization ─────────────────────────────────

    public function toApiArray(): array
    {
        $worker = $this->user;
        $job    = $this->job;

        return [
            'id'             => (string) $this->id,
            'job_id'         => (string) $this->job_id,
            'job_title'      => $job?->title,
            'employer_id'    => (string) $job?->employer_id,
            'employer_name'  => $job?->employer_name,
            'employer_avatar'=> $job?->employer?->avatar_url,
            'location'       => $job?->location,
            'status'         => $this->status,
            'cover_letter'   => $this->cover_letter,
            'employer_notes' => $this->employer_notes,
            'applied_at'     => $this->applied_at?->toIso8601String() ?? $this->created_at->toIso8601String(),
            // Worker info (for employer view)
            'worker' => $worker ? [
                'id'                     => (string) $worker->id,
                'full_name'              => $worker->full_name ?? $worker->name,
                'nationality'            => $worker->nationality,
                'current_city'           => $worker->current_city,
                'avatar_url'             => $worker->avatar_url,
                'worker_type'            => $worker->worker_type,
                'verified_badge_status'  => $worker->verified_badge_status,
                'ready_to_work_status'   => $worker->ready_to_work_status,
                'sponsorship_required'   => (bool) $worker->sponsorship_required,
                'cv_url'                 => $worker->cv_url, // Always visible if they applied
                // Extended profile fields
                'gender'                 => $worker->gender,
                'available_date'         => $worker->available_date,
                'expected_salary'        => $worker->expected_salary,
                'skills'                 => $worker->skills ?? [],
                'language_abilities'     => $worker->language_abilities ?? [],
                'current_work_status'    => $worker->current_work_status,
                'employer_self_check_required' => (bool) $worker->employer_self_check_required,
            ] : null,
            // Status snapshot at time of application
            'status_snapshot' => $this->statusSnapshot?->toApiArray(),
            'status_logs'     => $this->statusLogs->map->toApiArray()->values(),
        ];
    }
}
