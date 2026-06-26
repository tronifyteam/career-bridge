<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_id',
        'violation_type',
        'description',
        'points_deducted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => (string) $this->id,
            'user_id' => (string) $this->user_id,
            'report_id' => $this->report_id ? (string) $this->report_id : null,
            'violation_type' => $this->violation_type,
            'description' => $this->description,
            'points_deducted' => $this->points_deducted,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
