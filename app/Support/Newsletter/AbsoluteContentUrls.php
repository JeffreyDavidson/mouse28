<?php

namespace App\Support\Newsletter;

use Illuminate\Support\Facades\Config;

/**
 * Mail clients cannot resolve relative URLs, so these methods point every relative
 * link and image destination in newsletter content at the site.
 */
class AbsoluteContentUrls
{
    /**
     * Point every relative link and image in rendered HTML at the site.
     */
    public function inHtml(string $html): string
    {
        return preg_replace_callback(
            '/\b(href|src)="([^"]*)"/i',
            fn (array $match): string => "{$match[1]}=\"{$this->absoluteUrl($match[2])}\"",
            $html,
        ) ?? $html;
    }

    /**
     * Rewrite the link and image destinations of raw Markdown, for the plain-text part.
     */
    public function inMarkdown(string $markdown): string
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
