<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'structure',
        'is_active',
    ];

    protected $casts = [
        'structure' => 'array',
        'is_active' => 'boolean',
    ];
}
