<?php

use App\Rules\PassesTurnstile;
use App\Services\TurnstileVerifier;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

covers(PassesTurnstile::class);

beforeEach(function (): void {
    configureTurnstile();
    Http::preventStrayRequests();
});

/**
 * Validate the request's Turnstile token with the rule, as the contact form does.
 *
 * @param  array<string, mixed>  $input
 */
function validateTurnstile(array $input): Illuminate\Validation\Validator
{
    $request = Request::create(route('contact.store'), 'POST', $input, server: ['REMOTE_ADDR' => '203.0.113.10']);

    return Validator::make($input, [
        'cf-turnstile-response' => [new PassesTurnstile(app(TurnstileVerifier::class), $request, 'contact-form')],
    ]);
}

it('accepts a token Turnstile verifies for the expected action', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'mouse28.com',
        ]),
    ]);

    $validator = validateTurnstile(['cf-turnstile-response' => 'valid-token']);

    expect($validator->passes())
        ->toBeTrue();
    Http::assertSent(fn (ClientRequest $request): bool => $request['response'] === 'valid-token'
        && $request['remoteip'] === '203.0.113.10');
});

it('rejects a token Turnstile does not verify', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
    ]);

    $validator = validateTurnstile(['cf-turnstile-response' => 'invalid-token']);

    expect($validator->errors()
        ->get('cf-turnstile-response'))
        ->toBe(['Please verify that you are human and try again.']);
});

it('rejects a token verified for a different action', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
    ]);

    $validator = validateTurnstile(['cf-turnstile-response' => 'newsletter-token']);

    expect($validator->errors()
        ->get('cf-turnstile-response'))
        ->toBe(['Please verify that you are human and try again.']);
});

it('rejects a missing, blank or non-string token without contacting Turnstile', function (array $input) {
    /** @var array<string, mixed> $input */
    Http::fake();

    $validator = validateTurnstile($input);

    expect($validator->errors()
        ->get('cf-turnstile-response'))
        ->toBe(['Please verify that you are human and try again.']);
    Http::assertNothingSent();
})->with([
    'missing' => [[]],
    'blank' => [['cf-turnstile-response' => '']],
    'whitespace only' => [['cf-turnstile-response' => '   ']],
    'number' => [['cf-turnstile-response' => 123]],
    'array' => [['cf-turnstile-response' => ['unexpected']]],
]);
