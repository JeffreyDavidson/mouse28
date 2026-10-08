<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Renders untrusted Markdown to HTML with named safety presets, so every caller
 * gets the same options instead of repeating them. Every preset strips raw HTML
 * and refuses unsafe link schemes such as javascript:.
 */
class MarkdownRenderer
{
    /**
     * Safe HTML where a single newline inside a paragraph stays a soft break
     * (a newline character, which browsers render as a space).
     */
    public function safe(?string $markdown): string
    {
        return Str::markdown($markdown ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Safe HTML where a single newline inside a paragraph becomes a visible
     * `<br />`, matching how authors type in the editor.
     */
    public function safeWithLineBreaks(?string $markdown): string
    {
        return Str::markdown($markdown ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'renderer' => [
                'soft_break' => "<br />\n",
            ],
        ]);
    }
}
