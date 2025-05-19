<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantUserRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $tempPassword;
    public $tenantName;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $tempPassword, $tenantName)
    {
        $this->name = $name;
        $this->email = $email;
        $this->tempPassword = $tempPassword;
        $this->tenantName = $tenantName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to {$this->tenantName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-user-registration',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'tempPassword' => $this->tempPassword,
                'tenantName' => $this->tenantName,
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