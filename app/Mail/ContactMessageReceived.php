<?php

declare(strict_types=1);

namespace App\Mail;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Support\DisplayTimezone;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\Config;

/**
 * Notifies the administrators of a contact inquiry. Sent by SendContactInquiryEmails,
 * which records the send so retries never deliver it twice.
 */
class ContactMessageReceived extends Mailable
{
    public function __construct(public readonly ContactInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
            subject: "New Contact: {$this->inquiry->type->getLabel()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-submitted',
            with: [
                // Stored in UTC; the administrators read times in the site's display timezone.
                'receivedAt' => DisplayTimezone::convert($this->inquiry->created_at),
                'adminUrl' => ContactInquiryResource::getUrl('view', ['record' => $this->inquiry]),
            ],
        );
    }

    public function headers(): Headers
    {
        $fingerprint = hash('sha256', implode('|', [
            Config::string('app.url'),
            $this->inquiry->id,
            $this->inquiry->created_at?->toISOString(),
        ]));

        return new Headers(text: [
            'Resend-Idempotency-Key' => "mouse28-contact-{$fingerprint}-notification",
        ]);
    }
}
