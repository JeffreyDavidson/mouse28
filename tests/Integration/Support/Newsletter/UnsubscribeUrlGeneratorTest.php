<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use JeffreyDavidson\CreatorKit\Presenters\SubscriberPresenter;

use function Pest\Laravel\travel;

pest()->use(RefreshDatabase::class);

test('a reader gets a signed unsubscribe link that never expires', function (): void {
    $reader = Subscriber::factory()->create();

    $url = SubscriberPresenter::from($reader)->unsubscribeUrl();
    travel(2)->years();

    expect(parse_url($url, PHP_URL_PATH))->toBe("/newsletter/unsubscribe/{$reader->id}")
        ->and(Request::create($url)->hasValidSignature())
        ->toBeTrue()
        ->and($url)
        ->not->toContain('expires=');
});

test('the link only works for the reader it was made for', function (): void {
    $reader = Subscriber::factory()->create();
    $other = Subscriber::factory()->create();

    $url = SubscriberPresenter::from($reader)->unsubscribeUrl();
    $tampered = str_replace("/unsubscribe/{$reader->id}", "/unsubscribe/{$other->id}", $url);

    expect(Request::create($tampered)->hasValidSignature())->toBeFalse();
});
