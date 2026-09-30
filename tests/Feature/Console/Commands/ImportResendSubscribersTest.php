<?php

use App\Console\Commands\ImportResendSubscribers;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\assertDatabaseCount;

covers(ImportResendSubscribers::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.resend.enabled', true);
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.resend.key', 'resend-test-key');
    Cache::forget('newsletter_subscribers');
});

function fakeResendContacts(): void
{
    Http::fake(['https://api.resend.com/*' => Http::response(['data' => [
        ['email' => 'active@example.test', 'unsubscribed' => false],
        ['email' => 'second@example.test', 'unsubscribed' => false],
        ['email' => 'left@example.test', 'unsubscribed' => true],
    ]])]);
}

test('the import command is a dry run unless told to apply', function (): void {
    fakeResendContacts();

    pendingCommand('newsletter:import-resend-subscribers')
        ->expectsOutputToContain('Dry run')
        ->expectsOutputToContain('Would import: 2')
        ->expectsOutputToContain('Skipped, unsubscribed in Resend: 1')
        ->assertSuccessful();

    assertDatabaseCount('subscribers', 0);
});

test('the import command applies with the apply option', function (): void {
    fakeResendContacts();

    pendingCommand('newsletter:import-resend-subscribers', ['--apply' => true])
        ->expectsOutputToContain('Imported: 2')
        ->assertSuccessful();

    expect(Subscriber::query()->active()->count())->toBe(2);
});

test('the import command reports contacts that are already stored', function (): void {
    fakeResendContacts();
    Subscriber::factory()->create(['email' => 'active@example.test']);

    pendingCommand('newsletter:import-resend-subscribers', ['--apply' => true])
        ->expectsOutputToContain('Imported: 1')
        ->expectsOutputToContain('Already stored: 1')
        ->assertSuccessful();
});

test('the import command fails without writing when Resend cannot be read', function (int $status): void {
    Http::fake(['https://api.resend.com/*' => Http::response([], $status)]);

    pendingCommand('newsletter:import-resend-subscribers', ['--apply' => true])
        ->expectsOutputToContain('Failed to fetch subscribers from Resend API')
        ->assertFailed();

    assertDatabaseCount('subscribers', 0);
})->with([
    'server error' => [503],
    'rejected key' => [401],
]);

test('the import command fails when the Resend integration is disabled', function (): void {
    config()->set('services.resend.enabled', false);
    Http::fake();

    pendingCommand('newsletter:import-resend-subscribers', ['--apply' => true])
        ->expectsOutputToContain('The Resend integration is disabled.')
        ->assertFailed();

    Http::assertNothingSent();
});
