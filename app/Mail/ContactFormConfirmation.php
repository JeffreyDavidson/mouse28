<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "We got your message! — Mouse28",
            replyTo: [config('mail.admin_address', 'mouse28podcast@gmail.com')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-confirmation',
        );
    }
}
