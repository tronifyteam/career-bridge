<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_type',
        'chat_translation_quota',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── API Serialization ─────────────────────────────────

    public function toApiArray(): array
    {
        return [
            'id' => (string) $this->id,
            'user_id' => (string) $this->user_id,
            'plan_type' => $this->plan_type,
            'chat_translation_quota' => $this->chat_translation_quota,
            'starts_at' => $this->starts_at->toIso8601String(),
            'expires_at' => $this->expires_at ? $this->expires_at->toIso8601String() : null,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
