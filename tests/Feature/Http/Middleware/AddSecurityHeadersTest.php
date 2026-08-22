<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

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
    'admin page' => [fn (): string => '/admin/login', 200],
    'xml response' => [fn (): string => route('sitemap'), 200],
    'error response' => [fn (): string => '/this-page-does-not-exist', 404],
    'server error response' => [fn (): string => '/testing/security-header-error', 500],
]);

test('private and error responses cannot be indexed', function (string $url, int $status): void {
    get($url)
        ->assertStatus($status)
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
})->with([
    'admin page' => [fn (): string => '/admin/login', 200],
    'preview page' => [fn (): string => '/preview/testing', 200],
    'error response' => [fn (): string => '/this-page-does-not-exist', 404],
    'server error response' => [fn (): string => '/testing/security-header-error', 500],
]);

test('normal public responses do not receive a noindex header', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag');
});
