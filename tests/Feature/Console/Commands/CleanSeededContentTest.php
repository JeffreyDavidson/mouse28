<?php

use App\Console\Commands\CleanSeededContent;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('known demo content is removed without deleting real content', function (): void {
    $demoEpisode = Episode::factory()->create(['slug' => CleanSeededContent::episodeSlugs()[0]]);
    $realEpisode = Episode::factory()->create(['slug' => 'real-episode']);
    $demoPost = Post::factory()->create([
        'slug' => CleanSeededContent::postSlugs()[0],
        'episode_id' => $demoEpisode->id,
    ]);
    $realPost = Post::factory()->create([
        'slug' => 'real-post',
        'episode_id' => $demoEpisode->id,
    ]);
    $demoGuide = Guide::factory()->create(['slug' => CleanSeededContent::guideSlugs()[0]]);
    $realGuide = Guide::factory()->create(['slug' => 'real-guide']);

    $exitCode = Artisan::call('content:clean-seeded');

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('Removed 1 demo posts, 1 demo guides, and 1 demo episodes.');
    $this->assertModelMissing($demoPost);
    $this->assertModelMissing($demoGuide);
    $this->assertModelMissing($demoEpisode);
    $this->assertModelExists($realEpisode);
    $this->assertModelExists($realGuide);
    expect($realPost->refresh()->episode_id)->toBeNull();
});

test('production cleanup requires an explicit force option', function (): void {
    $demoPost = Post::factory()->create(['slug' => CleanSeededContent::postSlugs()[0]]);
    app()->detectEnvironment(fn (): string => 'production');

    $exitCode = Artisan::call('content:clean-seeded');

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('Production cleanup requires the --force option.');
    $this->assertModelExists($demoPost);
});

test('legacy episode cleanup preserves real episodes', function (): void {
    $demoEpisode = Episode::factory()->create(['slug' => CleanSeededContent::episodeSlugs()[0]]);
    $realEpisode = Episode::factory()->create(['slug' => 'real-episode']);

    $exitCode = Artisan::call('episodes:clean-seeded');

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('Deleted 1 seeded episodes.');
    $this->assertModelMissing($demoEpisode);
    $this->assertModelExists($realEpisode);
});
