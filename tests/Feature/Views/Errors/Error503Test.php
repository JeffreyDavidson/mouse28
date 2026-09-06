<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

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
