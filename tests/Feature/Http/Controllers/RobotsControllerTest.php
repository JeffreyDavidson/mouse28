<?php

use App\Actions\GenerateRobotsTxt;

use function Pest\Laravel\get;

covers(GenerateRobotsTxt::class);

test('robots.txt is served as plain text from the generated document', function (): void {
    config()->set('app.deployment_environment', 'production');

    $response = get(route('robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    expect($response->getContent())->toBe(app(GenerateRobotsTxt::class)->handle());
});

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
