<?php

use App\Http\Controllers\NewsletterOneClickUnsubscriptionController;
use App\Models\Subscriber;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\post;

covers(NewsletterOneClickUnsubscriptionController::class);

pest()->use(RefreshDatabase::class);

/**
 * The forgery middleware is bypassed while running tests, so this copy
 * enforces it to prove which paths are exempt.
 */
function enforcedForgeryMiddleware(): PreventRequestForgery
{
    return new class(app(), app(Encrypter::class)) extends PreventRequestForgery
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    };
}

function forgeryCheckedStatus(string $url): int
{
    $request = Request::create($url, 'POST');
    $request->setLaravelSession(app('session.store'));

    $response = enforcedForgeryMiddleware()->handle($request, fn (): Response => response()->noContent());

    if (! $response instanceof Response) {
        throw new RuntimeException('The middleware must return an HTTP response.');
    }

    return $response->getStatusCode();
}

test('a signed one-click post unsubscribes the reader', function (): void {
    $reader = Subscriber::factory()->create();

    post(URL::signedRoute('newsletter.unsubscribe.oneClick', $reader), ['List-Unsubscribe' => 'One-Click'])
        ->assertNoContent();

    expect($reader->refresh()->isActive())->toBeFalse();
});

test('an unsigned one-click post is refused', function (): void {
    $reader = Subscriber::factory()->create();

    post(route('newsletter.unsubscribe.oneClick', $reader), ['List-Unsubscribe' => 'One-Click'])
        ->assertForbidden();

    expect($reader->refresh()->isActive())->toBeTrue();
});

test('mail providers can post one-click requests without a forgery token', function (): void {
    $url = URL::signedRoute('newsletter.unsubscribe.oneClick', Subscriber::factory()->create());

    expect(forgeryCheckedStatus($url))->toBe(204);
});

test('other posts keep forgery protection', function (): void {
    expect(fn () => forgeryCheckedStatus(route('contact.store')))->toThrow(TokenMismatchException::class);
});
