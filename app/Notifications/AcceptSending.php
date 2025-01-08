<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Krait\LaravelFirebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;

class AcceptSending extends Notification implements ShouldQueue
{
    use Queueable;

    private $order_id;

    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    public function via($notifiable): array
    {
        return ['database', 'fcm']; // Adding custom FCM channel
    }

    public function toFcm($notifiable)
    {
        $fcmToken = $notifiable->routeNotificationFor('fcm');

        if (!$fcmToken) {
            Log::warning("No FCM token found for user ID: {$notifiable->id}");
            return;
        }

        try {
            $response = Firebase::send([
                'token' => $fcmToken,
                'notification' => [
                    'title' => 'Mate Order App',
                    'body' => "We accept sending your order of Id: {$this->order_id}",
                ],
                'data' => [
                    'order_id' => $this->order_id,
                ],
            ]);
            Log::info('FCM Notification Sent', ['response' => $response]);
        } catch (\Exception $e) {
            Log::error('Failed to send FCM Notification', ['error' => $e->getMessage()]);
        }
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "We accept sending your order of Id: {$this->order_id}",
        ];
    }
}
