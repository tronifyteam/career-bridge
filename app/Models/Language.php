<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = ['language_code', 'language_name'];

    public function workerLanguages(): HasMany
    {
        return $this->hasMany(WorkerLanguage::class);
    }

    public function toApiArray(): array
    {
        return [
            'id'            => $this->id,
            'language_code' => $this->language_code,
            'language_name' => $this->language_name,
        ];
    }
}
