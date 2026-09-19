<?php

use App\Actions\GenerateResponsiveCover;
use App\Models\Post;
use App\Support\ResponsiveArtwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

test('failed derivative publication leaves no final or temporary file and can be retried', function (string $operation): void {
    $disk = Storage::fake('public');
    $original = UploadedFile::fake()->image('cover.png', 600, 400)->getContent();
    $disk->put('posts/cover.png', $original);
    $record = Post::factory()->create(['cover_image' => 'posts/cover.png']);
    $failingDisk = Mockery::mock($disk);
    $failingDisk->shouldReceive($operation)->once()->andReturnFalse();
    Storage::set('public', $failingDisk);

    expect(fn () => app(GenerateResponsiveCover::class)($record))
        ->toThrow(RuntimeException::class, 'Unable to publish responsive image.')
        ->and($disk->allFiles())->toBe(['posts/cover.png'])
        ->and($disk->get('posts/cover.png'))->toBe($original);
    Storage::set('public', $disk);

    $count = app(GenerateResponsiveCover::class)($record);

    expect($count)->toBe(1)
        ->and($disk->exists(ResponsiveArtwork::variantPath(hash('sha256', $original), 480)))->toBeTrue();
})->with(['put', 'move']);

test('cover generation refuses unpublished records', function (string $state): void {
    $disk = Storage::fake('public');
    $record = $state === 'draft' ? Post::factory()->draft()->create() : Post::factory()->scheduled()->create();

    expect(fn () => app(GenerateResponsiveCover::class)($record))
        ->toThrow(RuntimeException::class, 'Only published covers can be generated.')
        ->and($disk->allFiles())->toBeEmpty();
})->with(['draft', 'scheduled']);

test('cover generation publishes a complete WebP and reuses it on retry', function (): void {
    $disk = Storage::fake('public');
    $disk->put('posts/cover.png', UploadedFile::fake()->image('cover.png', 600, 400)->getContent());
    $record = Post::factory()->create(['cover_image' => 'posts/cover.png']);

    $count = app(GenerateResponsiveCover::class)($record);
    $retryCount = app(GenerateResponsiveCover::class)($record);

    expect($count)->toBe(1)
        ->and($retryCount)->toBe(0)
        ->and($disk->allFiles())->toHaveCount(2)
        ->and(ResponsiveArtwork::srcset($record->cover_image))->toContain('480w');
});
