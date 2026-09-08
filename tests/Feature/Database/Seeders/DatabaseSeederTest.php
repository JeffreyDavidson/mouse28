<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

pest()->use(RefreshDatabase::class);

test('seeder creates a useful mix of related dummy content', function (): void {
    $exitCode = Artisan::call('db:seed', ['--force' => true]);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Episode::query()->count())->toBe(6)
        ->and(Episode::query()->published()->count())->toBe(4)
        ->and(Episode::query()->drafts()->count())->toBe(1)
        ->and(Episode::query()->scheduled()->count())->toBe(1)
        ->and(Post::query()->count())->toBe(9)
        ->and(Post::query()->published()->count())->toBe(6)
        ->and(Post::query()->drafts()->count())->toBe(2)
        ->and(Post::query()->scheduled()->count())->toBe(1)
        ->and(Post::query()->published()->whereNotNull('episode_id')->count())->toBe(6)
        ->and(Guide::query()->count())->toBe(6)
        ->and(Guide::query()->published()->count())->toBe(4)
        ->and(Guide::query()->drafts()->count())->toBe(1)
        ->and(Guide::query()->scheduled()->count())->toBe(1)
        ->and(Podcast::query()->count())->toBe(1);
});

test('production seeding does not create dummy content', function (): void {
    app()->detectEnvironment(fn (): string => 'production');

    $exitCode = Artisan::call('db:seed', ['--force' => true]);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Episode::query()->doesntExist())->toBeTrue()
        ->and(Post::query()->doesntExist())->toBeTrue()
        ->and(Guide::query()->doesntExist())->toBeTrue()
        ->and(Podcast::query()->count())->toBe(1);
});
