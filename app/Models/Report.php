<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_id',
        'job_id',
        'chat_message_id',
        'report_type',
        'reason',
        'severity',
        'description',
        'evidence_url',
        'status',
        'admin_note',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reported()
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function chatMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function toApiArray(): array
    {
        return [
            'id'              => (string) $this->id,
            'reporter_id'     => (string) $this->reporter_id,
            'reported_id'     => $this->reported_id ? (string) $this->reported_id : null,
            'job_id'          => $this->job_id ? (string) $this->job_id : null,
            'chat_message_id' => $this->chat_message_id ? (string) $this->chat_message_id : null,
            'report_type'     => $this->report_type,
            'reason'          => $this->reason,
            'severity'        => $this->severity ?? 'medium',
            'description'     => $this->description,
            'evidence_url'    => $this->evidence_url,
            'status'          => $this->status,
            'admin_note'      => $this->admin_note,
            'resolved_at'     => $this->resolved_at?->toIso8601String(),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
