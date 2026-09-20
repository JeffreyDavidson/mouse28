<?php

use App\Console\Commands\SyncPublicContentFromProduction;
use App\Models\Episode;
use App\Models\Post;
use App\Support\PublicContentArchive;
use Illuminate\Console\CacheCommandMutex;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

test('isolated sync refuses concurrent work through either command name', function (string $name): void {
    Process::fake();
    $command = app(SyncPublicContentFromProduction::class);
    $mutex = app(CacheCommandMutex::class);
    expect($mutex->create($command))->toBeTrue();

    try {
        $exitCode = pendingCommand($name, ['--isolated' => 1])->run();

        expect($exitCode)->toBe(Command::FAILURE);
        Process::assertNothingRan();
    } finally {
        $mutex->forget($command);
    }
})->with(['content:sync-from-production', 'content:sync-production']);

test('sync stops before transferring media when a local draft collides', function (): void {
    // Arrange
    Storage::fake('public');
    config()->set('mouse28.production_sync.ssh_host', 'cold-moon');
    config()->set('mouse28.production_sync.site_path', '/home/forge/mouse28.com/current');
    $post = Post::factory()->create(['cover_image' => 'posts/local.webp']);
    $archive = app(PublicContentArchive::class)->export();
    $post->update(['is_published' => false]);
    Process::fake(function (PendingProcess $process) use ($archive) {
        $command = syncProcessArguments($process);
        if ($command[0] === 'scp') {
            File::put(array_last($command), json_encode($archive, JSON_THROW_ON_ERROR));
        }

        return Process::result();
    });
    Process::preventStrayProcesses();

    // Act
    $exitCode = pendingCommand('content:sync-production')->run();

    // Assert
    expect($exitCode)->toBe(Command::FAILURE)
        ->and($post->refresh()->is_published)->toBeFalse();
    Process::assertDidntRun(fn (PendingProcess $process): bool => syncProcessArguments($process)[0] === 'rsync');
});

test('production syncs public content, preserves drafts, and transfers referenced media', function (): void {
    Storage::fake('public');
    config()->set('mouse28.production_sync.ssh_host', 'cold-moon');
    config()->set('mouse28.production_sync.site_path', '/home/forge/mouse28.com/current');

    $stalePost = Post::factory()->create(['slug' => 'stale-post']);
    $localDraft = Post::factory()->draft()->create();
    $localEpisode = Episode::factory()->create([
        'episode_number' => 1,
    ]);
    $archive = publicContentArchive([
        'episodes' => [[
            'title' => 'Example Episode',
            'slug' => 'example-episode',
            'episode_number' => 1,
            'published_at' => now()->subDay()->toAtomString(),
        ]],
        'posts' => [
            [
                'title' => 'Example Post',
                'slug' => 'example-post',
                'body' => '',
                'cover_image' => 'posts/example-post.webp',
                'episode_slug' => 'example-episode',
                'published_at' => now()->subDay()->toAtomString(),
                'og_image' => 'posts/example-post-social.webp',
            ],
            [
                'title' => 'Second Example Post',
                'slug' => 'second-example-post',
                'body' => '',
                'cover_image' => 'posts/second-example-post.webp',
                'published_at' => now()->subHours(2)->toAtomString(),
            ],
        ],
    ]);
    $transferredMedia = [];

    Process::fake(function (PendingProcess $process) use ($archive, &$transferredMedia) {
        $command = syncProcessArguments($process);

        if ($command[0] === 'scp') {
            File::put(array_last($command), json_encode($archive, JSON_THROW_ON_ERROR));
        }

        if ($command[0] === 'rsync') {
            $manifestArgument = collect($command)->first(
                fn (string $argument): bool => str_starts_with($argument, '--files-from='),
            ) ?? throw new UnexpectedValueException('The rsync command must specify a media manifest.');
            $transferredMedia = collect(explode("\n", File::get(substr($manifestArgument, strlen('--files-from=')))))
                ->map(fn (string $path): string => trim($path))
                ->filter()
                ->values()
                ->all();
        }

        return Process::result();
    });
    Process::preventStrayProcesses();

    $exitCode = pendingCommand('content:sync-production')
        ->expectsOutputToContain('Local drafts were preserved.')
        ->run();

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Post::query()->where('slug', 'example-post')->firstOrFail()->cover_image)
        ->toBe('posts/example-post.webp')
        ->and(Post::query()->whereKey($stalePost)->exists())->toBeFalse()
        ->and(Post::withTrashed()->find($stalePost->id)?->trashed())->toBeTrue()
        ->and($localDraft->fresh())->not->toBeNull()
        ->and(Episode::query()->count())->toBe(1)
        ->and(Episode::query()->firstOrFail()->id)->toBe($localEpisode->id)
        ->and(Episode::query()->firstOrFail()->slug)->toBe('example-episode')
        ->and(Post::query()->where('slug', 'example-post')->firstOrFail()->episode?->slug)
        ->toBe('example-episode')
        ->and($transferredMedia)->toBe([
            'posts/example-post-social.webp',
            'posts/example-post.webp',
            'posts/second-example-post.webp',
        ]);

    Process::assertRanTimes(fn (PendingProcess $process): bool => syncProcessArguments($process)[0] === 'ssh', 2);
    Process::assertRan(fn (PendingProcess $process): bool => syncProcessArguments($process)[0] === 'scp');
    Process::assertRan(fn (PendingProcess $process): bool => syncProcessArguments($process)[0] === 'rsync');
});

test('production content sync refuses to run in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    Process::fake();
    Process::preventStrayProcesses();

    $exitCode = pendingCommand('content:sync-production')->run();

    expect($exitCode)->toBe(Command::FAILURE);

    Process::assertNothingRan();
});

test('production content sync rejects unsafe media paths', function (): void {
    config()->set('mouse28.production_sync.ssh_host', 'cold-moon');
    config()->set('mouse28.production_sync.site_path', '/home/forge/mouse28.com/current');
    $archive = publicContentArchive([
        'posts' => [[
            'title' => 'Invalid Post',
            'slug' => 'invalid-post',
            'cover_image' => '../private/file.webp',
            'published_at' => now()->subDay()->toAtomString(),
        ]],
    ]);

    Process::fake(function (PendingProcess $process) use ($archive) {
        $command = syncProcessArguments($process);
        if ($command[0] === 'scp') {
            File::put(array_last($command), json_encode($archive, JSON_THROW_ON_ERROR));
        }

        return Process::result();
    });
    Process::preventStrayProcesses();

    $exitCode = pendingCommand('content:sync-production')
        ->expectsOutputToContain('unsafe media path')
        ->run();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(Post::query()->where('slug', 'invalid-post')->exists())->toBeFalse();

    Process::assertDidntRun(fn (PendingProcess $process): bool => syncProcessArguments($process)[0] === 'rsync');
});

/** @return non-empty-list<string> */
function syncProcessArguments(PendingProcess $process): array
{
    $command = $process->command;

    if (! is_array($command) || $command === []) {
        throw new UnexpectedValueException('Content sync must pass a nonempty argument array to the process.');
    }

    return array_values($command);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function publicContentArchive(array $overrides = []): array
{
    return [
        'version' => 1,
        'exported_at' => now()->toAtomString(),
        'episodes' => [],
        'posts' => [],
        'guides' => [],
        'podcast' => null,
        ...$overrides,
    ];
}
