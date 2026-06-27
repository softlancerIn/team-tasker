<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'subject',
        'body',
        'status',
        'priority',
        'user_id',
        'assigned_to',
        'email_source',
        'attachments',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($ticket) {
            $ticket->ticket_number = 'TKT-'.str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
            $ticket->save();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
