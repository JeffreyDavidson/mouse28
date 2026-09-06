<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

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
