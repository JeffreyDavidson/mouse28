<?php

namespace App\Support;

use Illuminate\Support\Str;

class PlainText
{
    /**
     * Render Markdown and keep only its words, for descriptions in feeds and metadata.
     *
     * Raw HTML is rendered too so that its text survives; the tags are stripped
     * afterwards, so the result is never output as HTML.
     */
    public static function fromMarkdown(?string $markdown): string
    {
        $html = Str::markdown($markdown ?? '', ['allow_unsafe_links' => false]);

        return Str::squish(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5));
    }

    /**
     * Join a spaced dash to the word before it with a non-breaking space, so a
     * wrapping title never starts a line with the dash. For display only.
     */
    public static function keepDashWithPrecedingWord(string $text): string
    {
        return preg_replace('/\s+([—–])(?=\s)/u', "\u{00A0}$1", $text) ?? $text;
    }
}
