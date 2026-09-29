<?php

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Tests\Support\DeploymentSmokeClient;

pest()->group('production');

function productionSmokeBaseUrl(): ?string
{
    $baseUrl = getenv('PRODUCTION_BASE_URL');

    return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : null;
}

function productionSmokeRequest(): PendingRequest
{
    $baseUrl = productionSmokeBaseUrl() ?? throw new LogicException('Set PRODUCTION_BASE_URL to run production smoke tests.');

    return DeploymentSmokeClient::request($baseUrl, getenv('CF_ACCESS_CLIENT_ID'), getenv('CF_ACCESS_CLIENT_SECRET'));
}

beforeEach(function (): void {
    $baseUrl = productionSmokeBaseUrl();

    if ($baseUrl !== null) {
        // The suite checks the deployed site over real HTTP; every other host stays blocked.
        Http::allowStrayRequests([$baseUrl, "{$baseUrl}/*"]);
    }
});

test('the deployed site serves the critical public routes', function (string $route): void {
    $response = productionSmokeRequest()->get($route);

    expect($response->successful())->toBeTrue("Expected {$route} to succeed; received HTTP {$response->status()}.");
})->with([
    '/',
    '/about',
    '/blog',
    '/contact',
    '/episodes',
    '/privacy',
    '/robots.txt',
    '/rss/blog',
    '/search?q=disney',
    '/sitemap.xml',
    '/up',
])->skip(fn (): bool => productionSmokeBaseUrl() === null, 'Set PRODUCTION_BASE_URL to run production smoke tests.');

test('the deployed site sends the podcast feed to Transistor', function (): void {
    $response = productionSmokeRequest()->get('/rss/podcast');

    expect($response->status())->toBe(301)
        ->and($response->header('Location'))->toStartWith('https://');
})->skip(fn (): bool => productionSmokeBaseUrl() === null, 'Set PRODUCTION_BASE_URL to run production smoke tests.');

test('the deployed admin entry point redirects to authentication', function (): void {
    $response = productionSmokeRequest()->get('/admin');

    expect($response->status())->toBe(302)
        ->and($response->header('Location'))->toMatch('#/admin/login$#');
})->skip(fn (): bool => productionSmokeBaseUrl() === null, 'Set PRODUCTION_BASE_URL to run production smoke tests.');

test('the deployed public routes return the required security headers', function (string $route): void {
    $response = productionSmokeRequest()->get($route);
    // Nginx and the application may both send a header; each combined value must still be safe.
    $values = fn (string $header): array => array_map(trim(...), explode(',', $response->header($header)));

    expect($response->successful())->toBeTrue("Expected {$route} to succeed; received HTTP {$response->status()}.")
        ->and($values('X-Frame-Options'))->each->toBe('SAMEORIGIN')
        ->and($values('X-Content-Type-Options'))->each->toBe('nosniff')
        ->and($response->header('Content-Security-Policy'))->toContain("frame-ancestors 'self'")
        ->and($response->header('Strict-Transport-Security'))->toContain('max-age=31536000')
        ->and($response->header('Referrer-Policy'))->toBe('strict-origin-when-cross-origin');
})->with(['/', '/blog', '/contact', '/episodes'])
    ->skip(fn (): bool => productionSmokeBaseUrl() === null, 'Set PRODUCTION_BASE_URL to run production smoke tests.');
