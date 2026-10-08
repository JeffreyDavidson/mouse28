<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('sitemap is valid and excludes unpublished content', function (): void {
    $post = Post::factory()->create();
    $draftPost = Post::factory()
        ->draft()
        ->create();
    $scheduledPost = Post::factory()
        ->scheduled()
        ->create();
    $guide = Guide::factory()->create();
    $draftGuide = Guide::factory()
        ->draft()
        ->create();
    $scheduledGuide = Guide::factory()
        ->scheduled()
        ->create();
    $episode = Episode::factory()->create();
    $draftEpisode = Episode::factory()
        ->draft()
        ->create();
    $scheduledEpisode = Episode::factory()
        ->scheduled()
        ->create();

    $sitemap = get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSeeHtml(route('blog.show', $post))
        ->assertSeeHtml(route('guides.show', $guide))
        ->assertSeeHtml(route('episodes.show', $episode))
        ->assertDontSee($draftPost->slug)
        ->assertDontSee($scheduledPost->slug)
        ->assertDontSee($draftGuide->slug)
        ->assertDontSee($scheduledGuide->slug)
        ->assertDontSee($draftEpisode->slug)
        ->assertDontSee($scheduledEpisode->slug);

    expect(simplexml_load_string($this->responseContent($sitemap)))->not->toBeFalse();
});

test('sitemap is served to crawlers without starting a session or setting cookies', function (): void {
    config()->set('session.driver', 'database');

    $response = get(route('sitemap'))
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie');

    expect($response->headers->getCookies())->toBeEmpty();
    assertDatabaseCount('sessions', 0);
});

test('sitemap renders the exact xml for a fixed set of content', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $publishedAt = Date::parse('2026-09-01 12:00:00');
    $olderPost = Post::factory()->create(['slug' => 'older-post', 'published_at' => Date::parse('2026-08-31 12:00:00'), 'updated_at' => Date::parse('2026-09-02 08:00:00')]);
    $tiedPost = Post::factory()->create(['slug' => 'tied-post', 'published_at' => $publishedAt, 'updated_at' => Date::parse('2026-09-03 09:15:30')]);
    $newerTiedPost = Post::factory()->create(['slug' => 'newer-tied-post', 'published_at' => $publishedAt, 'updated_at' => Date::parse('2026-09-04 10:00:00')]);
    $undatedPost = Post::factory()->create(['slug' => 'undated-post', 'published_at' => Date::parse('2026-08-30 12:00:00')]);
    DB::table('posts')
        ->where('id', $undatedPost->id)
        ->update(['updated_at' => null]);
    $episode = Episode::factory()->create(['slug' => 'first-episode', 'published_at' => $publishedAt, 'updated_at' => Date::parse('2026-09-05 11:00:00')]);
    $issue = NewsletterIssue::factory()->create(['slug' => 'first-issue', 'published_at' => $publishedAt, 'updated_at' => Date::parse('2026-09-06 12:00:00')]);
    $guide = Guide::factory()->create(['slug' => 'first-guide', 'published_at' => $publishedAt, 'updated_at' => Date::parse('2026-09-07 13:00:00')]);

    $response = get(route('sitemap'))->assertOk();

    $static = fn (string $routeName, string $priority): string => '<url><loc>'.route($routeName).'</loc><changefreq>weekly</changefreq><priority>'.$priority.'</priority></url>';
    $content = fn (string $loc, string $lastmod, string $priority): string => "<url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>monthly</changefreq><priority>{$priority}</priority></url>";

    expect($this->responseContent($response))->toBe(
        '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        .$static('home', '1.0')
        .$static('blog.index', '0.8')
        .$static('episodes.index', '0.8')
        .$static('newsletter.index', '0.8')
        .$static('about', '0.8')
        .$static('contact.create', '0.8')
        .$static('privacy', '0.8')
        .$static('guides.index', '0.8')
        .$content(route('blog.show', $newerTiedPost), '2026-09-04T10:00:00+00:00', '0.7')
        .$content(route('blog.show', $tiedPost), '2026-09-03T09:15:30+00:00', '0.7')
        .$content(route('blog.show', $olderPost), '2026-09-02T08:00:00+00:00', '0.7')
        .$content(route('episodes.show', $episode), '2026-09-05T11:00:00+00:00', '0.7')
        .$content(route('newsletter.issue', $issue), '2026-09-06T12:00:00+00:00', '0.6')
        .$content(route('guides.show', $guide), '2026-09-07T13:00:00+00:00', '0.8')
        .'</urlset>',
    );
});
