<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryHiredNotification extends Notification
{
    use Queueable;

    protected $storeName;
    protected $deliveryFee;

    public function __construct(string $storeName, float $deliveryFee)
    {
        $this->storeName = $storeName;
        $this->deliveryFee = $deliveryFee;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'store_name' => $this->storeName,
            'delivery_fee' => $this->deliveryFee,
            'message' => "Congratulations! You have been hired by E-Shop. You will earn $" . number_format($this->deliveryFee, 2) . " per order delivered.",
        ];
    }
}
