<?php

use App\Console\Commands\ImportPublicContent;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

covers(ImportPublicContent::class);

pest()->use(RefreshDatabase::class);

test('public content archive is imported idempotently with podcast metadata', function (): void {
    $archivePath = storage_path('framework/testing/public-content-import.json');
    File::put($archivePath, json_encode([
        'version' => 1,
        'episodes' => [[
            'title' => 'Example Episode',
            'slug' => 'example-episode',
            'episode_number' => 28,
            'transistor_url' => 'https://example.com/episode-audio',
            'published_at' => now()->subDay()->toAtomString(),
        ]],
        'posts' => [[
            'title' => 'Example Post',
            'slug' => 'example-post',
            'body' => '',
            'episode_slug' => 'example-episode',
            'published_at' => now()->subDay()->toAtomString(),
        ]],
        'guides' => [[
            'title' => 'Example Guide',
            'slug' => 'example-guide',
            'body' => '',
            'category' => 'accessibility',
            'author' => 'both',
            'published_at' => now()->subDay()->toAtomString(),
        ]],
        'podcast' => [
            'name' => 'Example Podcast',
        ],
    ], JSON_THROW_ON_ERROR));
    Podcast::query()->create([
        'name' => 'Existing Podcast',
    ]);

    $exitCode = pendingCommand('content:import-public', ['path' => $archivePath])->run();
    $repeatExitCode = pendingCommand('content:import-public', ['path' => $archivePath])->run();

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($repeatExitCode)->toBe(Command::SUCCESS)
        ->and(Post::query()->where('slug', 'example-post')->firstOrFail()->episode?->slug)->toBe('example-episode')
        ->and(Episode::query()->where('slug', 'example-episode')->firstOrFail()->transistor_url)->toBe('https://example.com/episode-audio')
        ->and(Guide::query()->where('slug', 'example-guide')->firstOrFail()->is_published)->toBeTrue()
        ->and(Podcast::query()->firstOrFail()->name)->toBe('Example Podcast')
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
        'posts' => [],
        'episodes' => [],
        'guides' => [],
        'podcast' => null,
    ], JSON_THROW_ON_ERROR));
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('app.url', 'https://mouse28.com');

    $exitCode = pendingCommand('content:import-public', [
        'path' => $archivePath,
        '--staging' => true,
    ])
        ->expectsOutputToContain('Public content cannot be imported into production.')
        ->run();

    expect($exitCode)->toBe(Command::FAILURE);

    File::delete($archivePath);
});

test('public content archive requires an explicit staging override on the staging hostname', function (): void {
    $archivePath = storage_path('framework/testing/public-content-staging.json');
    File::put($archivePath, json_encode([
        'version' => 1,
        'posts' => [],
        'episodes' => [],
        'guides' => [],
        'podcast' => null,
    ], JSON_THROW_ON_ERROR));
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('app.url', 'http://staging.mouse28.com');

    $refusedExitCode = pendingCommand('content:import-public', ['path' => $archivePath])->run();
    $allowedExitCode = pendingCommand('content:import-public', [
        'path' => $archivePath,
        '--staging' => true,
    ])->run();

    expect($refusedExitCode)->toBe(Command::FAILURE)
        ->and($allowedExitCode)->toBe(Command::SUCCESS);

    File::delete($archivePath);
});
