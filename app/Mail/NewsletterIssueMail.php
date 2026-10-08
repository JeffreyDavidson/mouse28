<?php

namespace App\Mail;

use App\Models\NewsletterIssue;
use App\Support\MarkdownRenderer;
use App\Support\Newsletter\AbsoluteContentUrls;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * A newsletter issue for one recipient. Delivery jobs send it immediately, so it is
 * not queued itself. A null unsubscribe URL marks an editor's test email, which omits
 * the one-click unsubscribe headers. The idempotency key lets Resend discard a repeat
 * of the same delivery if a retry follows a send whose result was lost.
 */
class NewsletterIssueMail extends Mailable
{
    public function __construct(
        public readonly NewsletterIssue $issue,
        public readonly ?string $unsubscribeUrl = null,
        public readonly ?string $idempotencyKey = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->issue->title);
    }

    public function headers(): Headers
    {
        $headers = [];

        if ($this->unsubscribeUrl !== null) {
            $headers['List-Unsubscribe'] = "<{$this->unsubscribeUrl}>";
            $headers['List-Unsubscribe-Post'] = 'List-Unsubscribe=One-Click';
        }

        if ($this->idempotencyKey !== null) {
            $headers['Resend-Idempotency-Key'] = $this->idempotencyKey;
        }

        return new Headers(text: $headers);
    }

    public function content(): Content
    {
        $markdown = app(MarkdownRenderer::class);
        $urls = app(AbsoluteContentUrls::class);

        return new Content(
            html: 'emails.newsletter-issue',
            text: 'emails.newsletter-issue-text',
            with: [
                'bodyHtml' => $urls->inHtml($markdown->safe($this->issue->content)),
                'bodyText' => $urls->inMarkdown($this->issue->content),
                'issueUrl' => route('newsletter.issue', $this->issue),
            ],
        );
    }
}
