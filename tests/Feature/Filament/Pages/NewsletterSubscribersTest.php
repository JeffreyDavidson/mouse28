<?php

use App\Filament\Pages\NewsletterSubscribers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.resend.key', 'resend-test-key');
    Cache::forget('newsletter_subscribers');
});

test('newsletter reporting separates subscription status from total contacts', function (): void {
    // Arrange
    Http::fake(['https://api.resend.com/*' => Http::response(['data' => [
        ['email' => 'active@example.com', 'unsubscribed' => false],
        ['email' => 'left@example.com', 'unsubscribed' => true],
        ['email' => 'unknown@example.com'],
        ['email' => 'malformed@example.com', 'unsubscribed' => 'false'],
    ]])]);
    actingAs(User::factory()->admin()->create());

    // Act
    $page = livewire(NewsletterSubscribers::class);
    $audience = $page->instance()->getAudience();

    // Assert
    $page->assertSee('Active subscribers')
        ->assertSee('Total contacts')
        ->assertSee('Unsubscribed')
        ->assertSee('Unknown')
        ->assertSee('left@example.com');
    expect($audience['active_count'])->toBe(1)
        ->and(array_column($audience['subscribers'], 'subscription_status'))
        ->toBe(['Subscribed', 'Unsubscribed', 'Unknown', 'Unknown']);
    Http::assertSentCount(1);
});

test('newsletter CSV preserves unsubscribed and unknown contacts with explicit status', function (): void {
    // Arrange
    Http::fake(['https://api.resend.com/*' => Http::response(['data' => [
        ['email' => 'active@example.com', 'unsubscribed' => false],
        ['email' => 'left@example.com', 'unsubscribed' => true],
        ['email' => 'unknown@example.com'],
    ]])]);
    actingAs(User::factory()->admin()->create());
    $page = livewire(NewsletterSubscribers::class);

    // Act
    $page->call('exportCsv');

    // Assert
    $page->assertFileDownloaded(
        'newsletter-subscribers-'.now()->format('Y-m-d').'.csv',
        "Email,\"Created At\",Status\nactive@example.com,,Subscribed\nleft@example.com,,Unsubscribed\nunknown@example.com,,Unknown\n",
        'text/csv',
    );
});

test('the subscriber page distinguishes provider errors from an empty audience', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response([], 503),
    ]);
    actingAs(User::factory()->admin()->create());

    get(NewsletterSubscribers::getUrl())
        ->assertOk()
        ->assertSee('Failed to fetch subscribers from Resend API (HTTP 503).')
        ->assertDontSee('No contacts yet');

    Http::assertSentCount(1);
});

test('an administrator can explicitly refresh cached subscribers', function (): void {
    Http::fakeSequence()
        ->push(['data' => [['email' => 'first@example.com']]])
        ->push(['data' => [['email' => 'second@example.com']]]);
    actingAs(User::factory()->admin()->create());

    Livewire::test(NewsletterSubscribers::class)
        ->assertSee('first@example.com')
        ->call('refreshSubscribers')
        ->assertNotified()
        ->assertSee('second@example.com');

    Http::assertSentCount(2);
});

test('subscriber exports neutralize spreadsheet formulas', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response([
            'data' => [['email' => '=2+2', 'created_at' => '@unsafe']],
        ]),
    ]);
    actingAs(User::factory()->admin()->create());

    Livewire::test(NewsletterSubscribers::class)
        ->call('exportCsv')
        ->assertFileDownloaded(
            'newsletter-subscribers-'.now()->format('Y-m-d').'.csv',
            "Email,\"Created At\",Status\n'=2+2,'@unsafe,Unknown\n",
            'text/csv',
        );
});

test('authenticated user can render newsletter subscribers', function (): void {
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.resend.key', 'resend-test-key');
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => []]),
    ]);

    $user = User::factory()->admin()->create();

    actingAs($user)
        ->get(NewsletterSubscribers::getUrl())
        ->assertOk()
        ->assertSee('Newsletter Subscribers')
        ->assertSee('No contacts yet')
        ->assertSee('audience <span aria-hidden="true">✨</span>', false);
});
