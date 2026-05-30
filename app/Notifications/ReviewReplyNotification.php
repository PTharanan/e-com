<?php

namespace App\Notifications;

use App\Models\ProductReview;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewReplyNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(private ProductReview $review)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seller Reply to Your Product Review')
            ->greeting("Hello {$notifiable->name}!")
            ->line("The seller has replied to your review for the product: **{$this->review->product->name}**")
            ->line('**Their Reply:**')
            ->line($this->review->reply)
            ->action('View Full Review', route('product.show', $this->review->product->id))
            ->line('Thank you for your feedback!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_reply',
            'review_id' => $this->review->id,
            'product_id' => $this->review->product->id,
            'product_name' => $this->review->product->name,
            'reply_text' => $this->review->reply,
            'replied_by' => $this->review->repliedByUser->name ?? 'Seller',
        ];
    }
}
