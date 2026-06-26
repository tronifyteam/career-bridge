<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerType extends Model
{
    protected $fillable = [
        'worker_type_name',
        'slug',
        'description',
        'requires_arc',
        'auto_ready_to_work',
        'eligible_to_work',
    ];

    protected function casts(): array
    {
        return [
            'requires_arc'       => 'boolean',
            'auto_ready_to_work' => 'boolean',
            'eligible_to_work'   => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'worker_type_id');
    }

    public function documentTypes(): HasMany
    {
        return $this->hasMany(DocumentType::class, 'worker_type_id');
    }

    public function toApiArray(): array
    {
        return [
            'id'                  => $this->id,
            'worker_type_name'    => $this->worker_type_name,
            'slug'                => $this->slug,
            'description'         => $this->description,
            'requires_arc'        => $this->requires_arc,
            'auto_ready_to_work'  => $this->auto_ready_to_work,
            'eligible_to_work'    => $this->eligible_to_work,
        ];
    }
}
