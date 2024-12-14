<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectSending extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $order_id;
    private $store_name;
    public function __construct($order_id, $store_name)
    {
        $order_id = $this->order_id;
        $store_name = $this->store_name;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'fcm'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toFcm($notifiable)
    {
        return [
            'to' => $notifiable->routeNotificationForFcm(),
            'notification' => [
                'title' => 'Mate Order App',
                'body' => "we reject sending your order of Id: $this->order_id from store:$this->store_name"
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
            'message' => "we reject sending your order of Id: $this->order_id from store:$this->store_name"
        ];
    }
}
