<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    protected $table = 'application_status_history';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'verified_badge_status',
        'ready_to_work_status',
        'employer_self_check_required',
        'worker_nationality',
        'worker_type_slug',
        'recorded_at',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'employer_self_check_required'=> 'boolean',
            'recorded_at'                 => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function toApiArray(): array
    {
        return [
            'id'                          => $this->id,
            'verified_badge_status'       => $this->verified_badge_status,
            'ready_to_work_status'        => $this->ready_to_work_status,
            'employer_self_check_required'=> $this->employer_self_check_required,
            'worker_nationality'          => $this->worker_nationality,
            'worker_type_slug'            => $this->worker_type_slug,
            'recorded_at'                 => $this->recorded_at?->toIso8601String(),
        ];
    }
}
