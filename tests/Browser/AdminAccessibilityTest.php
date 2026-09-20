<?php

use App\Filament\Pages\NewsletterSubscribers;
use App\Filament\Pages\PodcastSettings;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\ContactMessage;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

test('newsletter contact statuses remain readable on desktop and mobile', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    config()->set('services.resend.audience_id', 'audience-test-id');
    Http::fake(['https://api.resend.com/*' => Http::response(['data' => [
        ['email' => 'active@example.com', 'unsubscribed' => false],
        ['email' => 'left@example.com', 'unsubscribed' => true],
        ['email' => 'unknown@example.com'],
    ]])]);

    // Act
    $page = visit(NewsletterSubscribers::getUrl());
    $page->resize(1440, 1000);

    // Assert
    $page->assertSee('Active subscribers')
        ->assertSee('Unsubscribed')
        ->assertSee('Unknown')
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    // Act
    $page->resize(390, 844);

    // Assert
    $page->assertSee('Total contacts')
        ->assertSee('Export all contacts')
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

function unexpectedAdminJavaScriptErrorCountScript(): string
{
    return <<<'JS'
        (() => (window.__pestBrowser?.jsErrors ?? []).filter((error) => (
            error.message !== 'ResizeObserver loop completed with undelivered notifications.'
        )).length)()
        JS;
}

test('admin login exposes no unnamed artwork or decorative glyphs', function (): void {
    visit(route('filament.admin.auth.login'))
        ->assertVisible('input[type="email"]')
        ->assertVisible('input[type="password"]')
        ->assertScript('document.documentElement.classList.contains(\'dark\')', true)
        ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
        ->assertScript(browserDecorativeGlyphCountScript(['✦', '✧', '✨', '📅', '✏️']), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('admin login controls remain usable on narrow screens', function (): void {
    $page = visit(route('filament.admin.auth.login'));

    foreach ([320, 390] as $width) {
        $page->resize($width, 844);

        $page->assertScript($this->horizontalOverflowScript(), 0)
            ->assertScript($this->undersizedControlsScript(), '')
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }

    $page->click('button[aria-label="Show password"]');

    $page->assertVisible('input[type="text"]');

    $page->click('button[aria-label="Hide password"]');

    $page->assertVisible('input[type="password"]');
})->group('browser-smoke');

test('authenticated admin pages expose no unnamed artwork or decorative glyphs', function (): void {
    $user = User::factory()->admin()->create();
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create();
    $contactMessage = ContactMessage::query()->create([
        'name' => 'Park Guest',
        'email' => 'guest@example.com',
        'subject' => 'accessibility',
        'message' => 'Could you share your accessibility planning tips?',
    ]);

    actingAs($user);

    $urls = [
        Dashboard::getUrl(panel: 'admin'),
        NewsletterSubscribers::getUrl(),
        PodcastSettings::getUrl(),
        PostResource::getUrl(),
        PostResource::getUrl('create'),
        PostResource::getUrl('edit', ['record' => $post]),
        GuideResource::getUrl(),
        GuideResource::getUrl('create'),
        GuideResource::getUrl('edit', ['record' => $guide]),
        EpisodeResource::getUrl(),
        EpisodeResource::getUrl('create'),
        EpisodeResource::getUrl('edit', ['record' => $episode]),
        ContactMessageResource::getUrl(),
        ContactMessageResource::getUrl('view', ['record' => $contactMessage]),
    ];

    foreach ($urls as $url) {
        $path = parse_url($url, PHP_URL_PATH) ?: throw new UnexpectedValueException('The admin URL has no path.');

        visit($url)
            ->assertPathIs($path)
            ->assertVisible('.fi-main')
            ->assertScript('document.documentElement.classList.contains(\'dark\')', true)
            ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
            ->assertScript(browserDecorativeGlyphCountScript(['✦', '✧', '✨', '📅', '✏️']), 0)
            ->assertNoAccessibilityIssues()
            ->assertScript(unexpectedAdminJavaScriptErrorCountScript(), 0);
    }
});
