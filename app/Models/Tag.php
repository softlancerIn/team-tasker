<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'color'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }
}
