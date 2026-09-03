<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('unknown urls render the branded recovery page', function (): void {
    get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSee('<title>Page Not Found | Mouse28</title>', false)
        ->assertSee('<meta name="robots" content="none">', false)
        ->assertDontSee('<link rel="canonical"', false)
        ->assertSee('That page wandered off')
        ->assertSee('dispatch-error-sheet', false)
        ->assertSee('data-brand-wordmark', false)
        ->assertSee('js-dispatch-errors', false)
        ->assertSee(route('home'), false)
        ->assertSee(route('search'), false)
        ->assertSee(route('blog.index'), false)
        ->assertSee(route('guides.index'), false)
        ->assertSee(route('episodes.index'), false)
        ->assertSee('placeholder:text-navy/60', false)
        ->assertDontSee('placeholder:text-navy/35', false);
});

test('hidden content uses the same recovery page without revealing its title', function (): void {
    $draftPost = Post::factory()->draft()->create([
        'title' => 'Unannounced family update',
    ]);

    get(route('blog.show', $draftPost))
        ->assertNotFound()
        ->assertSee('That page wandered off')
        ->assertDontSee($draftPost->title);
});
