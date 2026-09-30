<?php

use App\Http\Controllers\NewsletterUnsubscriptionController;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;

covers(NewsletterUnsubscriptionController::class);

pest()->use(RefreshDatabase::class);

test('the unsubscribe page asks for a click and does not unsubscribe on its own', function (): void {
    $reader = Subscriber::factory()->create();
    $url = URL::signedRoute('newsletter.unsubscribe', $reader);

    get($url)
        ->assertOk()
        ->assertViewIs('pages.newsletter.unsubscribe')
        ->assertSee($reader->email)
        ->assertSee('Unsubscribe')
        ->assertSeeHtml('action="'.e(URL::signedRoute('newsletter.unsubscribe.store', $reader)).'"')
        ->assertSeeHtml('<meta name="robots" content="noindex,nofollow">');

    expect($reader->refresh()->isActive())->toBeTrue();
});

test('unsubscribing deactivates the reader and returns to the newsletter sign-up', function (): void {
    $reader = Subscriber::factory()->create();

    delete(URL::signedRoute('newsletter.unsubscribe.store', $reader))
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_success', 'You have been unsubscribed.');

    expect($reader->refresh()->isActive())->toBeFalse()
        ->and($reader->unsubscribed_at)->not->toBeNull();
});

test('unsigned unsubscribe requests are refused', function (): void {
    $reader = Subscriber::factory()->create();

    get(route('newsletter.unsubscribe', $reader))->assertForbidden();
    delete(route('newsletter.unsubscribe.store', $reader))->assertForbidden();

    expect($reader->refresh()->isActive())->toBeTrue();
});
