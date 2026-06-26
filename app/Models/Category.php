<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
    ];

    public function jobs()
    {
        return Job::where('category', $this->name);
    }
}
