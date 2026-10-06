<?php

namespace App\Mail;

use App\Models\NewsletterIssue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

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
        return new Content(
            html: 'emails.newsletter-issue',
            text: 'emails.newsletter-issue-text',
            with: [
                // Match the public site's Markdown safety settings.
                'bodyHtml' => $this->absoluteHtmlUrls(Str::markdown($this->issue->content, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ])),
                'bodyText' => $this->absoluteMarkdownUrls($this->issue->content),
                'issueUrl' => route('newsletter.issue', $this->issue),
            ],
        );
    }

    /**
     * Mail clients cannot resolve relative URLs, so point every relative link and
     * image in the rendered body at the site.
     */
    private function absoluteHtmlUrls(string $html): string
    {
        return preg_replace_callback(
            '/\b(href|src)="([^"]*)"/i',
            fn (array $match): string => "{$match[1]}=\"{$this->absoluteUrl($match[2])}\"",
            $html,
        ) ?? $html;
    }

    /**
     * The plain-text part is the raw Markdown, so rewrite its link and image destinations.
     */
    private function absoluteMarkdownUrls(string $markdown): string
    {
        return preg_replace_callback(
            '/(\]\(\s*<?)([^)\s>]+)/',
            fn (array $match): string => "{$match[1]}{$this->absoluteUrl($match[2])}",
            $markdown,
        ) ?? $markdown;
    }

    /**
     * Prefix only relative URLs; absolute, protocol-relative, mailto, tel and anchor
     * URLs are left alone.
     */
    private function absoluteUrl(string $url): string
    {
        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '//')) {
            return $url;
        }

        if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url) === 1) {
            return $url;
        }

        $base = rtrim(Config::string('app.url'), '/');

        return str_starts_with($url, '/')
            ? "{$base}{$url}"
            : "{$base}/{$url}";
    }
}
