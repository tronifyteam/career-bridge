<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $fillable = [
        'user_id',
        'ad_package_id',
        'type',
        'job_id',
        'title',
        'image_url',
        'target_url',
        'status',
        'starts_at',
        'expires_at',
        'impressions_count',
        'clicks_count',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(AdPackage::class, 'ad_package_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
