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

function exposedAdminDecorativeGlyphCountScript(): string
{
    return <<<'JS'
        (() => {
            const glyphs = ['✦', '✧', '✨', '📅', '✏️'];
            const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
            let count = 0;

            while (walker.nextNode()) {
                const parent = walker.currentNode.parentElement;

                if (! parent?.closest('[aria-hidden="true"]') && glyphs.some((glyph) => walker.currentNode.textContent.includes(glyph))) {
                    count++;
                }
            }

            return count;
        })()
        JS;
}

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
        ->assertScript(exposedAdminDecorativeGlyphCountScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

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
        visit($url)
            ->assertPathIs(parse_url($url, PHP_URL_PATH))
            ->assertVisible('.fi-main')
            ->assertScript('document.documentElement.classList.contains(\'dark\')', true)
            ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
            ->assertScript(exposedAdminDecorativeGlyphCountScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertScript(unexpectedAdminJavaScriptErrorCountScript(), 0);
    }
});
