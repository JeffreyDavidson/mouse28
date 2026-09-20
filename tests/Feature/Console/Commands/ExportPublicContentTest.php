<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

pest()->use(RefreshDatabase::class);

test('public content archive includes only published content', function (): void {
    $episode = Episode::factory()->create([
        'slug' => 'example-episode',
    ]);
    Episode::factory()->draft()->create([
        'title' => 'Draft Episode',
    ]);
    Post::factory()->create([
        'slug' => 'example-post',
        'episode_id' => $episode->id,
    ]);
    Post::factory()->draft()->create([
        'title' => 'Draft Post',
    ]);
    Guide::factory()->create([
        'slug' => 'example-guide',
    ]);
    Guide::factory()->draft()->create([
        'title' => 'Draft Guide',
    ]);
    Podcast::query()->create([
        'name' => 'Example Podcast',
    ]);
    $archivePath = storage_path('framework/testing/public-content-export.json');

    $exitCode = pendingCommand('content:export-public', ['path' => $archivePath])->run();
    $archive = publicArchive($archivePath);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($archive['version'])->toBe(1)
        ->and(collect(publicArchiveRecords($archive, 'posts'))->pluck('slug')->all())->toBe(['example-post'])
        ->and(collect(publicArchiveRecords($archive, 'episodes'))->pluck('slug')->all())->toBe(['example-episode'])
        ->and(collect(publicArchiveRecords($archive, 'guides'))->pluck('slug')->all())->toBe(['example-guide'])
        ->and(publicArchiveRecords($archive, 'posts')[0]['episode_slug'])->toBe('example-episode')
        ->and(File::get($archivePath))->not->toContain('Draft Post', 'Draft Guide', 'Draft Episode');

    File::delete($archivePath);
});

/** @return array<string, mixed> */
function publicArchive(string $path): array
{
    $archive = File::json($path);

    if (! is_array($archive)) {
        throw new UnexpectedValueException('The public content archive must be a JSON object.');
    }

    $result = [];

    foreach ($archive as $key => $value) {
        if (! is_string($key)) {
            throw new UnexpectedValueException('The public content archive keys must be strings.');
        }

        $result[$key] = $value;
    }

    return $result;
}

/**
 * @param  array<string, mixed>  $archive
 * @return list<array<string, mixed>>
 */
function publicArchiveRecords(array $archive, string $key): array
{
    $records = $archive[$key] ?? null;

    if (! is_array($records)) {
        throw new UnexpectedValueException("The public content archive {$key} value must be a list.");
    }

    $result = [];

    foreach ($records as $record) {
        if (! is_array($record)) {
            throw new UnexpectedValueException("The public content archive {$key} records must be objects.");
        }

        $normalized = [];

        foreach ($record as $recordKey => $value) {
            if (! is_string($recordKey)) {
                throw new UnexpectedValueException('The public content archive record keys must be strings.');
            }

            $normalized[$recordKey] = $value;
        }

        $result[] = $normalized;
    }

    return $result;
}
