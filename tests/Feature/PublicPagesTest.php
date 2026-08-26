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

test('homepage uses one newsletter form and responsive hero artwork', function (): void {
    $response = get(route('home'))
        ->assertOk()
        ->assertSee('/images/hero-family-640.webp 640w', false)
        ->assertSee('/images/hero-family-1024.webp 1024w', false)
        ->assertSee('We use your email to send Mouse28 updates.');

    expect(substr_count($response->getContent(), 'action="'.route('newsletter.store').'"'))->toBe(1);
});

test('homepage defers the below-fold featured post image', function (): void {
    Post::factory()->create([
        'cover_image' => 'posts/featured.webp',
    ]);

    $response = get(route('home'))
        ->assertOk();

    expect($response->getContent())->toMatch(
        '/<img[^>]*src="\/storage\/posts\/featured\.webp"[^>]*loading="lazy"[^>]*decoding="async"[^>]*>/',
    );
});

test('public forms use readable placeholder text colors', function (): void {
    Post::factory()->create();

    get(route('blog.index'))
        ->assertOk()
        ->assertSee('placeholder:text-navy/60', false)
        ->assertSee('placeholder:text-white/60', false)
        ->assertDontSee('placeholder:text-navy/25', false)
        ->assertDontSee('placeholder:text-white/25', false)
        ->assertDontSee('placeholder-white/', false);

    get(route('contact.show'))
        ->assertOk()
        ->assertSee('placeholder:text-cream/60', false)
        ->assertDontSee('placeholder:text-cream/30', false);
});

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
        ->assertSee('Start with a flexible plan', false)
        ->assertSee('id="back-to-top"', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertSee('tabindex="-1"', false)
        ->assertSee('inline-flex size-12 items-center justify-center rounded-full', false)
        ->assertDontSee('inline-flex size-11', false);
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
        'audio_url' => 'https://cdn.example.com/planning-a-sensory-friendly-visit.mp3',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('Our favorite planning strategies', false)
        ->assertSee('Listen to this episode')
        ->assertDontSee('Now Playing')
        ->assertSee('aria-label="Play Planning a Sensory-Friendly Visit"', false)
        ->assertSee('id="episode-transcript"', false)
        ->assertSee('aria-controls="episode-transcript"', false)
        ->assertSee(':aria-expanded="expanded.toString()"', false);
});

test('empty podcast page hides its decorative player preview from assistive technology', function (): void {
    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('id="podcast-player-preview" class="hidden md:block" aria-hidden="true"', false);
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
