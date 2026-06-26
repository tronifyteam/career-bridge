<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'translated_message',
        'translated_language',
        'type',
        'attachment_url',
        'attachment_name',
        'attachment_size',
        'is_read',
        'detected_language',
        'cv_data',
        'job_id',
        'application_id',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    // 🤝 Relationships 🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝🤝

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    public function translationLogs(): HasMany
    {
        return $this->hasMany(TranslationLog::class, 'chat_message_id')->orderBy('created_at');
    }

    // 🌐 API Serialization 🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐🌐

    public function toApiArray(): array
    {
        return [
            'id' => (string) $this->id,
            'sender_id' => (string) $this->sender_id,
            'receiver_id' => (string) $this->receiver_id,
            'message' => $this->message,
            'translated_message' => $this->translated_message,
            'translated_language' => $this->translated_language,
            'type' => $this->type,
            'attachment_url' => $this->attachment_url,
            'attachment_name' => $this->attachment_name,
            'attachment_size' => $this->attachment_size,
            'cv_data' => $this->cv_data ? json_decode($this->cv_data, true) : null,
            'job_id'         => $this->job_id ? (string) $this->job_id : null,
            'application_id' => $this->application_id ? (string) $this->application_id : null,
            'is_read' => $this->is_read,
            'detected_language' => $this->detected_language,
            'created_at' => $this->created_at->toIso8601String(),
            'sender' => $this->sender ? [
                'id' => (string) $this->sender->id,
                'full_name' => $this->sender->full_name ?? $this->sender->name,
                'avatar_url' => $this->sender->avatar_url,
            ] : null,
            // Include translation history if already loaded
            'translation_logs' => $this->relationLoaded('translationLogs')
                ? $this->translationLogs->map->toApiArray()->values()
                : [],
        ];
    }
}
