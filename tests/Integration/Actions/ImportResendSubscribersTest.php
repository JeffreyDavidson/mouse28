<?php

use App\Actions\ImportResendSubscribers;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\assertDatabaseCount;

covers(ImportResendSubscribers::class);

pest()->use(RefreshDatabase::class);

test('only contacts still subscribed in Resend are imported as confirmed readers', function (): void {
    $summary = app(ImportResendSubscribers::class)->handle([
        ['email' => 'active@example.test', 'unsubscribed' => false, 'created_at' => '2026-01-15T10:30:00.000Z'],
        ['email' => 'left@example.test', 'unsubscribed' => true],
        ['email' => 'unknown@example.test'],
        ['email' => 'malformed@example.test', 'unsubscribed' => 'false'],
    ], apply: true);

    $reader = Subscriber::query()->sole();

    expect($summary)->toBe(['imported' => 1, 'existing' => 0, 'unsubscribed' => 1, 'invalid' => 2])
        ->and($reader->email)->toBe('active@example.test')
        ->and($reader->isActive())->toBeTrue()
        ->and($reader->subscribed_at?->toDateTimeString())->toBe('2026-01-15 10:30:00')
        ->and($reader->verified_at?->toDateTimeString())->toBe('2026-01-15 10:30:00')
        ->and($reader->verification_token_hash)->toBeNull();
});

test('a dry run counts what would be imported and writes nothing', function (): void {
    $summary = app(ImportResendSubscribers::class)->handle([
        ['email' => 'active@example.test', 'unsubscribed' => false],
    ], apply: false);

    expect($summary['imported'])->toBe(1);
    assertDatabaseCount('subscribers', 0);
});

test('addresses are trimmed and lowercased and Resend duplicates collapse', function (): void {
    $summary = app(ImportResendSubscribers::class)->handle([
        ['email' => '  Reader@Example.TEST ', 'unsubscribed' => false],
        ['email' => 'reader@example.test', 'unsubscribed' => false],
    ], apply: true);

    expect($summary['imported'])->toBe(1)
        ->and(Subscriber::query()->sole()->email)->toBe('reader@example.test');
});

test('invalid addresses and non-string emails are skipped', function (mixed $email): void {
    $summary = app(ImportResendSubscribers::class)->handle([
        ['email' => $email, 'unsubscribed' => false],
    ], apply: true);

    expect($summary)->toBe(['imported' => 0, 'existing' => 0, 'unsubscribed' => 0, 'invalid' => 1]);
    assertDatabaseCount('subscribers', 0);
})->with([
    'not an address' => ['not-an-email'],
    'empty' => [''],
    'missing' => [null],
    'number' => [42],
]);

test('existing readers are never changed by the import', function (): void {
    $active = Subscriber::factory()->create(['email' => 'active@example.test']);
    $pending = Subscriber::factory()->pending()->create(['email' => 'pending@example.test']);
    $unsubscribed = Subscriber::factory()->unsubscribed()->create(['email' => 'left@example.test']);
    $before = Subscriber::query()->orderBy('id')->get()->map->getAttributes()->all();

    $summary = app(ImportResendSubscribers::class)->handle([
        ['email' => $active->email, 'unsubscribed' => false],
        ['email' => $pending->email, 'unsubscribed' => false],
        ['email' => $unsubscribed->email, 'unsubscribed' => false],
    ], apply: true);

    expect($summary)->toBe(['imported' => 0, 'existing' => 3, 'unsubscribed' => 0, 'invalid' => 0])
        ->and(Subscriber::query()->orderBy('id')->get()->map->getAttributes()->all())->toBe($before);
});

test('running the import twice imports nothing the second time', function (): void {
    $contacts = [['email' => 'active@example.test', 'unsubscribed' => false]];

    app(ImportResendSubscribers::class)->handle($contacts, apply: true);
    $summary = app(ImportResendSubscribers::class)->handle($contacts, apply: true);

    expect($summary['imported'])->toBe(0)
        ->and($summary['existing'])->toBe(1);
    assertDatabaseCount('subscribers', 1);
});

test('a missing or unreadable sign-up date falls back to the import time', function (mixed $createdAt): void {
    Date::setTestNow('2026-10-01 12:00:00');

    app(ImportResendSubscribers::class)->handle([
        ['email' => 'active@example.test', 'unsubscribed' => false, 'created_at' => $createdAt],
    ], apply: true);

    expect(Subscriber::query()->sole()->subscribed_at?->toDateTimeString())->toBe('2026-10-01 12:00:00');
})->with([
    'missing' => [null],
    'unreadable' => ['not a date'],
    'not text' => [12345],
]);
