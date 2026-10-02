<?php

use App\Actions\HandleResendWebhook;
use App\Enums\SuppressionReason;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(HandleResendWebhook::class);

test('a permanent bounce suppresses the recipient', function (): void {
    $reader = Subscriber::factory()->create(['email' => 'reader@example.com']);

    $suppressed = app(HandleResendWebhook::class)->handle([
        'type' => 'email.bounced',
        'data' => ['to' => ['Reader@Example.com'], 'bounce' => ['type' => 'Permanent']],
    ]);

    expect($suppressed)->toBe(1)
        ->and($reader->refresh()->suppression_reason)->toBe(SuppressionReason::Bounced);
});

test('a transient bounce is ignored', function (): void {
    $reader = Subscriber::factory()->create();

    $suppressed = app(HandleResendWebhook::class)->handle([
        'type' => 'email.bounced',
        'data' => ['to' => [$reader->email], 'bounce' => ['type' => 'Transient']],
    ]);

    expect($suppressed)->toBe(0)
        ->and($reader->refresh()->isActive())->toBeTrue();
});

test('complaints and provider suppressions record their reason', function (string $type, SuppressionReason $reason): void {
    $reader = Subscriber::factory()->create();

    app(HandleResendWebhook::class)->handle(['type' => $type, 'data' => ['to' => [$reader->email]]]);

    expect($reader->refresh()->suppression_reason)->toBe($reason);
})->with([
    'complaint' => ['email.complained', SuppressionReason::Complained],
    'provider suppression' => ['email.suppressed', SuppressionReason::Bounced],
]);

test('every recipient of an event is suppressed and unknown ones are skipped', function (): void {
    $first = Subscriber::factory()->create();
    $second = Subscriber::factory()->create();

    $suppressed = app(HandleResendWebhook::class)->handle([
        'type' => 'email.complained',
        'data' => ['to' => [$first->email, 'stranger@example.com', $second->email]],
    ]);

    expect($suppressed)->toBe(2)
        ->and(Subscriber::query()->whereNotNull('suppressed_at')->count())->toBe(2);
});

test('other events and malformed payloads change nothing', function (array $event): void {
    $reader = Subscriber::factory()->create();

    $suppressed = app(HandleResendWebhook::class)->handle($event);

    expect($suppressed)->toBe(0)
        ->and($reader->refresh()->isActive())->toBeTrue();
})->with([
    'delivered' => [['type' => 'email.delivered', 'data' => ['to' => ['reader@example.com']]]],
    'no type' => [['data' => ['to' => ['reader@example.com']]]],
    'no recipients' => [['type' => 'email.complained', 'data' => []]],
    'recipients not a list' => [['type' => 'email.complained', 'data' => ['to' => 'reader@example.com']]],
    'non-string recipient' => [['type' => 'email.complained', 'data' => ['to' => [42, null]]]],
    'bounce without a type' => [['type' => 'email.bounced', 'data' => ['to' => ['reader@example.com']]]],
]);
