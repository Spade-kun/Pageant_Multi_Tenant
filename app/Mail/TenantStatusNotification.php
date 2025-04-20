<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $status;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, string $status, ?string $rejectionReason = null)
    {
        $this->tenant = $tenant;
        $this->status = $status;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved' 
            ? 'Your Pageant Registration Has Been Approved' 
            : 'Your Pageant Registration Has Been Rejected';
            
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-status-notification',
            with: [
                'tenant' => $this->tenant,
                'status' => $this->status,
                'rejectionReason' => $this->rejectionReason,
            ],
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