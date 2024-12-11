<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectReceiving extends Notification
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
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => ' Sorry We Reject Your Order Of Id ' . $this->order_id . ' From Store ' . $this->store_name,
        ];
    }
}
