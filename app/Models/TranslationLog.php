<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationLog extends Model
{
    public $timestamps = false; // only created_at managed manually

    protected $fillable = [
        'chat_message_id',
        'user_id',
        'original_text',
        'translated_text',
        'source_language',
        'target_language',
        'trigger_type',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── API Serialization ─────────────────────────────────────

    public function toApiArray(): array
    {
        return [
            'id'              => (string) $this->id,
            'chat_message_id' => (string) $this->chat_message_id,
            'user_id'         => (string) $this->user_id,
            'original_text'   => $this->original_text,
            'translated_text' => $this->translated_text,
            'source_language' => $this->source_language,
            'target_language' => $this->target_language,
            'trigger_type'    => $this->trigger_type,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
