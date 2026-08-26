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

use function Pest\Laravel\actingAs;

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

test('admin login exposes no unnamed artwork or decorative glyphs', function (): void {
    visit('/admin/login')
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

    visit([
        '/admin',
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
    ])->assertScript('document.documentElement.classList.contains(\'dark\')', true)
        ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
        ->assertScript(exposedAdminDecorativeGlyphCountScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});
