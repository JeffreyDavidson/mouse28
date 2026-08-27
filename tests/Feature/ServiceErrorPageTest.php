<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

test('unexpected errors render a safe branded response', function (): void {
    config()->set('app.debug', false);
    Route::get('/testing/server-error', function (): never {
        throw new RuntimeException('Sensitive database connection details');
    });

    get('/testing/server-error')
        ->assertStatus(500)
        ->assertSee('<title>Something Went Wrong — Mouse28</title>', false)
        ->assertSee('<meta name="robots" content="none">', false)
        ->assertDontSee('<link rel="canonical"', false)
        ->assertDontSee('fonts.googleapis.com', false)
        ->assertSee('The magic hit a snag')
        ->assertDontSee('Sensitive database connection details');
});

test('maintenance responses offer a safe retry path', function (): void {
    Route::get('/testing/maintenance', fn () => abort(503, 'Private maintenance details'));

    get('/testing/maintenance')
        ->assertStatus(503)
        ->assertSee('<title>We’ll Be Right Back — Mouse28</title>', false)
        ->assertSee('We’re making a little magic')
        ->assertSee('/testing/maintenance', false)
        ->assertDontSee('Private maintenance details');
});

test('expired sessions explain how to recover', function (): void {
    Route::get('/testing/expired-session', fn () => abort(419, 'Private session details'));

    get('/testing/expired-session')
        ->assertStatus(419)
        ->assertSee('<title>Page Expired — Mouse28</title>', false)
        ->assertSee('Your session took a break')
        ->assertSee(route('contact.show'), false)
        ->assertDontSee('Private session details');
});
