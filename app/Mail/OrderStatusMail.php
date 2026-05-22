<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $newStatus;
    public $customerName;
    public $orderId;
    public $statusMessage;
    public $storeName;
    public $pickupImage;
    public $deliveryImage;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $newStatus)
    {
        $this->order = $order;
        $this->newStatus = ucfirst($newStatus);
        $this->customerName = $order->user->name;
        $this->orderId = '#ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

        $delivery = $order->delivery;
        $this->pickupImage = $delivery?->pickup_image ? url($delivery->pickup_image) : null;
        $this->deliveryImage = $delivery?->delivery_image ? url($delivery->delivery_image) : null;
        $secretCode = $delivery?->secret_code;

        // Custom message per status
        $messages = [
            'processing' => 'Your order is currently being processed. We will notify you once it has been shipped.',
            'completed'  => 'Your order has been completed and is ready for shipment.',
            'shipped'    => 'Great news! Your order has been shipped and is on its way to you.' . ($secretCode ? ' Your delivery confirmation code is **' . $secretCode . '**. Please provide this code to the delivery partner upon arrival.' : ''),
            'delivered'  => 'Your order has been delivered successfully. We hope you enjoy your purchase!',
            'cancelled'  => 'Your order has been cancelled. If you did not request this, please contact our support team.',
            'refunded'   => 'Your order has been successfully refunded. The amount has been returned to your original payment method.',
        ];

        $this->statusMessage = $messages[$newStatus] ?? 'Your order status has been updated.';

        // Determine Store Name based on seller_id in items
        $sellerIds = [];
        if (is_array($order->items_json)) {
            foreach ($order->items_json as $item) {
                if (!empty($item['seller_id'])) {
                    $sellerIds[] = $item['seller_id'];
                }
            }
        }
        
        $sellerIds = array_unique($sellerIds);
        if (count($sellerIds) > 0) {
            $seller = \App\Models\User::find($sellerIds[0]);
            $this->storeName = $seller ? $seller->name : 'E-Shop';
        } else {
            $this->storeName = 'E-Shop';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order ' . $this->orderId . ' — Status Updated to ' . $this->newStatus,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order_status',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        $delivery = $this->order->delivery;
        
        if ($this->newStatus === 'Shipped' && $delivery?->pickup_image) {
            $path = public_path($delivery->pickup_image);
            if (file_exists($path)) {
                $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                    ->as('pickup_confirmation.jpg')
                    ->withMime('image/jpeg');
            }
        }

        if ($this->newStatus === 'Delivered' && $delivery?->delivery_image) {
            $path = public_path($delivery->delivery_image);
            if (file_exists($path)) {
                $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                    ->as('delivery_confirmation.jpg')
                    ->withMime('image/jpeg');
            }
        }

        return $attachments;
    }
}
