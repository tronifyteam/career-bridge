<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyCheck extends Model
{
    protected $fillable = [
        'user_id',
        'source_type',
        'source_id',
        'input_text',
        'image_url',
        'risk_level',
        'result_json',
        'language',
    ];

    protected function casts(): array
    {
        return [
            'result_json' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
