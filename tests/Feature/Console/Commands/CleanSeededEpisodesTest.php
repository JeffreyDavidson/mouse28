<?php

use App\Console\Commands\CleanSeededContent;
use App\Models\Episode;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('legacy episode cleanup preserves real episodes', function (): void {
    $demoEpisode = Episode::factory()->create(['slug' => CleanSeededContent::episodeSlugs()[0]]);
    $realEpisode = Episode::factory()->create(['slug' => 'real-episode']);

    $exitCode = Artisan::call('episodes:clean-seeded');

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('Deleted 1 seeded episodes.');
    $this->assertModelMissing($demoEpisode);
    $this->assertModelExists($realEpisode);
});
