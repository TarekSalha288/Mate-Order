<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Services\FCMService;
use Illuminate\Support\Facades\Log;

class RejectSending extends Notification implements ShouldQueue
{
    use Queueable;

    private $order_id;


    /**
     * Create a new notification instance.
     */
    public function __construct($order_id)
    {
        // Correctly assign values to the properties
        $this->order_id = $order_id;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // The 'fcm' channel here is custom and handled manually
    }

    /**
     * Handle FCM notification delivery.
     *
     * @param object $notifiable
     * @return void
     */


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "We Reject sending your order of Id: ".$this->order_id
        ];
    }
}
