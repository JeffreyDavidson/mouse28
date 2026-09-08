<?php

use App\Console\Commands\GeneratePostArtwork;
use App\Models\Post;
use App\Support\ResponsivePostArtwork;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Prompts\Prompt;
use Symfony\Component\Console\Tester\CommandTester;

uses(RefreshDatabase::class);

test('responsive covers preserve originals and use immutable URLs', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('posts/cover.png', UploadedFile::fake()->image('cover.png', 1400, 900)->getContent());
    $post = Post::factory()->create(['cover_image' => 'posts/cover.png']);
    $original = $disk->get('posts/cover.png');

    expect(ResponsivePostArtwork::srcset($post->cover_image))->toBeNull();

    $result = $this->artisan('content:generate-post-artwork');

    expect($result)->toBe(Command::SUCCESS)
        ->and($disk->get('posts/cover.png'))->toBe($original)
        ->and($post->refresh()->cover_image)->toBe('posts/cover.png');

    foreach (ResponsivePostArtwork::WIDTHS as $width) {
        $path = ResponsivePostArtwork::variantPath(hash('sha256', $original), $width);
        expect(getimagesize($disk->path($path))[0])->toBe($width)
            ->and(ResponsivePostArtwork::srcset($post->cover_image))->toContain(" {$width}w");
    }

    $paths = $disk->allFiles();
    $this->artisan('content:generate-post-artwork');
    expect($disk->allFiles())->toBe($paths);

    $disk->put('posts/cover.png', UploadedFile::fake()->image('replacement.png', 1500, 900)->getContent());
    expect(ResponsivePostArtwork::srcset($post->cover_image))->toBeNull();
});

test('generation skips drafts and never upscales small covers', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('posts/small.png', UploadedFile::fake()->image('small.png', 200, 100)->getContent());
    $disk->put('posts/draft.png', UploadedFile::fake()->image('draft.png', 1400, 900)->getContent());
    Post::factory()->create(['cover_image' => 'posts/small.png']);
    Post::factory()->draft()->create(['cover_image' => 'posts/draft.png']);

    $result = $this->artisan('content:generate-post-artwork');

    expect($result)->toBe(Command::SUCCESS)
        ->and($disk->allFiles())->toHaveCount(2);
});

test('corrupt covers fail gracefully without changing originals', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('posts/broken.png', 'not an image');
    Post::factory()->create(['cover_image' => 'posts/broken.png']);

    $result = $this->artisan('content:generate-post-artwork');

    expect($result)->toBe(Command::FAILURE)
        ->and(Storage::disk('public')->get('posts/broken.png'))->toBe('not an image');
});

test('production generation requires explicit approval', function (): void {
    $this->app->detectEnvironment(fn (): string => 'production');

    $result = $this->artisan('content:generate-post-artwork');

    expect($result)->toBe(Command::FAILURE);
});

test('production artwork generation uses native confirmation', function (string $answer, bool $interactive, bool $force, int $expected): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('posts/cover.png', UploadedFile::fake()->image('cover.png', 800, 400)->getContent());
    $post = Post::factory()->create(['cover_image' => 'posts/cover.png']);
    $this->app->detectEnvironment(fn (): string => 'production');
    Prompt::fallbackWhen(true);
    $command = app(GeneratePostArtwork::class);
    $command->setLaravel($this->app);
    $tester = new CommandTester($command);
    $tester->setInputs([$answer]);

    $result = $tester->execute($force ? ['--force' => true] : [], ['interactive' => $interactive]);

    expect($result)->toBe($expected)
        ->and(ResponsivePostArtwork::srcset($post->cover_image) !== null)->toBe($expected === Command::SUCCESS);
})->with([
    'confirmed' => ['yes', true, false, Command::SUCCESS],
    'declined' => ['no', true, false, Command::FAILURE],
    'noninteractive defaults to no' => ['', false, false, Command::FAILURE],
    'explicit force' => ['', false, true, Command::SUCCESS],
]);
