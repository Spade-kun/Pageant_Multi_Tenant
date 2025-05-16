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
    public $reason;
    public $temporaryPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, string $status, ?string $reason = null, ?string $temporaryPassword = null)
    {
        $this->tenant = $tenant;
        $this->status = $status;
        $this->reason = $reason;
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved' 
            ? 'Your Tenant Application Has Been Approved' 
            : 'Your Tenant Application Has Been Rejected';

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
            view: 'emails.tenant-status',
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

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->status === 'approved' 
            ? 'Your Tenant Application Has Been Approved' 
            : 'Your Tenant Application Has Been Rejected';

        return $this->markdown('emails.tenant-status')
            ->subject($subject)
            ->with([
                'tenant' => $this->tenant,
                'status' => $this->status,
                'reason' => $this->reason,
                'temporaryPassword' => $this->temporaryPassword,
            ]);
    }
} 