<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobType extends Model
{
    protected $fillable = ['job_type_name', 'slug', 'description'];

    public function workerJobTypes(): HasMany
    {
        return $this->hasMany(WorkerJobType::class);
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(Job::class, 'job_type_id');
    }

    public function toApiArray(): array
    {
        return [
            'id'            => $this->id,
            'job_type_name' => $this->job_type_name,
            'slug'          => $this->slug,
            'description'   => $this->description,
        ];
    }
}
