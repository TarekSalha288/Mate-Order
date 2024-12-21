<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderSendingToSuperUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $product_id;
    private $order_id;
    private $user_name;
    public function __construct($user_name, $product_id, $order_id)
    {
        $this->user_name = $user_name;
        $this->product_id = $product_id;
        $this->order_id = $order_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }
    public function toArray(object $notifiable): array
    {
        return [
            // 'message' => "you have new order from user: $this->user_name and his order id: $this->order_id from product id: $this->product_id",
            'user_name' => $this->user_name,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id
        ];
    }
}
