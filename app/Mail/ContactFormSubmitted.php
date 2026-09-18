<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->contactMessage->email],
            subject: 'New Contact: '.$this->contactMessage->subjectLabel(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-submitted',
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'Resend-Idempotency-Key' => 'mouse28-contact-'.hash('sha256', Config::string('app.url').'|'.$this->contactMessage->id.'|'.$this->contactMessage->created_at?->toISOString()).'-notification',
        ]);
    }
}
