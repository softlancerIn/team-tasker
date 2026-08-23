<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'work_hours',
        'ip_address',
        'clock_in_location',
        'clock_out_location',
        'location',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
