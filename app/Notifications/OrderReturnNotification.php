<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderReturnNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $reason;

    public function __construct($order, $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'customer_name' => $this->order->user->name ?? 'Guest',
            'reason' => $this->reason,
            'message' => 'New return request for Order #' . $this->order->id . '. Reason: ' . $this->reason
        ];
    }
}
