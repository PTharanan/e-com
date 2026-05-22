<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnAssignedNotification extends Notification
{
    use Queueable;

    private $orderReturn;

    public function __construct($orderReturn)
    {
        $this->orderReturn = $orderReturn;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'return_id' => $this->orderReturn->id,
            'order_id' => $this->orderReturn->order_id,
            'customer_name' => $this->orderReturn->user->name,
            'reason' => $this->orderReturn->reason,
            'type' => 'return_assignment'
        ];
    }
}
