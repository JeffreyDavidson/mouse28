<?php

use App\Actions\GenerateRobotsTxt;

covers(GenerateRobotsTxt::class);

test('production allows crawlers, keeps them out of the admin and signed previews, and points them to the sitemap', function (): void {
    config()->set('app.deployment_environment', 'production');

    $document = app(GenerateRobotsTxt::class)->handle();

    expect($document)->toBe(implode("\n", [
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

test('every other deployment environment blocks all crawlers', function (string $environment): void {
    config()->set('app.deployment_environment', $environment);

    $document = app(GenerateRobotsTxt::class)->handle();

    expect($document)->toBe("User-agent: *\nDisallow: /\n");
})->with([
    'staging' => ['staging'],
    'local' => ['local'],
    'unknown' => ['preview'],
    'wrong case' => ['Production'],
    'empty' => [''],
]);

test('search results stay crawlable so their noindex tag can be read', function (): void {
    config()->set('app.deployment_environment', 'production');

    $document = app(GenerateRobotsTxt::class)->handle();

    expect($document)->not->toContain('Disallow: /search');
});
