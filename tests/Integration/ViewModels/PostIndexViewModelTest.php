<?php

use App\ViewModels\PostIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

covers(PostIndexViewModel::class);

pest()->use(RefreshDatabase::class);

test('blog index data normalizes filters and metadata', function (): void {
    $request = Request::create('/blog', 'GET', [
        'category' => 'disney-tips',
        'q' => str_repeat('a', 120),
        'sort' => 'oldest',
    ]);

    $data = app(PostIndexViewModel::class)->data($request);

    expect($data['category'])->toBe('disney-tips')
        ->and($data['search'])->toHaveLength(100)
        ->and($data['sort'])->toBe('oldest')
        ->and($data['robots'])->toBe('noindex,follow')
        ->and($data['canonicalUrl'])->toContain('category=disney-tips');
});
