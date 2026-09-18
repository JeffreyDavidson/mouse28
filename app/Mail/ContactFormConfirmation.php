<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ContactFormConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

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
        return new Headers(text: [
            'Resend-Idempotency-Key' => 'mouse28-contact-'.hash('sha256', Config::string('app.url').'|'.$this->contactMessage->id.'|'.$this->contactMessage->created_at?->toISOString()).'-confirmation',
        ]);
    }
}
