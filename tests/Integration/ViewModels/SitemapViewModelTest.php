<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\ViewModels\SitemapViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

covers(SitemapViewModel::class);

/** The sitemap's locations, one per line, so a slug can be searched for anywhere in them. */
function sitemapLocations(): string
{
    return implode("\n", array_column(app(SitemapViewModel::class)->data()['urls'], 'loc'));
}

test('sitemap includes published content links and excludes unpublished records', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $draftPost = Post::factory()
        ->draft()
        ->create();
    $draftGuide = Guide::factory()
        ->draft()
        ->create();

    $content = sitemapLocations();

    expect($content)
        ->toContain(route('blog.show', $post))
        ->toContain(route('guides.show', $guide))
        ->not->toContain($draftPost->slug)
        ->not->toContain($draftGuide->slug);
});

test('sitemap omits guide routes and records when guides are disabled', function (): void {
    config()->set('mouse28.guides_enabled', false);
    $guide = Guide::factory()->create();

    $content = sitemapLocations();

    expect($content)
        ->not->toContain(route('guides.index'))
        ->not->toContain(route('guides.show', $guide));
});

test('sitemap lists the newsletter archive and only live issues', function (): void {
    $live = NewsletterIssue::factory()->create();
    $draft = NewsletterIssue::factory()
        ->draft()
        ->create();
    $scheduled = NewsletterIssue::factory()
        ->scheduled()
        ->create();

    $content = sitemapLocations();

    expect($content)
        ->toContain(route('newsletter.index'))
        ->toContain(route('newsletter.issue', $live))
        ->not->toContain($draft->slug)
        ->not->toContain($scheduled->slug);
});

test('sitemap data lists static pages weekly then content monthly with its modification date and priority', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $post = Post::factory()->create(['updated_at' => Date::parse('2026-09-01 08:00:00')]);
    $episode = Episode::factory()->create(['updated_at' => Date::parse('2026-09-02 08:00:00')]);
    $issue = NewsletterIssue::factory()->create(['updated_at' => Date::parse('2026-09-03 08:00:00')]);
    $guide = Guide::factory()->create(['updated_at' => Date::parse('2026-09-04 08:00:00')]);
    $static = fn (string $routeName, string $priority): array => ['loc' => route($routeName), 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => $priority];
    $content = fn (string $loc, string $lastmod, string $priority): array => ['loc' => $loc, 'lastmod' => $lastmod, 'changefreq' => 'monthly', 'priority' => $priority];

    $urls = array_map(
        fn (array $url): array => [...$url, 'lastmod' => $url['lastmod']?->toW3cString()],
        app(SitemapViewModel::class)->data()['urls'],
    );

    expect($urls)->toBe([
        $static('home', '1.0'),
        $static('blog.index', '0.8'),
        $static('episodes.index', '0.8'),
        $static('newsletter.index', '0.8'),
        $static('about', '0.8'),
        $static('contact.create', '0.8'),
        $static('privacy', '0.8'),
        $static('guides.index', '0.8'),
        $content(route('blog.show', $post), '2026-09-01T08:00:00+00:00', '0.7'),
        $content(route('episodes.show', $episode), '2026-09-02T08:00:00+00:00', '0.7'),
        $content(route('newsletter.issue', $issue), '2026-09-03T08:00:00+00:00', '0.6'),
        $content(route('guides.show', $guide), '2026-09-04T08:00:00+00:00', '0.8'),
    ]);
});

test('sitemap data leaves out content without a modification date', function (Post|Episode|NewsletterIssue|Guide $record): void {
    config()->set('mouse28.guides_enabled', true);
    DB::table($record->getTable())
        ->where('id', $record->id)
        ->update(['updated_at' => null]);

    expect(sitemapLocations())->not->toContain($record->slug);
})->with([
    'posts' => fn (): Post => Post::factory()->create(),
    'episodes' => fn (): Episode => Episode::factory()->create(),
    'newsletter issues' => fn (): NewsletterIssue => NewsletterIssue::factory()->create(),
    'guides' => fn (): Guide => Guide::factory()->create(),
]);
