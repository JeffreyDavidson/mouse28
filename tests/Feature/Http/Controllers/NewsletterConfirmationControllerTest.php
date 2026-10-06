<?php

use App\Actions\ConfirmNewsletterSubscription;
use App\Http\Controllers\NewsletterConfirmationController;
use App\Http\Middleware\EnsureValidNewsletterConfirmationLink;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

covers(NewsletterConfirmationController::class, EnsureValidNewsletterConfirmationLink::class, ConfirmNewsletterSubscription::class);

pest()->use(RefreshDatabase::class);

function pendingReaderWithToken(string $token = 'confirmation-token'): Subscriber
{
    $reader = Subscriber::factory()->pending()->create();
    $reader->verification_token_hash = hash('sha256', $token);
    $reader->save();

    return $reader;
}

function confirmationUrl(Subscriber $reader, string $token = 'confirmation-token'): string
{
    return URL::temporarySignedRoute('newsletter.confirm', Date::now()->addDay(), [
        'subscriber' => $reader,
        'token' => $token,
    ]);
}

test('the confirmation page submits itself in a browser but does not confirm on its own', function (): void {
    $reader = pendingReaderWithToken();
    $url = confirmationUrl($reader);

    get($url)
        ->assertOk()
        ->assertViewIs('pages.newsletter.confirm')
        ->assertSee($reader->email)
        ->assertSee('Confirm sign-up')
        ->assertSee('Confirming your sign-up…')
        ->assertSeeHtml('action="'.e($url).'"')
        ->assertSeeHtml('method="POST"')
        ->assertSeeHtml('data-newsletter-confirm')
        ->assertSeeHtml('x-data="newsletterConfirm"')
        ->assertSeeHtml('<meta name="robots" content="noindex,nofollow">');

    expect($reader->refresh()->verified_at)->toBeNull();
});

test('a link scanner fetching the confirmation link confirms nothing and leaves the link usable', function (string $method): void {
    $reader = pendingReaderWithToken();
    $url = confirmationUrl($reader);

    foreach (range(1, 3) as $scan) {
        $this->call($method, $url)->assertOk();
    }

    expect($reader->refresh()->verified_at)->toBeNull()
        ->and($reader->verification_token_hash)->toBe(hash('sha256', 'confirmation-token'));

    post(URL::temporarySignedRoute('newsletter.confirm.store', Date::now()->addDay(), [
        'subscriber' => $reader,
        'token' => 'confirmation-token',
    ]))->assertRedirect(route('newsletter.confirmed'));

    expect($reader->refresh()->isActive())->toBeTrue();
})->with(['GET', 'HEAD']);

test('the confirmation page is never cached, including by the back-forward cache', function (): void {
    get(confirmationUrl(pendingReaderWithToken()))
        ->assertHeader('Cache-Control', 'no-store, private');
});

test('confirming activates the reader and shows the confirmed page', function (): void {
    $reader = pendingReaderWithToken();

    post(URL::temporarySignedRoute('newsletter.confirm.store', Date::now()->addDay(), [
        'subscriber' => $reader,
        'token' => 'confirmation-token',
    ]))
        ->assertRedirect(route('newsletter.confirmed'));

    expect($reader->refresh()->isActive())->toBeTrue()
        ->and($reader->verification_token_hash)->toBeNull();
});

function expectedExpiredLinkRedirect(): string
{
    return route('home').'#newsletter';
}

test('confirmation links that cannot be used send the reader back to the sign-up form', function (Closure $link): void {
    $reader = pendingReaderWithToken();

    $url = $link($reader);

    get(is_string($url) ? $url : '')
        ->assertRedirect(expectedExpiredLinkRedirect())
        ->assertSessionHasErrorsIn('newsletter', ['email' => 'This confirmation link has expired or has already been used. If you already confirmed, you’re subscribed. Otherwise, sign up again below.']);

    expect($reader->refresh()->verified_at)->toBeNull();
})->with([
    'wrong token' => [fn (Subscriber $reader): string => confirmationUrl($reader, 'another-token')],
    'unsigned link' => [fn (Subscriber $reader): string => route('newsletter.confirm', ['subscriber' => $reader, 'token' => 'confirmation-token'])],
    'tampered signature' => [fn (Subscriber $reader): string => confirmationUrl($reader).'x'],
]);

test('an expired confirmation link sends the reader back to the sign-up form', function (): void {
    $reader = pendingReaderWithToken();
    $url = confirmationUrl($reader);

    $this->travel(25)->hours();

    get($url)
        ->assertRedirect(expectedExpiredLinkRedirect())
        ->assertSessionHasErrorsIn('newsletter', ['email']);

    expect($reader->refresh()->verified_at)->toBeNull();
});

test('a confirmation link cannot be used twice', function (): void {
    $reader = pendingReaderWithToken();
    $url = URL::temporarySignedRoute('newsletter.confirm.store', Date::now()->addDay(), [
        'subscriber' => $reader,
        'token' => 'confirmation-token',
    ]);

    post($url)->assertRedirect(route('newsletter.confirmed'));
    post($url)
        ->assertRedirect(expectedExpiredLinkRedirect())
        ->assertSessionHasErrorsIn('newsletter', ['email']);
});

test('a confirmation link for a removed subscriber goes back to the sign-up form', function (): void {
    $reader = pendingReaderWithToken();
    $url = confirmationUrl($reader);
    $reader->delete();

    get($url)
        ->assertRedirect(expectedExpiredLinkRedirect())
        ->assertSessionHasErrorsIn('newsletter', ['email']);
});

test('the sign-up form shows why a confirmation link was refused', function (): void {
    $reader = pendingReaderWithToken();

    get(confirmationUrl($reader, 'another-token'))->assertRedirect();

    get(route('home'))->assertSee('This confirmation link has expired or has already been used.');
});

test('confirmation links are rate limited', function (): void {
    config()->set('mouse28.rate_limits.newsletter_confirm_per_minute', 1);
    $url = confirmationUrl(pendingReaderWithToken());

    get($url)->assertOk();
    get($url)->assertTooManyRequests();
});

test('the confirmed page thanks the reader without any subscriber data and is not indexed', function (): void {
    $reader = pendingReaderWithToken();
    post(URL::temporarySignedRoute('newsletter.confirm.store', Date::now()->addDay(), [
        'subscriber' => $reader,
        'token' => 'confirmation-token',
    ]));

    get(route('newsletter.confirmed'))
        ->assertOk()
        ->assertSee('You’re confirmed')
        ->assertSeeHtml('<meta name="robots" content="noindex,nofollow">')
        ->assertDontSee($reader->email);
});
