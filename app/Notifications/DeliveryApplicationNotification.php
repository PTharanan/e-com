<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class DeliveryApplicationNotification extends Notification
{
    use Queueable;

    protected $deliveryBoy;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $deliveryBoy)
    {
        $this->deliveryBoy = $deliveryBoy;
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'delivery_boy_id' => $this->deliveryBoy->id,
            'delivery_boy_name' => $this->deliveryBoy->name,
            'message' => $this->deliveryBoy->name . ' has applied to join your delivery network.',
        ];
    }
}
