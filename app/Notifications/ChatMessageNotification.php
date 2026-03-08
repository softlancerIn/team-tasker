<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ChatMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
        // Ensure user relationship is loaded for the sender's name
        if (!$this->message->relationLoaded('user')) {
            $this->message->load('user');
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // 1. Refresh the message to check latest is_read/delivered status
        $this->message->refresh();

        // 2. Check if the user has already read the message via real-time socket
        $participant = \Illuminate\Support\Facades\DB::table('conversation_participants')
            ->where('conversation_id', $this->message->conversation_id)
            ->where('user_id', $notifiable->id)
            ->first();

        if ($participant && $participant->last_read_at) {
            $lastRead = \Carbon\Carbon::parse($participant->last_read_at);
            $msgCreated = $this->message->created_at;
            
            \Illuminate\Support\Facades\Log::info('ChatMessageNotification: User ' . $notifiable->id . ' last_read_at: ' . $lastRead . ', Message created_at: ' . $msgCreated);

            if ($lastRead->addSeconds(5)->gte($msgCreated)) {
                \Illuminate\Support\Facades\Log::info('ChatMessageNotification: Suppressing notification because user recently viewed the chat (within 5s buffer).');
                return [];
            }
        }

        \Illuminate\Support\Facades\Log::info('ChatMessageNotification: Dispatching through FirebaseChannel.');
        return [Channels\FirebaseChannel::class];
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase(object $notifiable): array
    {
        $body = strip_tags($this->message->body);
        
        if (empty($body) && $this->message->attachments()->count() > 0) {
            $body = 'sent an attachment';
        }

        return [
            'title' => 'New Message from ' . ($this->message->user->name ?? 'User'),
            'body' => $body,
            'data' => [
                'conversation_id' => (string) $this->message->conversation_id,
                'action' => 'new_message',
            ],
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'message' => 'New message from ' . ($this->message->user->name ?? 'User'),
        ];
    }
}
