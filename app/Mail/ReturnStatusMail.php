<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $return;
    public $statusMessage;
    public $statusLabel;
    public $storeName;

    /**
     * Create a new message instance.
     */
    public function __construct($return)
    {
        $this->return = $return;
        $this->statusLabel = ucfirst(str_replace('_', ' ', $return->status));
        
        $messages = [
            'accepted'  => 'Your return request has been accepted. A delivery partner has been assigned to pick up the item.',
            'rejected'  => 'Your return request has been rejected. Reason: ' . ($return->rejection_reason ?? 'No reason provided.'),
            'picked_up' => 'The item has been successfully picked up by our delivery partner and is on its way to the store.',
            'completed' => 'The return process is complete. The item has been returned to the store and we will process your refund shortly.',
        ];

        $this->statusMessage = $messages[$return->status] ?? 'The status of your return request has been updated.';
        $this->storeName = $return->storeOwner->role === 'admin' ? 'E-Shop' : $return->storeOwner->name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Return Status Update: ' . $this->statusLabel,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.return_status',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
