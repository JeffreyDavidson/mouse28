<?php

use App\Actions\RequestNewsletterSubscription;
use App\Mail\NewsletterConfirmationMail;
use App\Models\Subscriber;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

pest()->use(RefreshDatabase::class);

covers(RequestNewsletterSubscription::class);

function queuedConfirmationUrl(): string
{
    $url = '';

    Mail::assertQueued(NewsletterConfirmationMail::class, function (NewsletterConfirmationMail $mail) use (&$url): bool {
        $url = $mail->confirmationUrl;

        return true;
    });

    return $url;
}

beforeEach(function (): void {
    Mail::fake();
});

test('a new sign-up is stored pending and its confirmation is queued', function (): void {
    app(RequestNewsletterSubscription::class)->handle('Reader@Example.com');

    $subscriber = Subscriber::query()->sole();

    expect($subscriber->email)->toBe('reader@example.com')
        ->and($subscriber->subscribed_at)
        ->not->toBeNull()
        ->and($subscriber->verified_at)
        ->toBeNull()
        ->and($subscriber->unsubscribed_at)
        ->toBeNull()
        ->and($subscriber->verification_token_hash)
        ->toHaveLength(64);

    Mail::assertQueued(
        NewsletterConfirmationMail::class,
        fn (NewsletterConfirmationMail $mail): bool => $mail->hasTo('reader@example.com')
            && URL::hasValidSignature(Request::create($mail->confirmationUrl)),
    );
});

test('the confirmation link expires after one day', function (): void {
    app(RequestNewsletterSubscription::class)->handle('reader@example.com');

    $url = queuedConfirmationUrl();

    expect(URL::hasValidSignature(Request::create($url)))->toBeTrue();

    $this->travel(25)
        ->hours();

    expect(URL::hasValidSignature(Request::create($url)))->toBeFalse();
});

test('the stored token is the hash of the emailed token', function (): void {
    app(RequestNewsletterSubscription::class)->handle('reader@example.com');

    $url = queuedConfirmationUrl();
    $token = Str::afterLast(Str::before($url, '?'), '/');

    expect(Subscriber::query()
        ->sole()
        ->verification_token_hash)->toBe(hash('sha256', $token));
});

test('an active reader is not restarted', function (): void {
    $subscribedAt = Date::now()
        ->subMonth()
        ->startOfSecond();
    $verifiedAt = Date::now()
        ->subMonth()
        ->addMinute()
        ->startOfSecond();
    $reader = Subscriber::factory()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => $subscribedAt,
        'verified_at' => $verifiedAt,
    ]);

    app(RequestNewsletterSubscription::class)->handle('Reader@Example.com');

    $reader->refresh();

    expect($reader->subscribed_at?->equalTo($subscribedAt))->toBeTrue()
        ->and($reader->verified_at?->equalTo($verifiedAt))
        ->toBeTrue()
        ->and($reader->verification_token_hash)
        ->toBeNull();

    Mail::assertNothingQueued();
});

test('an unsubscribed reader can start confirmation again', function (): void {
    $reader = Subscriber::factory()
        ->unsubscribed()
        ->create(['email' => 'reader@example.com']);

    app(RequestNewsletterSubscription::class)->handle('reader@example.com');

    $reader->refresh();

    expect($reader->subscribed_at->isToday())->toBeTrue()
        ->and($reader->verified_at)
        ->toBeNull()
        ->and($reader->unsubscribed_at)
        ->toBeNull()
        ->and($reader->verification_token_hash)
        ->not->toBeNull();

    Mail::assertQueued(NewsletterConfirmationMail::class, 1);
});

test('a pending confirmation link is kept during the email cooldown', function (): void {
    $action = app(RequestNewsletterSubscription::class);
    $action->handle('reader@example.com');

    $reader = Subscriber::query()->sole();
    $tokenHash = $reader->verification_token_hash;
    $subscribedAt = $reader->subscribed_at;

    $action->handle('READER@example.com');

    expect($reader->refresh()
        ->verification_token_hash)->toBe($tokenHash)
        ->and($reader->subscribed_at?->equalTo($subscribedAt))
        ->toBeTrue();

    Mail::assertQueued(NewsletterConfirmationMail::class, 1);
});

test('a pending confirmation can be requested again once the cooldown ends', function (): void {
    $action = app(RequestNewsletterSubscription::class);
    $action->handle('reader@example.com');
    $tokenHash = Subscriber::query()
        ->sole()
        ->verification_token_hash;

    $this->travel(901)
        ->seconds();
    $action->handle('reader@example.com');

    expect(Subscriber::query()
        ->sole()
        ->verification_token_hash)->not->toBe($tokenHash);

    Mail::assertQueued(NewsletterConfirmationMail::class, 2);
});

test('the cooldown is dropped when queueing the confirmation fails', function (): void {
    $pendingMail = Mockery::mock(PendingMail::class);
    $pendingMail->shouldReceive('queue')
        ->once()
        ->andThrow(new RuntimeException('mail transport unavailable'));

    $mailer = Mockery::mock(Mailer::class);
    $mailer->shouldReceive('to')
        ->with('reader@example.com')
        ->once()
        ->andReturn($pendingMail);
    app()->instance(Mailer::class, $mailer);

    expect(fn () => app(RequestNewsletterSubscription::class)->handle('reader@example.com'))
        ->toThrow(RuntimeException::class, 'mail transport unavailable')
        ->and(Cache::has('newsletter.confirmation.cooldown.'.hash('sha256', 'reader@example.com')))
        ->toBeFalse();
});

test('a suppressed address is left alone and gets no email', function (): void {
    $reader = Subscriber::factory()
        ->suppressed()
        ->create(['email' => 'reader@example.com']);

    app(RequestNewsletterSubscription::class)->handle('Reader@Example.com');

    $reader->refresh();

    expect($reader->isSuppressed())->toBeTrue()
        ->and($reader->unsubscribed_at)
        ->not->toBeNull()
        ->and($reader->verification_token_hash)
        ->toBeNull();

    Mail::assertNothingQueued();
});
