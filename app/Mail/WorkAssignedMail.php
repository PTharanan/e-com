<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class WorkAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $storeName;

    public function __construct(Order $order)
    {
        $this->order = $order;

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

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Delivery Assigned - ' . $this->storeName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.work_assigned',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
