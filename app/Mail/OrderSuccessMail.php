<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $products;
    public $total;
    public $storeName;

    /**
     * Create a new message instance.
     */
    public function __construct($products, $total)
    {
        $this->products = $products;
        $this->total = $total;

        $sellerIds = [];
        if (is_array($products)) {
            foreach ($products as $item) {
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
            subject: 'Order Successful - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order_success',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
