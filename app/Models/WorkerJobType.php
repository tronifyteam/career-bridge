<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerJobType extends Model
{
    protected $fillable = ['user_id', 'job_type_id', 'years_of_experience'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function toApiArray(): array
    {
        return [
            'id'                  => $this->id,
            'job_type_id'         => $this->job_type_id,
            'job_type_name'       => $this->jobType?->job_type_name,
            'slug'                => $this->jobType?->slug,
            'years_of_experience' => $this->years_of_experience,
        ];
    }
}
