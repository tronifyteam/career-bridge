<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerLanguage extends Model
{
    protected $fillable = ['user_id', 'language_id', 'proficiency_level'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function toApiArray(): array
    {
        return [
            'id'               => $this->id,
            'language_id'      => $this->language_id,
            'language_code'    => $this->language?->language_code,
            'language_name'    => $this->language?->language_name,
            'proficiency_level'=> $this->proficiency_level,
        ];
    }
}
