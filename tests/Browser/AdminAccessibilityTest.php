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
    $page->assertSee('active')
        ->assertSee('Unsubscribed')
        ->assertSee('Unknown')
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    // Act
    $page->resize(390, 844);

    // Assert
    $page->assertSee('total contacts')
        ->assertSee('Export all contacts')
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('newsletter contact table and pagination use the full available width', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.resend.key', 'resend-test-key');
    Http::fake(['https://api.resend.com/*' => Http::response(['data' => [
        ['email' => 'reader@example.com', 'unsubscribed' => false],
    ]])]);

    // Act
    $page = visit(NewsletterSubscribers::getUrl());

    foreach ([1440, 390, 320] as $width) {
        $page->resize($width, 1000);

        // Assert
        $page->assertScript(<<<'JS'
            (() => {
                const container = document.querySelector('[aria-label="Newsletter contacts"]');
                const table = document.querySelector('[aria-label="Newsletter contacts table"]');
                const pagination = container?.querySelector('.fi-pagination');

                if (! container || ! table || ! pagination) {
                    return false;
                }

                const containerBounds = container.getBoundingClientRect();
                const tableBounds = table.getBoundingClientRect();
                const paginationBounds = pagination.getBoundingClientRect();

                return tableBounds.width >= containerBounds.width - 4
                    && paginationBounds.top >= tableBounds.bottom - 1;
            })()
            JS, true)
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }
})->group('browser-smoke');

test('mobile admin user menu meets the minimum touch target', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());

    // Act
    $page = visit(PodcastSettings::getUrl());

    foreach ([390, 320] as $width) {
        $page->resize($width, 844);

        // Assert
        $page->assertScript(<<<'JS'
            (() => {
                const trigger = document.querySelector('.fi-topbar .fi-user-menu-trigger');

                if (! trigger) {
                    return false;
                }

                const bounds = trigger.getBoundingClientRect();

                return bounds.width >= 48 && bounds.height >= 48;
            })()
            JS, true)
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }
})->group('browser-smoke');

function undersizedFilamentTouchTargetsScript(): string
{
    return <<<'JS'
        (() => {
            const selectors = [
                '.fi-tabs-item',
                '.fi-ta-header-cell-sort-btn',
                '.fi-ta-header-toolbar .fi-icon-btn',
                '.fi-fo-rich-editor-tool',
                '.fi-fo-markdown-editor .editor-toolbar button',
                '.fi-select-input-btn',
                '.fi-pagination .fi-select-input',
                '.fi-ac-link-action',
            ];
            const controls = document.querySelectorAll(selectors.join(','));

            return [...controls].filter((control) => {
                const bounds = control.getBoundingClientRect();
                const styles = window.getComputedStyle(control);

                return bounds.width > 0
                    && bounds.height > 0
                    && styles.visibility !== 'hidden'
                    && ! control.closest('[aria-hidden="true"]')
                    && (bounds.width < 48 || bounds.height < 48);
            }).map((control) => {
                const bounds = control.getBoundingClientRect();

                return `${control.className} (${Math.round(bounds.width)}x${Math.round(bounds.height)})`;
            }).join('|');
        })()
        JS;
}

test('mobile resource status tabs scroll without overlapping labels', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());

    $pages = [
        PostResource::getUrl(),
        EpisodeResource::getUrl(),
        GuideResource::getUrl(),
    ];

    foreach ($pages as $url) {
        $page = visit($url);

        foreach ([390, 320] as $width) {
            $page->resize($width, 844);

            // Assert
            $page->assertScript(<<<'JS'
                (() => {
                    const tabs = document.querySelector('.fi-tabs');
                    const items = [...(tabs?.querySelectorAll('.fi-tabs-item') ?? [])];
                    const labels = items.map((item) => {
                        const label = item.querySelector('.fi-tabs-item-label') ?? item;
                        const range = document.createRange();
                        range.selectNodeContents(label);
                        const bounds = range.getBoundingClientRect();

                        return { left: bounds.left, right: bounds.right };
                    });

                    return items.length > 1
                        && tabs.scrollWidth > tabs.clientWidth
                        && labels.every((label, index) => index === 0 || label.left >= labels[index - 1].right);
                })()
                JS, true)
                ->assertScript($this->horizontalOverflowScript(), 0)
                ->assertNoAccessibilityIssues()
                ->assertNoJavaScriptErrors();
        }
    }
})->group('browser-smoke');

test('mobile table filter reset and checkbox labels meet the minimum touch target', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    Post::factory()->create();

    // Act
    $page = visit(PostResource::getUrl());

    foreach ([390, 320] as $width) {
        $page->resize($width, 844);
        $page->click('button[aria-label="Filter"]');

        // Assert
        $page->assertScript(<<<'JS'
            (() => {
                const panel = [...document.querySelectorAll('.fi-ta-filters-dropdown .fi-dropdown-panel')]
                    .find((element) => getComputedStyle(element).display !== 'none');

                if (! panel) {
                    return false;
                }

                const reset = panel.querySelector('.fi-ta-filters-header button');
                const checkboxLabels = [...panel.querySelectorAll('.fi-ta-filters .fi-fo-field-label:has(.fi-checkbox-input)')];

                return Boolean(reset)
                    && checkboxLabels.length > 0
                    && [reset, ...checkboxLabels].every((control) => {
                        const bounds = control.getBoundingClientRect();

                        return bounds.width >= 48 && bounds.height >= 48;
                    });
            })()
            JS, true)
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertNoJavaScriptErrors();
    }
})->group('browser-smoke');

test('table filter dropdown keeps its expanded state on the trigger button', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    Post::factory()->create();

    // Act
    $page = visit(PostResource::getUrl());
    $page->assertScript(<<<'JS'
        (() => {
            const dropdown = document.querySelector('.fi-ta-filters-dropdown');
            const wrapper = dropdown?.querySelector(':scope > .fi-dropdown-trigger');
            const trigger = wrapper?.querySelector('button');

            return Boolean(trigger)
                && ! wrapper.hasAttribute('aria-expanded')
                && trigger.getAttribute('aria-expanded') === 'false';
        })()
        JS, true);

    $page->click('button[aria-label="Filter"]');

    // Assert
    $page->assertScript(<<<'JS'
        (() => {
            const dropdown = document.querySelector('.fi-ta-filters-dropdown');
            const wrapper = dropdown?.querySelector(':scope > .fi-dropdown-trigger');
            const trigger = wrapper?.querySelector('button');
            const panel = dropdown?.querySelector('.fi-dropdown-panel');

            return Boolean(trigger && panel)
                && ! wrapper.hasAttribute('aria-expanded')
                && trigger.getAttribute('aria-expanded') === 'true'
                && trigger.getAttribute('aria-controls') === panel.id;
        })()
        JS, true)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    // Act
    $page->click('button[aria-label="Filter"]');

    // Assert
    $page->assertScript(<<<'JS'
        (() => document.querySelector('.fi-ta-filters-dropdown .fi-dropdown-trigger button')
            ?.getAttribute('aria-expanded') === 'false')()
        JS, true);
})->group('browser-smoke');

test('mobile Filament controls meet the minimum touch target across admin pages', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    Post::factory()->create(['published_at' => now()]);
    Episode::factory()->create();
    Guide::factory()->create();
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.resend.key', 'resend-test-key');
    Http::fake(['https://api.resend.com/*' => Http::response(['data' => [
        ['email' => 'reader@example.com', 'unsubscribed' => false],
    ]])]);

    ContactMessage::query()->create([
        'name' => 'Alex Example',
        'email' => 'alex@example.com',
        'subject' => 'accessibility',
        'message' => 'Could you share your accessibility planning tips?',
    ]);

    $pages = [
        [PostResource::getUrl(), '.fi-tabs-item'],
        [PostResource::getUrl(), '.fi-ta-header-cell-sort-btn'],
        [PostResource::getUrl(), '.fi-ta-header-toolbar .fi-icon-btn'],
        [EpisodeResource::getUrl(), '.fi-tabs-item'],
        [EpisodeResource::getUrl(), '.fi-ta-header-cell-sort-btn'],
        [GuideResource::getUrl(), '.fi-tabs-item'],
        [GuideResource::getUrl(), '.fi-ta-header-cell-sort-btn'],
        [ContactMessageResource::getUrl(), '.fi-ta-header-cell-sort-btn'],
        [ContactMessageResource::getUrl(), '.fi-ac-link-action'],
        [PostResource::getUrl('create'), '.fi-select-input-btn'],
        [PostResource::getUrl('create'), '.fi-fo-markdown-editor .editor-toolbar button'],
        [GuideResource::getUrl('create'), '.fi-fo-markdown-editor .editor-toolbar button'],
        [EpisodeResource::getUrl('create'), '.fi-fo-rich-editor-tool'],
        [NewsletterSubscribers::getUrl(), '.fi-pagination .fi-select-input'],
    ];

    foreach ($pages as [$url, $expectedControl]) {
        $page = visit($url);

        foreach ([390, 320] as $width) {
            $page->resize($width, 844);

            // Assert
            $page->assertScript(
                'document.querySelectorAll('.json_encode($expectedControl).').length > 0',
                true,
            )
                ->assertScript(undersizedFilamentTouchTargetsScript(), '')
                ->assertScript($this->horizontalOverflowScript(), 0)
                ->assertNoAccessibilityIssues()
                ->assertNoJavaScriptErrors();
        }
    }
})->group('browser-smoke');

function unexpectedAdminJavaScriptErrorCountScript(): string
{
    return <<<'JS'
        (() => (window.__pestBrowser?.jsErrors ?? []).filter((error) => (
            error.message !== 'ResizeObserver loop completed with undelivered notifications.'
        )).length)()
        JS;
}

test('dashboard remains readable with long content on desktop and mobile', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create(['name' => 'Alex Example']));
    Post::factory()->draft()->create([
        'title' => 'A sample story about making a family afternoon easier to plan',
    ]);

    // Act
    $page = visit(Dashboard::getUrl(panel: 'admin'));

    foreach ([1440, 390, 320] as $width) {
        $page->resize($width, 1000);

        // Assert
        $page->assertSee('A sample story about making a family afternoon easier to plan')
            ->assertSee('Quick Draft')
            ->assertScript('getComputedStyle(document.querySelector("h1")).fontFamily.includes("Besley")', true)
            ->assertScript('getComputedStyle(document.body).fontFamily.includes("Poppins")', true)
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }
})->group('browser-smoke');

test('admin login exposes no unnamed artwork or decorative glyphs', function (): void {
    visit(route('filament.admin.auth.login'))
        ->assertVisible('input[type="email"]')
        ->assertVisible('input[type="password"]')
        ->assertScript('document.documentElement.classList.contains(\'dark\')', false)
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
            ->assertScript('document.documentElement.classList.contains(\'dark\')', false)
            ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
            ->assertScript(browserDecorativeGlyphCountScript(['✦', '✧', '✨', '📅', '✏️']), 0)
            ->assertNoAccessibilityIssues()
            ->assertScript(unexpectedAdminJavaScriptErrorCountScript(), 0);
    }
});

test('dashboard Quick Draft loads without Livewire entanglement errors', function (): void {
    actingAs(User::factory()->admin()->create());

    visit(Dashboard::getUrl(panel: 'admin'))
        ->assertSee('Quick Draft')
        ->assertVisible('input[placeholder="Post title..."]')
        ->assertVisible('textarea[placeholder="Quick notes or ideas..."]')
        ->assertScript(unexpectedAdminJavaScriptErrorCountScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('episode resource header and table remain usable on narrow screens', function (): void {
    actingAs(User::factory()->admin()->create());
    Episode::factory()->create(['title' => 'Example Episode']);

    $page = visit(EpisodeResource::getUrl());

    foreach ([1440, 390] as $width) {
        $page->resize($width, 1000);

        $page->assertSee('Episodes')
            ->assertSee('New Episode')
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }
})->group('browser-smoke');
