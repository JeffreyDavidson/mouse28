<?php

use App\Support\PublicContentArchive;

covers(PublicContentArchive::class);

test('archive versions must match the supported integer version', function (mixed $version): void {
    $service = new PublicContentArchive;
    $archive = [
        'version' => $version,
        'posts' => [],
        'guides' => [],
        'episodes' => [],
        'podcast' => null,
    ];

    expect(fn () => $service->mediaPaths($archive))
        ->toThrow(InvalidArgumentException::class, 'The public content archive version is not supported.');
})->with([
    'string version' => ['1'],
    'future version' => [2],
    'null version' => [null],
]);

test('media paths include all content types once in sorted order and skip legacy episode audio', function (): void {
    $service = new PublicContentArchive;
    $archive = [
        'version' => 1,
        'posts' => [[
            'slug' => 'park-story',
            'cover_image' => 'shared/cover.webp',
            'source_url' => 'https://example.com/source',
        ]],
        'guides' => [[
            'slug' => 'park-guide',
            'cover_image' => 'shared/cover.webp',
        ]],
        'episodes' => [[
            'slug' => 'park-episode',
            'audio_path' => 'episodes/audio.mp3',
            'cover_image' => null,
            'audio_url' => 'https://example.com/audio.mp3',
        ]],
        'podcast' => ['cover_image' => 'podcasts/show.webp'],
    ];

    $paths = $service->mediaPaths($archive);

    expect($paths)->toBe([
        'podcasts/show.webp',
        'shared/cover.webp',
    ]);
});

test('media paths read stored media paths and prefer them over an older cover_image', function (): void {
    $service = new PublicContentArchive;
    $archive = [
        'version' => 1,
        'posts' => [['slug' => 'park-story', 'featured_image_path' => 'posts/new.webp', 'cover_image' => 'posts/old.webp']],
        'guides' => [['slug' => 'park-guide', 'featured_image_path' => 'guides/guide.webp']],
        'episodes' => [['slug' => 'park-episode', 'featured_image_path' => 'episodes/episode.webp']],
        'podcast' => ['cover_image_path' => 'podcast/new.webp', 'cover_image' => 'podcast/old.webp'],
    ];

    expect($service->mediaPaths($archive))->toBe([
        'episodes/episode.webp',
        'guides/guide.webp',
        'podcast/new.webp',
        'posts/new.webp',
    ]);
});

test('archives without media return an empty list', function (): void {
    $service = new PublicContentArchive;
    $archive = [
        'version' => 1,
        'posts' => [['slug' => 'text-post', 'cover_image' => null]],
        'guides' => [],
        'episodes' => [],
        'podcast' => null,
    ];

    $paths = $service->mediaPaths($archive);

    expect($paths)->toBeEmpty();
});

test('unsafe media paths are rejected', function (mixed $path): void {
    $service = new PublicContentArchive;
    $archive = [
        'version' => 1,
        'posts' => [['slug' => 'unsafe-post', 'cover_image' => $path]],
        'guides' => [],
        'episodes' => [],
        'podcast' => null,
    ];

    expect(fn () => $service->mediaPaths($archive))
        ->toThrow(InvalidArgumentException::class, 'The public content archive contains an unsafe media path.');
})->with([
    'absolute path' => ['/private/cover.webp'],
    'parent directory' => ['../private/cover.webp'],
    'nested traversal' => ['posts/../../private/cover.webp'],
    'backslash' => ['posts\\cover.webp'],
    'null byte' => ["posts/cover\0.webp"],
    'newline' => ["posts/cover\n.webp"],
    'integer' => [42],
    'array' => [['posts/cover.webp']],
]);
