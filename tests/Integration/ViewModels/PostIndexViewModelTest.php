<?php

use App\Models\Category;
use App\ViewModels\PostIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        ->and($data['search'])
        ->toHaveLength(100)
        ->and($data['sort'])
        ->toBe('oldest')
        ->and($data['robots'])
        ->toBe('noindex,follow')
        ->and($data['canonicalUrl'])
        ->toContain('category=disney-tips');
});

test('blog index data names the selected category in its metadata', function (): void {
    $category = Category::factory()->create(['name' => 'Sample Topic', 'slug' => 'sample-topic']);
    $request = Request::create('/blog', 'GET', ['category' => $category->slug, 'page' => 2]);

    $data = app(PostIndexViewModel::class)->data($request);

    expect($data['category'])->toBe('sample-topic')
        ->and($data['pageTitle'])
        ->toBe('Sample Topic | Mouse28')
        ->and($data['pageDescription'])
        ->toBe('Mouse28 Sample Topic articles, family experiences, and practical Disney park takeaways.')
        ->and($data['canonicalUrl'])
        ->toBe(route('blog.index', ['category' => 'sample-topic', 'page' => 2]))
        ->and($data['robots'])
        ->toBe('index,follow');
});

test('blog index data ignores a category that does not exist', function (): void {
    $request = Request::create('/blog', 'GET', ['category' => 'not-a-category']);

    $data = app(PostIndexViewModel::class)->data($request);

    expect($data['category'])->toBeEmpty()
        ->and($data['pageTitle'])
        ->toBe('Disney Parks Blog | Mouse28')
        ->and($data['canonicalUrl'])
        ->toBe(route('blog.index'));
});

test('blog index data looks up no category when none is requested', function (): void {
    DB::enableQueryLog();

    $data = app(PostIndexViewModel::class)->data(Request::create('/blog'));

    expect($data['category'])->toBeEmpty()
        ->and(DB::getQueryLog())
        ->toBeEmpty();
});
