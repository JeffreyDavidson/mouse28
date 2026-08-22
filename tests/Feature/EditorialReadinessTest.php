<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use App\Support\EditorialReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        ->and(EditorialReadiness::issues($episode))->toContain('Add audio', 'Add a transcript');
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
        ->assertSee('Published posts require');
    get(GuideResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Published guides require');
    get(EpisodeResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Published episodes require')
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
