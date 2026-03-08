<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $serverKey;
    protected $url = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key') ?: env('FCM_SERVER_KEY');
    }

    /**
     * Send a push notification to a specific FCM token.
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $extraData
     * @return bool
     */
    public function sendNotification($token, $title, $body, array $extraData = [])
    {
        if (!$this->serverKey) {
            Log::error('FirebaseService: FCM_SERVER_KEY is not configured.');
            return false;
        }

        if (!$token) {
            return false;
        }

        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => array_merge([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ], $extraData),
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->url, $payload);

            if ($response->successful()) {
                Log::info('FirebaseService Success: ' . $response->body());
                return true;
            }

            $errorMessage = $response->body();
            if ($response->status() == 404) {
                $errorMessage = '404 Not Found. This usually means the FCM Legacy API is disabled in the Firebase Console or the URL is incorrect for this project. Google has deprecated Legacy FCM; consider migrating to HTTP v1.';
            } elseif ($response->status() == 401) {
                $errorMessage = '401 Unauthorized. The FCM_SERVER_KEY is invalid.';
            }

            Log::error('FirebaseService Error (Status ' . $response->status() . '): ' . $errorMessage);
            return false;
        } catch (\Exception $e) {
            Log::error('FirebaseService Exception: ' . $e->getMessage());
            return false;
        }
    }
}
