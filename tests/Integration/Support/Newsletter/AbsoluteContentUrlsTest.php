<?php

use App\Support\Newsletter\AbsoluteContentUrls;

covers(AbsoluteContentUrls::class);

beforeEach(fn () => config()->set('app.url', 'https://example.test/'));

test('relative html links and images point at the site', function (): void {
    $html = '<a href="/blog/x">A</a> <a href="blog/y">B</a> <img src="/storage/pic.png"> <a href="/blog?page=2&amp;a=1">C</a>';

    expect(new AbsoluteContentUrls()->inHtml($html))
        ->toBe('<a href="https://example.test/blog/x">A</a> <a href="https://example.test/blog/y">B</a> <img src="https://example.test/storage/pic.png"> <a href="https://example.test/blog?page=2&amp;a=1">C</a>');
});

test('relative markdown link and image destinations point at the site', function (): void {
    $markdown = "[Post](/blog/x) [Rel](blog/y) [Angle](</blog/z>)\n\n![Pic](/storage/pic.png)";

    expect(new AbsoluteContentUrls()->inMarkdown($markdown))
        ->toBe("[Post](https://example.test/blog/x) [Rel](https://example.test/blog/y) [Angle](<https://example.test/blog/z>)\n\n![Pic](https://example.test/storage/pic.png)");
});

test('absolute, mailto, tel, anchor, protocol-relative and empty urls are left alone', function (string $url): void {
    $urls = new AbsoluteContentUrls;

    expect($urls->inHtml("<a href=\"{$url}\">A</a>"))
        ->toBe("<a href=\"{$url}\">A</a>")
        ->and($urls->inMarkdown("[A]({$url})"))
        ->toBe("[A]({$url})");
})->with([
    'absolute' => 'https://other.test/a',
    'mailto' => 'mailto:me@example.com',
    'tel' => 'tel:+15555550100',
    'anchor' => '#section',
    'protocol-relative' => '//cdn.test/e',
]);

test('an empty html attribute stays empty', function (): void {
    expect(new AbsoluteContentUrls()->inHtml('<a href="">A</a>'))->toBe('<a href="">A</a>');
});

test('markdown angle-bracket destinations with spaces become clean absolute urls', function (): void {
    $markdown = "[Post](< /blog/z >)\n\n![Pic](< /storage/pic.png >)";

    expect(new AbsoluteContentUrls()->inMarkdown($markdown))
        ->toBe("[Post](<https://example.test/blog/z>)\n\n![Pic](<https://example.test/storage/pic.png>)");
});
