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
        return ['database']; // Adding custom FCM channel
    }



    public function toArray($notifiable): array
    {
        return [
            'message' => "We accept sending your order of Id: {$this->order_id}",
        ];
    }
}
