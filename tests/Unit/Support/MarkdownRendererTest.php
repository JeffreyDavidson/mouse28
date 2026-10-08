<?php

use App\Support\MarkdownRenderer;

covers(MarkdownRenderer::class);

test('every preset renders Markdown formatting', function (string $preset): void {
    $html = new MarkdownRenderer()->{$preset}("Hello **readers**.\n\n- one\n- two");

    expect($html)
        ->toContain('<strong>readers</strong>')
        ->toContain('<li>two</li>');
})->with(['safe', 'safeWithLineBreaks']);

test('every preset strips raw HTML but keeps its text', function (string $preset): void {
    $html = new MarkdownRenderer()->{$preset}("<script>alert('x')</script>\n\nA <b>bold</b> claim.");

    expect($html)
        ->not->toContain('<script>')
        ->not->toContain('<b>')
        ->toContain('claim.');
})->with(['safe', 'safeWithLineBreaks']);

test('every preset drops unsafe link schemes and keeps safe ones', function (string $preset, string $unsafe): void {
    $html = new MarkdownRenderer()->{$preset}("[Click]({$unsafe}) and [Read](https://example.com/read) and [Home](/blog)");

    expect($html)
        ->not->toContain($unsafe)
        ->toContain('href="https://example.com/read"')
        ->toContain('href="/blog"');
})->with(['safe', 'safeWithLineBreaks'])
    ->with([
        'javascript' => 'javascript:alert(1)',
        'vbscript' => 'vbscript:run',
        'data' => 'data:text/html;base64,AAA',
    ]);

test('the safe preset leaves a soft line break as a newline', function (): void {
    $html = new MarkdownRenderer()->safe("First line\nSecond line");

    expect($html)
        ->toBe("<p>First line\nSecond line</p>\n")
        ->not->toContain('<br');
});

test('the line break preset turns a soft line break into a visible break', function (): void {
    $html = new MarkdownRenderer()->safeWithLineBreaks("First line\nSecond line");

    expect($html)->toBe("<p>First line<br />\nSecond line</p>\n");
});

test('a missing source renders as nothing', function (string $preset): void {
    expect(new MarkdownRenderer()->{$preset}(null))->toBe('');
})->with(['safe', 'safeWithLineBreaks']);
