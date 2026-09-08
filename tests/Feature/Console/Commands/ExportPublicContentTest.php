<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

pest()->use(RefreshDatabase::class);

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
