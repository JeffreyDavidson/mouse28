<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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
    'podcast' => ['episodes.index', 'The Mouse28 Podcast'],
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

test('podcast pages describe episodes without audio as details instead of playable media', function (): void {
    $episode = Episode::factory()->create([
        'audio_url' => null,
        'audio_path' => null,
        'transcript' => null,
    ]);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('Episode details')
        ->assertDontSee('Listen now');

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('A transcript is not available for this episode.')
        ->assertDontSee('Transcript coming soon');
});

test('podcast index identifies episodes with hosted audio as playable', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('episodes/audio/available.mp3', 'audio');

    Episode::factory()->create([
        'audio_path' => 'episodes/audio/available.mp3',
    ]);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('Listen now')
        ->assertDontSee('Episode details');
});
