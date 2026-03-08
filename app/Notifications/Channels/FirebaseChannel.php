<?php

namespace App\Notifications\Channels;

use App\Services\FirebaseService;
use Illuminate\Notifications\Notification;

class FirebaseChannel
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFirebase')) {
            \Illuminate\Support\Facades\Log::warning('FirebaseChannel: Notification ' . get_class($notification) . ' is missing toFirebase method.');
            return;
        }

        $message = $notification->toFirebase($notifiable);
        
        if (!$message) {
            return;
        }

        $fcmToken = $notifiable->fcm_token;

        if ($fcmToken) {
            \Illuminate\Support\Facades\Log::info('FirebaseChannel: Sending notification to token ' . substr($fcmToken, 0, 10) . '...');
            $this->firebaseService->sendNotification(
                $fcmToken,
                $message['title'],
                $message['body'],
                $message['data'] ?? []
            );
        } else {
            \Illuminate\Support\Facades\Log::warning('FirebaseChannel: User has no FCM token.');
        }
    }
}
