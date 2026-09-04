<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('production public content and referenced media can be synced locally', function (): void {
    Storage::fake('public');
    config()->set('mouse28.production_sync.ssh_host', 'cold-moon');
    config()->set('mouse28.production_sync.site_path', '/home/forge/mouse28.com/current');

    $stalePost = Post::factory()->create(['slug' => 'stale-published-post']);
    $localDraft = Post::factory()->draft()->create(['slug' => 'local-draft']);
    $localEpisode = Episode::factory()->create([
        'slug' => 'local-placeholder-episode',
        'episode_number' => 1,
    ]);
    $archive = publicContentArchive([
        'episodes' => [[
            'title' => 'Production Episode',
            'slug' => 'production-episode',
            'episode_number' => 1,
            'published_at' => now()->subDay()->toAtomString(),
        ]],
        'posts' => [
            [
                'title' => 'Production Post',
                'slug' => 'production-post',
                'excerpt' => 'Published on production.',
                'body' => '<p>Production body.</p>',
                'cover_image' => 'posts/production-post.webp',
                'category' => 'disney-tips',
                'author' => 'both',
                'episode_slug' => 'production-episode',
                'published_at' => now()->subDay()->toAtomString(),
                'og_image' => 'posts/production-post-social.webp',
            ],
            [
                'title' => 'Second Production Post',
                'slug' => 'second-production-post',
                'excerpt' => 'Also published on production.',
                'body' => '<p>Second production body.</p>',
                'cover_image' => 'posts/second-production-post.webp',
                'category' => 'family-experiences',
                'author' => 'both',
                'published_at' => now()->subHours(2)->toAtomString(),
            ],
        ],
    ]);
    $transferredMedia = [];

    Process::fake(function (PendingProcess $process) use ($archive, &$transferredMedia) {
        $command = $process->command;

        if ($command[0] === 'scp') {
            File::put($command[array_key_last($command)], json_encode($archive, JSON_THROW_ON_ERROR));
        }

        if ($command[0] === 'rsync') {
            $manifestArgument = collect($command)->first(
                fn (string $argument): bool => str_starts_with($argument, '--files-from='),
            );
            $transferredMedia = File::lines(substr($manifestArgument, strlen('--files-from=')))
                ->map(fn (string $path): string => trim($path))
                ->filter()
                ->values()
                ->all();
        }

        return Process::result();
    });
    Process::preventStrayProcesses();

    $exitCode = Artisan::call('content:sync-production');

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Post::query()->where('slug', 'production-post')->firstOrFail()->cover_image)
        ->toBe('posts/production-post.webp')
        ->and(Post::query()->whereKey($stalePost)->exists())->toBeFalse()
        ->and(Post::withTrashed()->find($stalePost->id)?->trashed())->toBeTrue()
        ->and($localDraft->fresh())->not->toBeNull()
        ->and(Episode::query()->count())->toBe(1)
        ->and(Episode::query()->firstOrFail()->id)->toBe($localEpisode->id)
        ->and(Episode::query()->firstOrFail()->slug)->toBe('production-episode')
        ->and(Post::query()->where('slug', 'production-post')->firstOrFail()->episode?->slug)
        ->toBe('production-episode')
        ->and($transferredMedia)->toBe([
            'posts/production-post-social.webp',
            'posts/production-post.webp',
            'posts/second-production-post.webp',
        ])
        ->and(Artisan::output())->toContain('Local drafts were preserved.');

    Process::assertRanTimes(fn (PendingProcess $process): bool => $process->command[0] === 'ssh', 2);
    Process::assertRan(fn (PendingProcess $process): bool => $process->command[0] === 'scp');
    Process::assertRan(fn (PendingProcess $process): bool => $process->command[0] === 'rsync');
});

test('production content sync refuses to run in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    Process::fake();
    Process::preventStrayProcesses();

    $exitCode = Artisan::call('content:sync-production');

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('may only run in a non-production environment');

    Process::assertNothingRan();
});

test('production content sync rejects unsafe media paths', function (): void {
    config()->set('mouse28.production_sync.ssh_host', 'cold-moon');
    config()->set('mouse28.production_sync.site_path', '/home/forge/mouse28.com/current');
    $archive = publicContentArchive([
        'posts' => [[
            'title' => 'Unsafe Post',
            'slug' => 'unsafe-post',
            'cover_image' => '../private/file.webp',
            'published_at' => now()->subDay()->toAtomString(),
        ]],
    ]);

    Process::fake(function (PendingProcess $process) use ($archive) {
        if ($process->command[0] === 'scp') {
            File::put($process->command[array_key_last($process->command)], json_encode($archive, JSON_THROW_ON_ERROR));
        }

        return Process::result();
    });
    Process::preventStrayProcesses();

    $exitCode = Artisan::call('content:sync-production');

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('unsafe media path')
        ->and(Post::query()->where('slug', 'unsafe-post')->exists())->toBeFalse();

    Process::assertDidntRun(fn (PendingProcess $process): bool => $process->command[0] === 'rsync');
});

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
