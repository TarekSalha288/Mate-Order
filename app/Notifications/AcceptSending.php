<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Services\FCMService;
use Illuminate\Support\Facades\Log;

class AcceptSending extends Notification implements ShouldQueue
{
    use Queueable;

    private $order_id;
    private $store_name;

    /**
     * Create a new notification instance.
     */
    public function __construct($order_id, $store_name)
    {
        // Correctly assign values to the properties
        $this->order_id = $order_id;
        $this->store_name = $store_name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'fcm']; // The 'fcm' channel here is custom and handled manually
    }

    /**
     * Handle FCM notification delivery.
     *
     * @param object $notifiable
     * @return void
     */
    public function toFcm(object $notifiable)
    {
        $fcmToken = $notifiable->routeNotificationForFcm(); // Ensure this method exists in your User model
        $fcmService = new FCMService();

        // Send the FCM notification
        $response = $fcmService->sendNotification(
            $fcmToken,
            'Mate Order App',
            "We accept sending your order of Id: {$this->order_id} from store: {$this->store_name}",
            ['order_id' => $this->order_id]
        );

        // Log or handle the response for debugging
        if (!$response['success'] ?? false) {
            Log::error('FCM Notification Failed', ['response' => $response]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "We accept sending your order of Id: ".$this->order_id." from store: ".$this->store_name
        ];
    }
}
