<?php

use App\Http\Middleware\EnsureValidNewsletterConfirmationLink;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\call;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

covers(EnsureValidNewsletterConfirmationLink::class);

pest()->use(RefreshDatabase::class);

const LINK_TOKEN = 'link-token';

/** A pending subscriber whose current confirmation token is LINK_TOKEN. */
function subscriberAwaitingConfirmation(): Subscriber
{
    $subscriber = Subscriber::factory()
        ->pending()
        ->create();
    $subscriber->verification_token_hash = hash('sha256', LINK_TOKEN);
    $subscriber->save();

    return $subscriber;
}

/** A link to the GET page or the POST confirmation, signed for a day unless told otherwise. */
function newsletterConfirmationLink(string $routeName, Subscriber $subscriber, string $token = LINK_TOKEN, ?DateTimeInterface $expires = null): string
{
    return URL::temporarySignedRoute($routeName, $expires ?? Date::now()->addDay(), [
        'subscriber' => $subscriber,
        'token' => $token,
    ]);
}

dataset('confirmation routes', [
    'confirmation page (GET)' => ['GET', 'newsletter.confirm'],
    'confirmation submit (POST)' => ['POST', 'newsletter.confirm.store'],
]);

test('a valid link reaches the confirmation page', function (): void {
    // Arrange
    $subscriber = subscriberAwaitingConfirmation();
    $url = newsletterConfirmationLink('newsletter.confirm', $subscriber);

    // Act
    $response = get($url);

    // Assert
    $response->assertOk()
        ->assertViewIs('pages.newsletter.confirm')
        ->assertSee($subscriber->email);
});

test('a valid link reaches the confirmation and confirms the subscriber', function (): void {
    // Arrange
    $subscriber = subscriberAwaitingConfirmation();
    $url = newsletterConfirmationLink('newsletter.confirm.store', $subscriber);

    // Act
    $response = post($url);

    // Assert
    $response->assertRedirect(route('newsletter.confirmed'));

    $subscriber->refresh();

    expect($subscriber->verified_at)->not->toBeNull()
        ->and($subscriber->verification_token_hash)
        ->toBeNull();
});

test('a link the middleware refuses goes back to the sign-up form and confirms nothing', function (string $method, string $routeName, Closure $link): void {
    // Arrange
    $subscriber = subscriberAwaitingConfirmation();
    $tokenHash = $subscriber->verification_token_hash;
    /** @var string $url */
    $url = $link($routeName, $subscriber);

    // Act
    $response = call($method, $url);

    // Assert
    $response->assertRedirect(route('home').'#newsletter')
        ->assertSessionHasErrorsIn('newsletter', ['email']);

    $subscriber->refresh();

    expect($subscriber->verified_at)->toBeNull()
        ->and($subscriber->verification_token_hash)
        ->toBe($tokenHash);
})->with('confirmation routes')
    ->with([
        'unsigned' => [fn (string $routeName, Subscriber $subscriber): string => route($routeName, ['subscriber' => $subscriber, 'token' => LINK_TOKEN])],
        'tampered signature' => [fn (string $routeName, Subscriber $subscriber): string => newsletterConfirmationLink($routeName, $subscriber).'0'],
        'expired' => [fn (string $routeName, Subscriber $subscriber): string => newsletterConfirmationLink($routeName, $subscriber, expires: Date::now()->subMinute())],
        'wrong token' => [fn (string $routeName, Subscriber $subscriber): string => newsletterConfirmationLink($routeName, $subscriber, 'another-token')],
    ]);

test('a link that was already used is refused', function (string $method, string $routeName): void {
    // Arrange
    $subscriber = subscriberAwaitingConfirmation();
    $url = newsletterConfirmationLink($routeName, $subscriber);
    $subscriber->verification_token_hash = null;
    $subscriber->save();

    // Act
    $response = call($method, $url);

    // Assert
    $response->assertRedirect(route('home').'#newsletter')
        ->assertSessionHasErrorsIn('newsletter', ['email']);

    $subscriber->refresh();

    expect($subscriber->verified_at)->toBeNull();
})->with('confirmation routes');

test('a link for a subscriber that no longer exists gets the same refusal', function (string $method, string $routeName): void {
    // Arrange
    $subscriber = subscriberAwaitingConfirmation();
    $url = newsletterConfirmationLink($routeName, $subscriber);
    $subscriber->delete();

    // Act
    $response = call($method, $url);

    // Assert
    $response->assertRedirect(route('home').'#newsletter')
        ->assertSessionHasErrorsIn('newsletter', ['email']);
})->with('confirmation routes');
