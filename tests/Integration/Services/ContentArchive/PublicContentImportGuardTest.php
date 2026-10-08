<?php

use App\Services\ContentArchive\PublicContentImportGuard;

covers(PublicContentImportGuard::class);

test('public content imports are allowed only away from the live site', function (string $environment, string $url, bool $staging, bool $allowed): void {
    app()->detectEnvironment(fn (): string => $environment);
    config()->set('app.url', $url);

    expect(app(PublicContentImportGuard::class)->allows($staging))->toBe($allowed);
})->with([
    'local site' => ['local', 'https://mouse28.test', false, true],
    'testing site' => ['testing', 'http://localhost', false, true],
    'live site address outside production' => ['local', 'https://mouse28.com', false, false],
    'live www address outside production' => ['local', 'https://www.mouse28.com', true, false],
    'live address in another case with a trailing dot' => ['local', 'https://MOUSE28.COM.', false, false],
    'live site in production' => ['production', 'https://mouse28.com', true, false],
    'staging in production without the staging option' => ['production', 'https://staging.mouse28.com', false, false],
    'staging in production with the staging option' => ['production', 'https://staging.mouse28.com', true, true],
    'another host in production with the staging option' => ['production', 'https://example.com', true, false],
    'an address without a host' => ['local', 'not a url', false, false],
]);

test('public content imports are refused when the site address is not text', function (): void {
    config()->set('app.url', null);

    expect(app(PublicContentImportGuard::class)->allows(false))->toBeFalse();
});
