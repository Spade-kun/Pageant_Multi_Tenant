<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlanRequestStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $status;
    public $reason;

    public function __construct(Tenant $tenant, $status, $reason = null)
    {
        $this->tenant = $tenant;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function build()
    {
        $subject = match($this->status) {
            'approved' => 'Your Plan Request Has Been Approved',
            'rejected' => 'Your Plan Request Has Been Rejected',
            'updated' => 'Your Subscription Plan Has Been Updated',
            default => 'Plan Request Status Update'
        };

        return $this->subject($subject)
            ->view('emails.plan-request-status')
            ->with([
                'tenant' => $this->tenant,
                'status' => $this->status,
                'reason' => $this->reason
            ]);
    }
} 