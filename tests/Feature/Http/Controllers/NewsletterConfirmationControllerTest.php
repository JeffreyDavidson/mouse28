<?php

use App\Actions\ConfirmNewsletterSubscription;
use App\Http\Controllers\NewsletterConfirmationController;
use App\Http\Middleware\EnsureValidNewsletterConfirmationToken;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

covers(NewsletterConfirmationController::class, EnsureValidNewsletterConfirmationToken::class, ConfirmNewsletterSubscription::class);

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

test('the confirmation page asks for a click and does not confirm on its own', function (): void {
    $reader = pendingReaderWithToken();
    $url = confirmationUrl($reader);

    get($url)
        ->assertOk()
        ->assertViewIs('pages.newsletter.confirm')
        ->assertSee($reader->email)
        ->assertSee('Confirm sign-up')
        ->assertSeeHtml('action="'.e($url).'"')
        ->assertSeeHtml('<meta name="robots" content="noindex,nofollow">');

    expect($reader->refresh()->verified_at)->toBeNull();
});

test('confirming activates the reader and returns to the newsletter sign-up', function (): void {
    $reader = pendingReaderWithToken();

    post(URL::temporarySignedRoute('newsletter.confirm.store', Date::now()->addDay(), [
        'subscriber' => $reader,
        'token' => 'confirmation-token',
    ]))
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_success', 'You\'re subscribed. Thanks for confirming!');

    expect($reader->refresh()->isActive())->toBeTrue()
        ->and($reader->verification_token_hash)->toBeNull();
});

test('confirmation links with a wrong token are refused', function (): void {
    $reader = pendingReaderWithToken();

    get(confirmationUrl($reader, 'another-token'))->assertForbidden();

    expect($reader->refresh()->verified_at)->toBeNull();
});

test('a confirmation link cannot be used twice', function (): void {
    $reader = pendingReaderWithToken();
    $url = URL::temporarySignedRoute('newsletter.confirm.store', Date::now()->addDay(), [
        'subscriber' => $reader,
        'token' => 'confirmation-token',
    ]);

    post($url)->assertRedirect();
    post($url)->assertForbidden();
});

test('unsigned confirmation links are refused', function (): void {
    $reader = pendingReaderWithToken();

    get(route('newsletter.confirm', ['subscriber' => $reader, 'token' => 'confirmation-token']))->assertForbidden();

    expect($reader->refresh()->verified_at)->toBeNull();
});

test('expired confirmation links are refused', function (): void {
    $url = confirmationUrl(pendingReaderWithToken());

    $this->travel(25)->hours();

    get($url)->assertForbidden();
});

test('confirmation links are rate limited', function (): void {
    config()->set('mouse28.rate_limits.newsletter_confirm_per_minute', 1);
    $url = confirmationUrl(pendingReaderWithToken());

    get($url)->assertOk();
    get($url)->assertTooManyRequests();
});
