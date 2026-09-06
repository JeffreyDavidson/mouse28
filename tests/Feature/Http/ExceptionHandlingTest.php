<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('unknown URLs render the branded recovery page', function (): void {
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

test('expired sessions explain how to recover', function (): void {
    Route::get('/testing/expired-session', fn () => abort(419, 'Private session details'));

    get('/testing/expired-session')
        ->assertStatus(419)
        ->assertSee('<title>Page Expired | Mouse28</title>', false)
        ->assertSee('Your session took a break')
        ->assertSee('dispatch-error-recovery', false)
        ->assertSee(route('contact.show'), false)
        ->assertDontSee('Private session details');
});

test('unexpected errors render a safe branded response', function (): void {
    config()->set('app.debug', false);
    Route::get('/testing/server-error', function (): never {
        throw new RuntimeException('Sensitive database connection details');
    });

    get('/testing/server-error')
        ->assertStatus(500)
        ->assertSee('<title>Something Went Wrong | Mouse28</title>', false)
        ->assertSee('<meta name="robots" content="none">', false)
        ->assertDontSee('<link rel="canonical"', false)
        ->assertDontSee('fonts.googleapis.com', false)
        ->assertSee('The magic hit a snag')
        ->assertSee('dispatch-error-marker', false)
        ->assertSee('data-brand-wordmark', false)
        ->assertDontSee('Sensitive database connection details');
});

test('maintenance responses offer a safe retry path', function (): void {
    Route::get('/testing/maintenance', fn () => abort(503, 'Private maintenance details'));

    get('/testing/maintenance')
        ->assertStatus(503)
        ->assertSee('<title>We’ll Be Right Back | Mouse28</title>', false)
        ->assertSee('We’re making a little magic')
        ->assertSee('dispatch-error-sheet', false)
        ->assertSee('/testing/maintenance', false)
        ->assertDontSee('Private maintenance details');
});
