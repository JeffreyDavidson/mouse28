<?php

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

covers(AddSecurityHeaders::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('app.debug', false);
    Route::get('/preview/testing', fn (): string => 'Preview');
    Route::get('/testing/security-header-error', function (): never {
        throw new RuntimeException('Sensitive server details');
    });
});

test('web responses include baseline security headers', function (string $url, int $status): void {
    get($url)
        ->assertStatus($status)
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
})->with([
    'public page' => [fn (): string => route('home'), 200],
    'admin page' => [fn (): string => route('filament.admin.auth.login'), 200],
    'xml response' => [fn (): string => route('sitemap'), 200],
    'error response' => [fn (): string => '/this-page-does-not-exist', 404],
    'server error response' => [fn (): string => '/testing/security-header-error', 500],
]);

test('private and error responses cannot be indexed', function (string $url, int $status): void {
    get($url)
        ->assertStatus($status)
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
})->with([
    'admin page' => [fn (): string => route('filament.admin.auth.login'), 200],
    'preview page' => [fn (): string => '/preview/testing', 200],
    'error response' => [fn (): string => '/this-page-does-not-exist', 404],
    'server error response' => [fn (): string => '/testing/security-header-error', 500],
]);

test('normal public responses do not receive a noindex header', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag');
});

test('private and error responses cannot be stored', function (string $url, int $status): void {
    $response = get($url)
        ->assertStatus($status);

    expect($response->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('private');
})->with([
    'admin page' => [fn (): string => route('filament.admin.auth.login'), 200],
    'preview page' => [fn (): string => '/preview/testing', 200],
    'error response' => [fn (): string => '/this-page-does-not-exist', 404],
    'server error response' => [fn (): string => '/testing/security-header-error', 500],
]);

test('normal public responses remain eligible for application caching', function (): void {
    $response = get(route('home'))
        ->assertOk();

    expect($response->headers->get('Cache-Control'))
        ->not->toContain('no-store');
});

test('web responses isolate their browsing context and resources', function (string $url, int $status): void {
    get($url)
        ->assertStatus($status)
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
})->with([
    'public page' => [fn (): string => route('home'), 200],
    'admin page' => [fn (): string => route('filament.admin.auth.login'), 200],
    'error response' => [fn (): string => '/this-page-does-not-exist', 404],
]);

test('public pages allow only nonce-bearing scripts and the site third parties', function (): void {
    $response = get(route('home'))->assertOk();
    $policy = (string) $response->headers->get('Content-Security-Policy');

    preg_match("/'nonce-([^']+)'/", $policy, $matches);
    $nonce = $matches[1] ?? '';

    expect($policy)
        ->toContain("script-src 'self' 'nonce-")
        ->toContain('https://challenges.cloudflare.com')
        ->toContain('https://cdn.usefathom.com')
        ->toContain('frame-src https://challenges.cloudflare.com https://share.transistor.fm')
        ->toContain("object-src 'none'")
        ->toContain("frame-ancestors 'self'")
        ->not->toContain("'unsafe-eval'")
        ->and($nonce)->not->toBeEmpty();

    $response->assertSeeHtml("nonce=\"{$nonce}\"");
});

test('the admin panel keeps the script allowances Filament requires', function (): void {
    $policy = (string) get(route('filament.admin.auth.login'))->assertOk()->headers->get('Content-Security-Policy');

    expect($policy)
        ->toContain("'unsafe-inline'")
        ->toContain("'unsafe-eval'")
        ->not->toContain("'nonce-");
});

test('error responses carry the content security policy', function (string $url, int $status): void {
    $policy = (string) get($url)->assertStatus($status)->headers->get('Content-Security-Policy');

    expect($policy)->toContain("default-src 'self'");
})->with([
    'not found' => ['/this-page-does-not-exist', 404],
    'server error' => ['/testing/security-header-error', 500],
]);

test('only secure responses declare strict transport security', function (bool $secure): void {
    $url = route('home');
    $response = get($secure ? str_replace('http://', 'https://', $url) : str_replace('https://', 'http://', $url))->assertOk();

    expect($response->headers->get('Strict-Transport-Security'))
        ->toBe($secure ? 'max-age=31536000; includeSubDomains' : null);
})->with([
    'https' => [true],
    'http' => [false],
]);

test('content security policies list exactly the approved sources', function (string $url, string $scripts): void {
    $policy = (string) get($url)->assertOk()->headers->get('Content-Security-Policy');
    $policy = (string) preg_replace("/'nonce-[^']+'/", "'nonce-*'", $policy);

    expect($policy)->toBe(implode('; ', [
        "base-uri 'self'",
        "connect-src 'self' https://challenges.cloudflare.com https://cdn.usefathom.com",
        "default-src 'self'",
        "font-src 'self' data:",
        "form-action 'self'",
        "frame-ancestors 'self'",
        'frame-src https://challenges.cloudflare.com https://share.transistor.fm',
        "img-src 'self' data: blob: https:",
        "media-src 'self' blob: https:",
        "object-src 'none'",
        "script-src 'self' {$scripts} https://challenges.cloudflare.com https://cdn.usefathom.com",
        "style-src 'self' 'unsafe-inline'",
        "worker-src 'self' blob:",
    ]));
})->with([
    'public page' => [fn (): string => route('home'), "'nonce-*'"],
    'admin page' => [fn (): string => route('filament.admin.auth.login'), "'unsafe-inline' 'unsafe-eval'"],
]);

test('local development allows the Vite dev server', function (): void {
    app()->detectEnvironment(fn (): string => 'local');

    $policy = (string) get(route('home'))->assertOk()->headers->get('Content-Security-Policy');

    expect($policy)
        ->toContain("script-src 'self' 'nonce-")
        ->toContain('https://cdn.usefathom.com http://localhost:* http://127.0.0.1:* https://localhost:* https://127.0.0.1:*;')
        ->toContain('https://cdn.usefathom.com http://localhost:* http://127.0.0.1:* https://localhost:* https://127.0.0.1:* ws://localhost:* ws://127.0.0.1:* wss://localhost:* wss://127.0.0.1:*;');
});
