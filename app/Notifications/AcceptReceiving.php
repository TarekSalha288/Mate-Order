<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Services\FCMService;

class AcceptReceiving extends Notification implements ShouldQueue
{
    use Queueable;

    private $order_id;
    private $store_name;

    /**
     * Create a new notification instance.
     */
    public function __construct($order_id, $store_name)
    {
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
        return ['database', 'fcm']; // Custom FCM channel
    }

    /**
     * Handle the FCM notification delivery.
     *
     * @param object $notifiable
     * @return void
     */
    public function toFcm(object $notifiable)
    {
        $fcmToken = $notifiable->routeNotificationForFcm(); // Ensure this method exists and returns the FCM token
        $fcmService = new FCMService();

        // Send the FCM notification
        $response = $fcmService->sendNotification(
            $fcmToken,
            'Mate Order App',
            "Accept your order of Id: {$this->order_id} from store: {$this->store_name}",
            ['order_id' => $this->order_id]
        );

        // Optionally log the response to handle any failures
        if (!$response['success'] ?? false) {
            \Log::error('FCM Notification Failed', ['response' => $response]);
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
            'message' => "Accept your order of Id: {$this->order_id} from store: {$this->store_name}",
        ];
    }
}
