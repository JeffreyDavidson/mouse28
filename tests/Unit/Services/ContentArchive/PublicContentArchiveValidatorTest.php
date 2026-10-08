<?php

use App\Services\ContentArchive\PublicContentArchiveValidator;

covers(PublicContentArchiveValidator::class);

test('archive versions must match the supported integer version', function (mixed $version): void {
    $validator = new PublicContentArchiveValidator;
    $archive = [
        'version' => $version,
        'posts' => [],
        'guides' => [],
        'episodes' => [],
        'podcast' => null,
    ];

    expect(fn () => $validator->validate($archive))
        ->toThrow(InvalidArgumentException::class, 'The public content archive version is not supported.');
})->with([
    'string version' => ['1'],
    'future version' => [2],
    'null version' => [null],
]);

test('unsafe media paths are rejected', function (mixed $path): void {
    $validator = new PublicContentArchiveValidator;
    $archive = [
        'version' => 1,
        'posts' => [['slug' => 'unsafe-post', 'cover_image' => $path]],
        'guides' => [],
        'episodes' => [],
        'podcast' => null,
    ];

    expect(fn () => $validator->validate($archive))
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
