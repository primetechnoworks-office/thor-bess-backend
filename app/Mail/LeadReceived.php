<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notifies the sales team of a new website lead (reply-to = the visitor). */
class LeadReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        $who = $this->lead->name.($this->lead->company ? ', '.$this->lead->company : '');

        $subject = match ($this->lead->type) {
            Lead::TYPE_DATASHEET => "Datasheet request: {$this->lead->subject} — {$who}",
            Lead::TYPE_QUOTE => "e-Generator enquiry: {$this->lead->subject} — {$who}",
            default => "Website enquiry — {$who}",
        };

        return new Envelope(
            subject: $subject,
            replyTo: [new Address($this->lead->email, $this->lead->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lead-received',
            with: [
                'heading' => match ($this->lead->type) {
                    Lead::TYPE_DATASHEET => 'New datasheet request',
                    Lead::TYPE_QUOTE => 'New custom e-Generator enquiry',
                    default => 'New website enquiry',
                },
                'rows' => $this->lead->summaryRows(),
                'adminUrl' => route('admin.leads.show', $this->lead),
            ],
        );
    }

    public function attachments(): array
    {
        if (! $this->lead->attachment_path) {
            return [];
        }

        return [Attachment::fromStorageDisk('local', $this->lead->attachment_path)->as($this->lead->attachment_name)];
    }
}
