<?php

use App\Console\Commands\GenerateResponsiveArtwork;
use App\Models\Episode;
use App\Models\Post;
use App\Support\ResponsiveArtwork;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Prompts\Prompt;
use Symfony\Component\Console\Tester\CommandTester;

pest()->use(RefreshDatabase::class);

test('record scoped generation leaves other covers alone', function (string $type, string $command): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put("{$type}/selected.png", UploadedFile::fake()->image('selected.png', 1000, 800)->getContent());
    $disk->put("{$type}/other.png", UploadedFile::fake()->image('other.png', 1200, 800)->getContent());
    $selected = ($type === 'posts' ? Post::factory() : Episode::factory())->createOne(['cover_image' => "{$type}/selected.png"]);
    $other = ($type === 'posts' ? Post::factory() : Episode::factory())->createOne(['cover_image' => "{$type}/other.png"]);

    $this->pendingCommand($command, ['--type' => $type, '--id' => $selected->id])
        ->expectsOutputToContain('Originals and content records were not changed.')
        ->assertSuccessful();

    expect(ResponsiveArtwork::srcset($selected->cover_image, square: $type === 'episodes'))->not->toBeNull()
        ->and(ResponsiveArtwork::srcset($other->cover_image, square: $type === 'episodes'))->toBeNull();
})->with(['posts', 'episodes'])->with(['content:generate-responsive-artwork', 'content:generate-artwork']);

test('record scoped generation reports missing sources as a failure', function (): void {
    Storage::fake('public');
    $post = Post::factory()->create(['cover_image' => 'posts/missing.png']);

    $this->pendingCommand('content:generate-artwork', ['--id' => $post->id])
        ->expectsOutputToContain('Skipped unavailable or unsupported artwork')
        ->assertFailed();
});

test('responsive covers preserve originals and use immutable URLs', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('posts/cover.png', UploadedFile::fake()->image('cover.png', 1400, 900)->getContent());
    $post = Post::factory()->create(['cover_image' => 'posts/cover.png']);
    $original = $disk->get('posts/cover.png') ?? throw new UnexpectedValueException('The original cover is missing.');

    expect(ResponsiveArtwork::srcset($post->cover_image))->toBeNull();

    $result = $this->pendingCommand('content:generate-post-artwork')->run();

    expect($result)->toBe(Command::SUCCESS)
        ->and($disk->get('posts/cover.png'))->toBe($original)
        ->and($post->refresh()->cover_image)->toBe('posts/cover.png');

    foreach ([480, 640, 768, 1280] as $width) {
        $path = ResponsiveArtwork::variantPath(hash('sha256', $original), $width);
        $dimensions = getimagesize($disk->path($path)) ?: throw new UnexpectedValueException('The generated cover is not an image.');
        expect($dimensions[0])->toBe($width)
            ->and($dimensions['mime'])->toBe('image/webp')
            ->and(ResponsiveArtwork::srcset($post->cover_image))->toContain(" {$width}w");
    }

    $paths = $disk->allFiles();
    $this->pendingCommand('content:generate-post-artwork')->run();
    expect($disk->allFiles())->toBe($paths);

    $disk->put('posts/cover.png', UploadedFile::fake()->image('replacement.png', 1500, 900)->getContent());
    expect(ResponsiveArtwork::srcset($post->cover_image))->toBeNull();
});

test('generation skips drafts and never upscales small covers', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('posts/small.png', UploadedFile::fake()->image('small.png', 200, 100)->getContent());
    $disk->put('posts/draft.png', UploadedFile::fake()->image('draft.png', 1400, 900)->getContent());
    Post::factory()->create(['cover_image' => 'posts/small.png']);
    Post::factory()->draft()->create(['cover_image' => 'posts/draft.png']);

    $result = $this->pendingCommand('content:generate-post-artwork')->run();

    expect($result)->toBe(Command::SUCCESS)
        ->and($disk->allFiles())->toHaveCount(2);
});

test('intermediate candidates are generated only when the source can support them', function (string $type, int $width, int $height, bool $hasIntermediate): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $source = "{$type}/cover.png";
    $original = UploadedFile::fake()->image('cover.png', $width, $height)->getContent();
    $disk->put($source, $original);
    ($type === 'posts' ? Post::factory() : Episode::factory())->create(['cover_image' => $source]);
    $square = $type === 'episodes';
    $existing = ResponsiveArtwork::variantPath(hash('sha256', $original), 480, $square);
    $disk->put($existing, 'existing derivative');

    $result = $this->pendingCommand('content:generate-artwork', ['--type' => $type])->run();

    expect($result)->toBe(Command::SUCCESS)
        ->and($disk->get($source))->toBe($original)
        ->and($disk->get($existing))->toBe('existing derivative')
        ->and($disk->exists(ResponsiveArtwork::variantPath(hash('sha256', $original), 640, $square)))->toBe($hasIntermediate)
        ->and($disk->exists(ResponsiveArtwork::variantPath(hash('sha256', $original), 768, $square)))->toBeFalse();
})->with([
    'post below 640 pixels' => ['posts', 639, 400, false],
    'post at 640 pixels' => ['posts', 640, 400, true],
    'episode short edge below 640 pixels' => ['episodes', 1000, 639, false],
    'episode short edge at 640 pixels' => ['episodes', 1000, 640, true],
]);

test('corrupt covers fail gracefully without changing originals', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('posts/broken.png', 'not an image');
    Post::factory()->create(['cover_image' => 'posts/broken.png']);

    $result = $this->pendingCommand('content:generate-post-artwork')->run();

    expect($result)->toBe(Command::FAILURE)
        ->and(Storage::disk('public')->get('posts/broken.png'))->toBe('not an image');
});

test('production generation requires explicit approval', function (): void {
    $this->app->detectEnvironment(fn (): string => 'production');

    $this->pendingCommand('content:generate-post-artwork')
        ->expectsConfirmation('Are you sure you want to run this command?', 'no')
        ->assertFailed();
});

test('production artwork generation uses native confirmation', function (string $answer, bool $interactive, bool $force, int $expected, string $type): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put("{$type}/cover.png", UploadedFile::fake()->image('cover.png', 1000, 800)->getContent());
    $record = ($type === 'posts' ? Post::factory() : Episode::factory())->createOne(['cover_image' => "{$type}/cover.png"]);
    $this->app->detectEnvironment(fn (): string => 'production');
    Prompt::fallbackWhen(true);
    $command = app(GenerateResponsiveArtwork::class);
    $command->setLaravel($this->app);
    $tester = new CommandTester($command);
    $tester->setInputs([$answer]);

    $result = $tester->execute(['--type' => $type, '--force' => $force], ['interactive' => $interactive]);

    expect($result)->toBe($expected)
        ->and(ResponsiveArtwork::srcset($record->cover_image, square: $type === 'episodes') !== null)->toBe($expected === Command::SUCCESS);
})->with([
    'confirmed' => ['yes', true, false, Command::SUCCESS],
    'declined' => ['no', true, false, Command::FAILURE],
    'noninteractive defaults to no' => ['', false, false, Command::FAILURE],
    'explicit force' => ['', false, true, Command::SUCCESS],
])->with(['posts', 'episodes']);

test('episode generation is explicit and only includes published covers', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    foreach (['published', 'draft', 'scheduled'] as $index => $name) {
        $disk->put("episodes/{$name}.png", UploadedFile::fake()->image("{$name}.png", 1400 + $index, 800)->getContent());
    }
    $episode = Episode::factory()->create(['cover_image' => 'episodes/published.png']);
    Episode::factory()->draft()->create(['cover_image' => 'episodes/draft.png']);
    Episode::factory()->scheduled()->create(['cover_image' => 'episodes/scheduled.png']);
    $original = $disk->get('episodes/published.png') ?? throw new UnexpectedValueException('The original episode cover is missing.');

    $this->pendingCommand('content:generate-post-artwork')->run();

    expect($disk->allFiles())->toHaveCount(3);

    $result = $this->pendingCommand('content:generate-artwork', ['--type' => 'episodes'])->run();

    expect($result)->toBe(Command::SUCCESS)
        ->and($disk->allFiles())->toHaveCount(6)
        ->and($disk->get('episodes/published.png'))->toBe($original)
        ->and($episode->refresh()->cover_image)->toBe('episodes/published.png');

    foreach ([480, 640, 768] as $width) {
        $path = ResponsiveArtwork::variantPath(hash('sha256', $original), $width, square: true);
        $dimensions = getimagesize($disk->path($path)) ?: throw new UnexpectedValueException('The generated episode cover is not an image.');
        expect([$dimensions[0], $dimensions[1]])->toBe([$width, $width]);
    }
});

test('episode variants use a centered square crop', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $source = imagecreatetruecolor(1200, 600);

    imagefilledrectangle($source, 0, 0, 299, 599, 0xFF0000);
    imagefilledrectangle($source, 300, 0, 899, 599, 0x00FF00);
    imagefilledrectangle($source, 900, 0, 1199, 599, 0x0000FF);
    ob_start();
    imagepng($source);
    $contents = ob_get_clean() ?: throw new UnexpectedValueException('The test image could not be encoded.');

    $disk->put('episodes/cover.png', $contents);
    $episode = Episode::factory()->create(['cover_image' => 'episodes/cover.png']);

    $this->pendingCommand('content:generate-artwork', ['--type' => 'episodes'])->run();

    $path = ResponsiveArtwork::variantPath(hash('sha256', $contents), 480, square: true);
    $variant = imagecreatefromwebp($disk->path($path)) ?: throw new UnexpectedValueException('The generated episode cover is not WebP.');
    $index = imagecolorat($variant, 240, 240);
    $color = imagecolorsforindex($variant, $index === false ? throw new UnexpectedValueException('The generated episode cover has no center pixel.') : $index);

    expect($episode->refresh()->cover_image)->toBe('episodes/cover.png')
        ->and($color['green'])->toBeGreaterThan(200)
        ->and($color['red'])->toBeLessThan(50)
        ->and($color['blue'])->toBeLessThan(50);
});

test('unsupported artwork types fail without generating files', function (): void {
    Storage::fake('public');

    $result = $this->pendingCommand('content:generate-artwork', ['--type' => 'anything'])->run();

    expect($result)->toBe(Command::FAILURE)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});
