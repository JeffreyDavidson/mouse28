<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

test('blog feed is valid and excludes unpublished content', function (): void {
    $post = Post::factory()->create();
    $draftPost = Post::factory()
        ->draft()
        ->create();
    $scheduledPost = Post::factory()
        ->scheduled()
        ->create();

    $blogFeed = get(route('rss.blog'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml')
        ->assertSee($post->title)
        ->assertDontSee($draftPost->title)
        ->assertDontSee($scheduledPost->title);

    expect(simplexml_load_string($this->responseContent($blogFeed)))->not->toBeFalse();
});

test('blog feed is served to feed readers without starting a session or setting cookies', function (): void {
    config()->set('session.driver', 'database');

    $response = get(route('rss.blog'))
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie');

    expect($response->headers->getCookies())->toBeEmpty();
    assertDatabaseCount('sessions', 0);
});

test('blog feed renders this exact xml for a fixed set of posts', function (): void {
    travelTo(Date::parse('2026-10-07 12:00:00'));
    $category = Category::factory()->create(['name' => 'Food & Drink']);
    $tips = Post::factory()
        ->for($category)
        ->create([
            'title' => 'Tips & "tricks" for \'parks\'',
            'excerpt' => 'Quiet <spots> & shade.',
            'published_at' => Date::parse('2026-10-05 08:30:00'),
        ]);
    $arrival = Post::factory()->create([
        'title' => 'Arrival day',
        'excerpt' => null,
        'content' => "## Arrival\n\n**Plan** a flexible arrival.",
        'category_id' => null,
        'published_at' => Date::parse('2026-10-01 09:00:00'),
    ]);

    $response = get(route('rss.blog'))->assertOk();

    expect($this->responseContent($response))->toBe(implode("\n", [
        '<?xml version="1.0" encoding="UTF-8"?>',
        '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
        '<channel>',
        '<title>Mouse28 Blog</title>',
        '<link>'.route('blog.index').'</link>',
        '<description>Disney parks through the eyes of a family raising a daughter with autism. Practical tips and stories.</description>',
        '<language>en-us</language>',
        '<lastBuildDate>Mon, 05 Oct 2026 08:30:00 +0000</lastBuildDate>',
        '<atom:link href="'.route('rss.blog').'" rel="self" type="application/rss+xml" />',
        '<image><url>'.url('/images/logo.jpg').'</url><title>Mouse28</title><link>'.route('home').'</link></image>',
        '<item>',
        '<title>Tips &amp; "tricks" for \'parks\'</title>',
        '<link>'.route('blog.show', $tips).'</link>',
        '<guid isPermaLink="true">'.route('blog.show', $tips).'</guid>',
        '<description>Quiet &lt;spots&gt; &amp; shade.</description>',
        '<pubDate>Mon, 05 Oct 2026 08:30:00 +0000</pubDate>',
        '<category>Food &amp; Drink</category>',
        '</item>',
        '<item>',
        '<title>Arrival day</title>',
        '<link>'.route('blog.show', $arrival).'</link>',
        '<guid isPermaLink="true">'.route('blog.show', $arrival).'</guid>',
        '<description>Arrival Plan a flexible arrival.</description>',
        '<pubDate>Thu, 01 Oct 2026 09:00:00 +0000</pubDate>',
        '</item>',
        '</channel>',
        '</rss>',
    ]));
});
