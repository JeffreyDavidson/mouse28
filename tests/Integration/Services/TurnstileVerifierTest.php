<?php

use App\Services\TurnstileVerifier;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

covers(TurnstileVerifier::class);

beforeEach(function (): void {
    config()->set([
        'services.turnstile.secret_key' => 'test-secret',
        'services.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'services.turnstile.allowed_hostnames' => ['Mouse28.COM'],
    ]);
    Http::preventStrayRequests();
});

test('invalid credentials fail without contacting Turnstile', function (string $field, mixed $value): void {
    Http::fake();
    $input = ['cf-turnstile-response' => 'test-token'];

    if ($field === 'token') {
        $input['cf-turnstile-response'] = $value;
    } else {
        config()->set('services.turnstile.secret_key', $value);
    }

    $request = Request::create(route('contact.store'), 'POST', $input);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeFalse();
    Http::assertNothingSent();
})->with(['token', 'secret'])
    ->with([
        'missing' => [null],
        'empty' => [''],
        'whitespace' => ['   '],
        'integer' => [123],
        'array' => [['unexpected']],
    ]);

test('verification submits form credentials and accepts a case insensitive allowed hostname', function (): void {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'MOUSE28.com',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', [
        'cf-turnstile-response' => 'test-token',
    ], server: ['REMOTE_ADDR' => '203.0.113.10']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeTrue();
    Http::assertSentCount(1);
    Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded')
        && $request->data() === [
            'secret' => 'test-secret',
            'response' => 'test-token',
            'remoteip' => '203.0.113.10',
        ]);
});

test('verification requires an OK response with a boolean success value', function (mixed $success, int $status): void {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => $success,
            'action' => 'contact-form',
            'hostname' => 'mouse28.com',
        ], $status),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeFalse();
    Http::assertSentCount(1);
})->with([
    'integer success' => [1, 200],
    'string success' => ['true', 200],
    'null success' => [null, 200],
    'client error' => [true, 400],
    'server error' => [true, 503],
]);

test('verification rejects a malformed hostname', function (): void {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => ['mouse28.com'],
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeFalse();
});

test('a verification connection failure returns false', function (): void {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::failedConnection(),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeFalse();
});

test('a blank expected action fails without contacting Turnstile', function (): void {
    Http::fake();
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, '');

    expect($passes)->toBeFalse();
    Http::assertNothingSent();
});

test('a blank or missing verification URL fails closed without contacting Turnstile', function (?string $endpoint): void {
    Http::fake();
    config()->set('services.turnstile.siteverify_url', $endpoint);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeFalse();
    Http::assertNothingSent();
})->with([
    'missing' => [null],
    'empty' => [''],
    'whitespace' => ['   '],
]);

test('hostnames are compared ignoring case, spaces, and a trailing dot', function (): void {
    config()->set('services.turnstile.allowed_hostnames', [' Mouse28.COM. ']);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'mouse28.com.',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeTrue();
});

test('allowed hostnames must be a list of non-empty strings', function (mixed $allowed): void {
    config()->set('services.turnstile.allowed_hostnames', $allowed);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'mouse28.com',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeFalse();
})->with([
    'a string instead of a list' => ['mouse28.com'],
    'null' => [null],
    'non-string entries' => [[123, null, '']],
]);

test('non-string entries in the allowed hostnames are ignored', function (): void {
    config()->set('services.turnstile.allowed_hostnames', [123, 'mouse28.com']);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'mouse28.com',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);
    $turnstile = app(TurnstileVerifier::class);

    $passes = $turnstile->passes($request, 'contact-form');

    expect($passes)->toBeTrue();
});
