<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;


class FCMService
{
    protected $serverKey;

    public function __construct()
    {
        $this->serverKey = env('FCM_SERVER_KEY'); // Ensure this is set in your .env file
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            'data' => $data, // Optional data payload
        ];

        $response = Http::withToken($this->serverKey)
            ->post($url, $payload);

        return $response->json(); // Return response for debugging
    }
}
