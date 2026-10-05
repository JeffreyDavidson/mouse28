<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactInquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\Config;

/**
 * Confirms a contact inquiry to its sender. Sent by SendContactInquiryEmails, which
 * records the send so retries never deliver it twice. The recipient address is
 * visitor-supplied, so the subject and body are fixed and never echo the inquiry
 * (it is not exposed to the view); otherwise anyone could use the form to send their
 * own text, from this domain, to any address.
 */
class ContactMessageConfirmation extends Mailable
{
    public function __construct(protected readonly ContactInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: array_values(array_filter(array_map(trim(...), explode(',', Config::string('mail.admin_address'))))),
            subject: 'We got your message! — Mouse28',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-confirmation',
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
            'Resend-Idempotency-Key' => "mouse28-contact-{$fingerprint}-confirmation",
        ]);
    }
}
