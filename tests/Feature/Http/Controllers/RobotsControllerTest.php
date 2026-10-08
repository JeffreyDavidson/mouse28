<?php

use App\Http\Controllers\RobotsController;

use function Pest\Laravel\get;

covers(RobotsController::class);

test('robots.txt allows crawlers in production, keeps them out of the admin and signed previews, and points them to the sitemap', function (): void {
    config()->set('app.deployment_environment', 'production');

    $response = get(route('robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    expect($response->getContent())->toBe(implode("\n", [
        'User-agent: *',
        'Allow: /',
        '',
        'Disallow: /admin',
        'Disallow: /admin/*',
        'Disallow: /preview/',
        '',
        'Sitemap: '.route('sitemap'),
        '',
    ]));
});

test('robots.txt blocks all crawlers in every other deployment environment', function (string $environment): void {
    config()->set('app.deployment_environment', $environment);

    $response = get(route('robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    expect($response->getContent())->toBe("User-agent: *\nDisallow: /\n");
})->with([
    'staging' => ['staging'],
    'local' => ['local'],
    'unknown' => ['preview'],
    'wrong case' => ['Production'],
    'empty' => [''],
]);

test('robots.txt is served like a static file without cookies and publicly cacheable', function (string $environment): void {
    config()->set('app.deployment_environment', $environment);

    $response = get(route('robots'))
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie')
        ->assertHeader('Cache-Control', 'max-age=3600, public')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy');

    expect($response->headers->getCookies())->toBeEmpty();
})->with([
    'production',
    'staging',
]);

test('robots.txt is not shadowed by a static robots file', function (): void {
    expect(public_path('robots.txt'))->not->toBeFile();
});
