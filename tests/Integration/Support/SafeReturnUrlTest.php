<?php

use App\Support\SafeReturnUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

beforeEach(function (): void {
    URL::forceRootUrl('https://mouse28.test');
    URL::forceScheme('https');
});

test('unsafe or missing referrers use the supplied fallback', function (?string $referrer): void {
    $request = Request::create(route('newsletter.store'), 'POST');
    if ($referrer !== null) {
        $request->headers->set('referer', $referrer);
    }

    $returnUrl = SafeReturnUrl::from($request, 'https://mouse28.test/');

    expect($returnUrl)->toBe('https://mouse28.test/');
})->with([
    'missing' => [null],
    'empty' => [''],
    'relative' => ['/blog'],
    'external host' => ['https://example.com/blog'],
    'misleading host suffix' => ['https://mouse28.test.example.com/blog'],
    'different scheme' => ['http://mouse28.test/blog'],
    'different port' => ['https://mouse28.test:8443/blog'],
    'malformed URL' => ['https://mouse28.test:invalid/blog'],
]);

test('same origin referrers preserve their path and query while removing fragments', function (string $referrer, string $expected): void {
    $request = Request::create(route('newsletter.store'), 'POST');
    $request->headers->set('referer', $referrer);

    $returnUrl = SafeReturnUrl::from($request, 'https://mouse28.test/');

    expect($returnUrl)->toBe($expected);
})->with([
    'path' => ['https://mouse28.test/blog', 'https://mouse28.test/blog'],
    'query and fragment' => ['https://mouse28.test/blog?q=parks#newsletter', 'https://mouse28.test/blog?q=parks'],
    'explicit default port' => ['https://mouse28.test:443/blog', 'https://mouse28.test:443/blog'],
]);
