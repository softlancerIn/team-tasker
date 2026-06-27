<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'company', 
        'profile_image', 'fcm_token', 'is_approved', 'status', 
        'otp', 'otp_expires_at', 'email_verified_at'
    ];

    protected $hidden = [
        'password', 'remember_token', 'otp'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'is_approved' => 'boolean',
        'password' => 'hashed',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
    
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants', 'client_id', 'conversation_id')
            ->withPivot('last_read_at', 'user_id')
            ->withTimestamps();
    }

    public function unreadChatMessagesCount()
    {
        $clientId = $this->id;

        return \Illuminate\Support\Facades\DB::table('messages')
            ->join('conversation_participants', function ($join) use ($clientId) {
                $join->on('messages.conversation_id', '=', 'conversation_participants.conversation_id')
                    ->where('conversation_participants.client_id', '=', $clientId);
            })
            ->where(function ($query) use ($clientId) {
                $query->where('messages.client_id', '!=', $clientId)
                      ->orWhereNull('messages.client_id');
            })
            ->where(function ($query) {
                $query->whereNull('conversation_participants.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
            })
            ->count();
    }
}