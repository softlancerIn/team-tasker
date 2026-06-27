<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'body', 'attachment_path', 'attachment_type', 'attachment_original_name', 'delivered_at', 'read_at', 'deleted_at'];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['is_read'];

    public function getIsReadAttribute()
    {
        return ! is_null($this->read_at);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
