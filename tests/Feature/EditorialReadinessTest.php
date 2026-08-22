<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use App\Support\EditorialReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('publication status distinguishes drafts schedules and missing dates', function (): void {
    $draft = Post::factory()->draft()->create();
    $missingDate = Post::factory()->create(['published_at' => null]);
    $scheduled = Post::factory()->scheduled()->create();
    $published = Post::factory()->create();

    expect(EditorialReadiness::status($draft))->toBe('Draft')
        ->and(EditorialReadiness::status($missingDate))->toBe('Needs publish date')
        ->and(EditorialReadiness::status($scheduled))->toBe('Scheduled')
        ->and(EditorialReadiness::status($published))->toBe('Published');
});

test('readiness reports actionable issues for each content type', function (): void {
    $post = Post::factory()->create([
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);
    $guide = Guide::factory()->create([
        'source_url' => null,
        'last_reviewed_at' => null,
    ]);
    $episode = Episode::factory()->create([
        'audio_url' => null,
        'transcript' => null,
    ]);

    expect(EditorialReadiness::label($post))->toBe('3 missing')
        ->and(EditorialReadiness::issues($post))->toContain('Add a cover image')
        ->and(EditorialReadiness::issues($guide))->toContain('Add an official source', 'Set the review date')
        ->and(EditorialReadiness::issues($episode))->not->toContain('Add audio', 'Add a transcript');
});

test('episode readiness does not require deferred audio or transcripts', function (): void {
    $episode = Episode::factory()->create([
        'audio_path' => null,
        'audio_url' => null,
        'transcript' => null,
        'cover_image' => 'episodes/complete.jpg',
        'meta_title' => 'A complete episode title',
        'meta_description' => 'A complete episode description for search and social sharing.',
    ]);

    expect(EditorialReadiness::issues($episode))->toBeEmpty();
});

test('editorial scopes separate the content work queue', function (): void {
    $draft = Post::factory()->draft()->create();
    $scheduled = Post::factory()->scheduled()->create();
    $published = Post::factory()->create([
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'Complete title',
        'meta_description' => 'Complete description',
    ]);
    $needsAttention = Post::factory()->create(['cover_image' => null]);

    expect(Post::drafts()->pluck('id'))->toContain($draft->id)
        ->and(Post::scheduled()->pluck('id'))->toContain($scheduled->id)
        ->and(Post::published()->pluck('id'))->toContain($published->id, $needsAttention->id)
        ->and(Post::needsAttention()->pluck('id'))->toContain($draft->id, $scheduled->id, $needsAttention->id)
        ->and(Post::needsAttention()->pluck('id'))->not->toContain($published->id);
});

test('complete content is marked ready', function (): void {
    $post = Post::factory()->create([
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'A complete park-planning post',
        'meta_description' => 'A complete description for search and social sharing.',
    ]);

    expect(EditorialReadiness::issues($post))->toBeEmpty()
        ->and(EditorialReadiness::label($post))->toBe('Ready')
        ->and(EditorialReadiness::summary($post))->toBe('Ready to publish.');
});

test('sourced posts require a matching official source and review date', function (): void {
    $missingReviewDate = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => null,
    ]);
    $missingSource = Post::factory()->create([
        'source_url' => null,
        'last_reviewed_at' => today(),
    ]);

    expect(EditorialReadiness::issues($missingReviewDate))->toContain('Set the review date')
        ->and(EditorialReadiness::issues($missingSource))->toContain('Add an official source');
});

test('administrators can preview draft content without exposing structured data', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create();
    $guide = Guide::factory()->draft()->create();
    $episode = Episode::factory()->draft()->create();

    actingAs($admin);

    foreach ([
        route('preview.posts', $post),
        route('preview.guides', $guide),
        route('preview.episodes', $episode),
    ] as $url) {
        get($url)
            ->assertOk()
            ->assertSee('Preview mode')
            ->assertSee('noindex,nofollow', false)
            ->assertDontSee('application/ld+json', false);
    }
});

test('preview routes reject guests and non admin users', function (): void {
    $post = Post::factory()->draft()->create();

    get(route('preview.posts', $post))->assertForbidden();

    actingAs(User::factory()->create())
        ->get(route('preview.posts', $post))
        ->assertForbidden();
});

test('filament content tables show readiness and missing publish dates', function (): void {
    $admin = User::factory()->admin()->create();
    Post::factory()->create(['published_at' => null]);
    Guide::factory()->create(['published_at' => null]);
    Episode::factory()->create(['published_at' => null]);

    actingAs($admin);

    foreach ([PostResource::getUrl(), GuideResource::getUrl(), EpisodeResource::getUrl()] as $url) {
        get($url)
            ->assertOk()
            ->assertSee('Readiness')
            ->assertSee('Needs publish date');
    }
});

test('filament forms explain publish requirements and edit pages offer previews', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create();
    $guide = Guide::factory()->draft()->create();
    $episode = Episode::factory()->draft()->create();

    actingAs($admin);

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('use the Publish action')
        ->assertSee('Optional for evergreen posts');
    get(GuideResource::getUrl('create'))
        ->assertOk()
        ->assertSee('use the Publish action');
    get(EpisodeResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Audio and transcripts may be added later')
        ->assertSee('Hosted MP3')
        ->assertSee('Upload an MP3 up to 256 MB');

    foreach ([
        PostResource::getUrl('edit', ['record' => $post]),
        GuideResource::getUrl('edit', ['record' => $guide]),
        EpisodeResource::getUrl('edit', ['record' => $episode]),
    ] as $url) {
        get($url)
            ->assertOk()
            ->assertSee('Preview');
    }
});

test('ready drafts can be explicitly published and unpublished', function (string $page, string $model): void {
    $admin = User::factory()->admin()->create();
    $record = $model::factory()->draft()->create(match ($model) {
        Post::class => [
            'cover_image' => 'posts/complete.jpg',
            'meta_title' => 'Complete post title',
            'meta_description' => 'Complete post description',
        ],
        Guide::class => [
            'cover_image' => 'guides/complete.jpg',
            'meta_title' => 'Complete guide title',
            'meta_description' => 'Complete guide description',
        ],
        Episode::class => [
            'cover_image' => 'episodes/complete.jpg',
            'meta_title' => 'Complete episode title',
            'meta_description' => 'Complete episode description',
        ],
    });

    actingAs($admin);

    Livewire::test($page, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeTrue()
        ->and($record->published_at)->not->toBeNull();

    Livewire::test($page, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeFalse();
})->with([
    'post' => [EditPost::class, Post::class],
    'guide' => [EditGuide::class, Guide::class],
    'episode' => [EditEpisode::class, Episode::class],
]);

test('publishing is blocked until editorial requirements are complete', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create([
        'excerpt' => null,
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    actingAs($admin);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($post->refresh()->is_published)->toBeFalse();
});

test('content resources expose useful records to global search', function (): void {
    expect(PostResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'category', 'author'])
        ->and(GuideResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'category', 'author'])
        ->and(EpisodeResource::getGloballySearchableAttributes())->toBe(['title', 'slug', 'episode_number']);
});
