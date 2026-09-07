<?php

use App\Support\PublicContentArchive;

test('media paths include all content types once in sorted order', function (): void {
    $service = new PublicContentArchive;
    $archive = [
        'version' => 1,
        'posts' => [[
            'slug' => 'park-story',
            'cover_image' => 'shared/cover.webp',
            'og_image' => 'posts/social.webp',
            'source_url' => 'https://example.com/source',
        ]],
        'guides' => [[
            'slug' => 'park-guide',
            'cover_image' => 'shared/cover.webp',
            'og_image' => '',
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
        'episodes/audio.mp3',
        'podcasts/show.webp',
        'posts/social.webp',
        'shared/cover.webp',
    ]);
});

test('archives without media return an empty list', function (): void {
    $service = new PublicContentArchive;
    $archive = [
        'version' => 1,
        'posts' => [['slug' => 'text-post', 'cover_image' => null, 'og_image' => '']],
        'guides' => [],
        'episodes' => [],
        'podcast' => null,
    ];

    $paths = $service->mediaPaths($archive);

    expect($paths)->toBe([]);
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
