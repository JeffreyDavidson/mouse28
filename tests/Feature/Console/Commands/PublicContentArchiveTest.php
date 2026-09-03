<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('public content archive excludes drafts and private records', function (): void {
    $episode = Episode::factory()->create([
        'slug' => 'published-episode',
        'title' => 'Published Episode',
    ]);
    Episode::factory()->draft()->create([
        'slug' => 'private-episode',
        'title' => 'Private Episode',
    ]);
    Post::factory()->create([
        'slug' => 'published-post',
        'title' => 'Published Post',
        'episode_id' => $episode->id,
    ]);
    Post::factory()->draft()->create([
        'slug' => 'private-post',
        'title' => 'Private Post',
    ]);
    Guide::factory()->create([
        'slug' => 'published-guide',
        'title' => 'Published Guide',
    ]);
    Guide::factory()->draft()->create([
        'slug' => 'private-guide',
        'title' => 'Private Guide',
    ]);
    Podcast::query()->create([
        'name' => 'Mouse28',
        'description' => 'Public show description',
        'email' => 'private@example.com',
    ]);
    $archivePath = storage_path('framework/testing/public-content-export.json');

    $exitCode = Artisan::call('content:export-public', ['path' => $archivePath]);
    $archive = File::json($archivePath);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($archive['version'])->toBe(1)
        ->and(collect($archive['posts'])->pluck('slug')->all())->toBe(['published-post'])
        ->and(collect($archive['episodes'])->pluck('slug')->all())->toBe(['published-episode'])
        ->and(collect($archive['guides'])->pluck('slug')->all())->toBe(['published-guide'])
        ->and($archive['posts'][0]['episode_slug'])->toBe('published-episode')
        ->and($archive['podcast'])->not->toHaveKey('email')
        ->and(File::get($archivePath))->not->toContain('Private Post', 'Private Guide', 'Private Episode', 'private@example.com');

    File::delete($archivePath);
});

test('public content archive can be imported without replacing environment-specific podcast email', function (): void {
    $archivePath = storage_path('framework/testing/public-content-import.json');
    File::put($archivePath, json_encode([
        'version' => 1,
        'exported_at' => now()->toAtomString(),
        'episodes' => [[
            'title' => 'Synced Episode',
            'slug' => 'synced-episode',
            'description' => 'A public episode.',
            'show_notes' => '<p>Public notes.</p>',
            'transcript' => '<p>Public transcript.</p>',
            'episode_number' => 28,
            'season_number' => 1,
            'audio_url' => null,
            'audio_path' => null,
            'apple_url' => null,
            'spotify_url' => null,
            'youtube_url' => null,
            'duration_seconds' => 120,
            'cover_image' => 'episodes/synced.webp',
            'published_at' => now()->subDay()->toAtomString(),
            'meta_title' => null,
            'meta_description' => null,
            'og_image' => null,
        ]],
        'posts' => [[
            'title' => 'Synced Post',
            'slug' => 'synced-post',
            'excerpt' => 'A public post.',
            'body' => '<p>Public body.</p>',
            'source_url' => null,
            'last_reviewed_at' => null,
            'cover_image' => 'posts/synced.webp',
            'episode_slug' => 'synced-episode',
            'category' => 'episode-recap',
            'author' => 'both',
            'published_at' => now()->subDay()->toAtomString(),
            'meta_title' => null,
            'meta_description' => null,
            'og_image' => null,
        ]],
        'guides' => [[
            'title' => 'Synced Guide',
            'slug' => 'synced-guide',
            'excerpt' => 'A public guide.',
            'body' => '<p>Public guide body.</p>',
            'category' => 'accessibility',
            'author' => 'both',
            'cover_image' => null,
            'source_url' => 'https://example.com/guide',
            'last_reviewed_at' => now()->toDateString(),
            'published_at' => now()->subDay()->toAtomString(),
            'meta_title' => null,
            'meta_description' => null,
            'og_image' => null,
        ]],
        'podcast' => [
            'name' => 'Synced Mouse28',
            'description' => 'Synced public description.',
            'cover_image' => null,
            'apple_url' => 'https://example.com/apple',
            'spotify_url' => null,
            'youtube_url' => null,
            'instagram_url' => null,
            'tiktok_url' => null,
        ],
    ], JSON_THROW_ON_ERROR));
    Podcast::query()->create([
        'name' => 'Staging Mouse28',
        'email' => 'staging@example.com',
    ]);

    $exitCode = Artisan::call('content:import-public', ['path' => $archivePath]);
    $repeatExitCode = Artisan::call('content:import-public', ['path' => $archivePath]);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($repeatExitCode)->toBe(Command::SUCCESS)
        ->and(Post::query()->where('slug', 'synced-post')->firstOrFail()->episode?->slug)->toBe('synced-episode')
        ->and(Guide::query()->where('slug', 'synced-guide')->firstOrFail()->is_published)->toBeTrue()
        ->and(Podcast::query()->firstOrFail()->name)->toBe('Synced Mouse28')
        ->and(Podcast::query()->firstOrFail()->email)->toBe('staging@example.com')
        ->and(Post::query()->count())->toBe(1)
        ->and(Guide::query()->count())->toBe(1)
        ->and(Episode::query()->count())->toBe(1)
        ->and(Podcast::query()->count())->toBe(1);

    File::delete($archivePath);
});

test('public content archive cannot be imported in production', function (): void {
    $archivePath = storage_path('framework/testing/public-content-production.json');
    File::put($archivePath, json_encode([
        'version' => 1,
        'exported_at' => now()->toAtomString(),
        'posts' => [],
        'episodes' => [],
        'guides' => [],
        'podcast' => null,
    ], JSON_THROW_ON_ERROR));
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('app.url', 'https://mouse28.com');

    $exitCode = Artisan::call('content:import-public', [
        'path' => $archivePath,
        '--staging' => true,
    ]);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('Public content cannot be imported into production.');

    File::delete($archivePath);
});

test('public content archive requires an explicit staging override on the staging hostname', function (): void {
    $archivePath = storage_path('framework/testing/public-content-staging.json');
    File::put($archivePath, json_encode([
        'version' => 1,
        'exported_at' => now()->toAtomString(),
        'posts' => [],
        'episodes' => [],
        'guides' => [],
        'podcast' => null,
    ], JSON_THROW_ON_ERROR));
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('app.url', 'http://staging.mouse28.com');

    $refusedExitCode = Artisan::call('content:import-public', ['path' => $archivePath]);
    $allowedExitCode = Artisan::call('content:import-public', [
        'path' => $archivePath,
        '--staging' => true,
    ]);

    expect($refusedExitCode)->toBe(Command::FAILURE)
        ->and($allowedExitCode)->toBe(Command::SUCCESS);

    File::delete($archivePath);
});
