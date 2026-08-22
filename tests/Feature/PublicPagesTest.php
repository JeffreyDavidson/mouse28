<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public index pages render', function (string $route, string $content): void {
    get(route($route))
        ->assertOk()
        ->assertSee($content);
})->with([
    'home' => ['home', 'Mouse28'],
    'about' => ['about', 'Our Story'],
    'blog' => ['blog.index', 'Blog'],
    'guides' => ['guides.index', 'Park Guides'],
    'podcast' => ['episodes.index', 'Listen Along'],
    'contact' => ['contact.show', 'Get in Touch'],
]);

test('published post detail page renders', function (): void {
    $post = Post::query()->create([
        'title' => 'An Accessible Day at the Parks',
        'slug' => 'accessible-day-at-the-parks',
        'excerpt' => 'A practical guide for planning a comfortable park day.',
        'body' => 'Start with a flexible plan and make room for sensory breaks.',
        'category' => 'park-accessibility',
        'author' => 'jeffrey',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('Start with a flexible plan', false);
});

test('published episode detail page renders', function (): void {
    $episode = Episode::query()->create([
        'title' => 'Planning a Sensory-Friendly Visit',
        'slug' => 'planning-a-sensory-friendly-visit',
        'description' => 'How our family prepares for a day in the parks.',
        'show_notes' => '<p>Our favorite planning strategies.</p>',
        'transcript' => '<p><strong>Jeffrey:</strong> Welcome to the show.</p>',
        'episode_number' => 1,
        'season_number' => 1,
        'duration_seconds' => 1800,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('Our favorite planning strategies', false);
});
