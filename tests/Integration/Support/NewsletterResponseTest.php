<?php

use App\Http\Requests\StoreNewsletterRequest;
use App\Support\NewsletterResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

covers(NewsletterResponse::class);

test('successful JSON responses contain a success flag', function (): void {
    $request = StoreNewsletterRequest::create(route('home'), 'POST', [
        'email' => 'reader@example.test',
    ]);
    $request->headers->set('Accept', 'application/json');

    $response = (new NewsletterResponse)->success($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);

    if (! $response instanceof JsonResponse) {
        throw new UnexpectedValueException('The newsletter success response must be JSON.');
    }

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true))->toBe(['success' => true]);
});

test('successful redirects flash a success message to the newsletter anchor', function (): void {
    $request = StoreNewsletterRequest::create(route('home'), 'POST', [
        'email' => 'reader@example.test',
    ]);

    $response = (new NewsletterResponse)->success($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);

    if (! $response instanceof RedirectResponse) {
        throw new UnexpectedValueException('The newsletter success response must redirect.');
    }

    expect($response->getTargetUrl())->toEndWith('#newsletter')
        ->and(session()->get('newsletter_success'))->toBeTrue();
});

test('error JSON responses preserve the supplied status and safe message', function (): void {
    $request = StoreNewsletterRequest::create(route('home'), 'POST', [
        'email' => 'reader@example.test',
    ]);
    $request->headers->set('Accept', 'application/json');

    $response = (new NewsletterResponse)->error($request, 503);

    expect($response)->toBeInstanceOf(JsonResponse::class);

    if (! $response instanceof JsonResponse) {
        throw new UnexpectedValueException('The newsletter error response must be JSON.');
    }

    expect($response->getStatusCode())->toBe(503)
        ->and($response->getData(true))->toBe(['error' => 'Something went wrong.']);
});

test('error redirects preserve the email and flash an error message', function (): void {
    $request = StoreNewsletterRequest::create(route('home'), 'POST', [
        'email' => 'reader@example.test',
    ]);

    $response = (new NewsletterResponse)->error($request, 422);

    expect($response)->toBeInstanceOf(RedirectResponse::class);

    if (! $response instanceof RedirectResponse) {
        throw new UnexpectedValueException('The newsletter error response must redirect.');
    }

    expect($response->getTargetUrl())->toEndWith('#newsletter')
        ->and(session()->getOldInput('email'))->toBe('reader@example.test')
        ->and(session()->get('newsletter_error'))->toBe('Something went wrong. Please try again.');
});
