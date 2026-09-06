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
        ->assertSee('<title>Something Went Wrong | Mouse28</title>', false)
        ->assertSee('<meta name="robots" content="none">', false)
        ->assertDontSee('<link rel="canonical"', false)
        ->assertDontSee('fonts.googleapis.com', false)
        ->assertSee('The magic hit a snag')
        ->assertSee('dispatch-error-marker', false)
        ->assertSee('data-brand-wordmark', false)
        ->assertDontSee('Sensitive database connection details');
});
