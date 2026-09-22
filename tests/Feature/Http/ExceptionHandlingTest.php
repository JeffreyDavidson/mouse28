<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('unknown URLs render the branded recovery page', function (): void {
    get('/this-page-does-not-exist')->assertNotFound()->assertSeeHtml('<title>Page Not Found | Mouse28</title>')->assertSee('That page wandered off')->assertSeeHtml('dispatch-error-sheet')->assertSeeHtml('data-brand-wordmark')->assertSeeHtml('js-dispatch-errors')->assertSeeHtml(route('home'))->assertSeeHtml(route('search'))->assertSeeHtml(route('blog.index'))->assertSeeHtml(route('guides.index'))->assertSeeHtml(route('episodes.index'))->assertSeeHtml('placeholder:text-navy/60')->assertDontSeeHtml('placeholder:text-navy/35');
});

test('expired sessions explain how to recover', function (): void {
    Route::get('/testing/expired-session', fn () => abort(419, 'Private session details'));

    get('/testing/expired-session')->assertStatus(419)->assertSeeHtml('<title>Page Expired | Mouse28</title>')->assertSee('Your session took a break')->assertSeeHtml('dispatch-error-recovery')->assertSee('Return to the site')
        ->assertDontSee('Private session details');
});

test('failed newsletter submissions link to a GET form rather than replaying the POST', function (int $status): void {
    config()->set('app.debug', false);
    Route::post('/newsletter', fn () => abort($status));

    post('/newsletter')
        ->assertStatus($status)
        ->assertSeeHtml('href="'.route('home').'#newsletter"')
        ->assertSee('Return to newsletter');
})->with([419, 500, 503]);

test('an expired admin session links back to admin login', function (): void {
    Route::post('/admin/testing-expired', fn () => abort(419));

    post('/admin/testing-expired')
        ->assertStatus(419)
        ->assertSee('Return to admin login')
        ->assertDontSee('Return to contact');
});

test('unexpected errors render a safe branded response', function (): void {
    config()->set('app.debug', false);
    Route::get('/testing/server-error', function (): never {
        throw new RuntimeException('Sensitive database connection details');
    });

    get('/testing/server-error')->assertStatus(500)->assertSeeHtml('<title>Something Went Wrong | Mouse28</title>')->assertDontSeeHtml('fonts.googleapis.com')->assertSee('The magic hit a snag')->assertSeeHtml('dispatch-error-marker')->assertSeeHtml('data-brand-wordmark')
        ->assertDontSee('Sensitive database connection details');
});

test('maintenance responses offer a safe retry path', function (): void {
    Route::get('/testing/maintenance', fn () => abort(503, 'Private maintenance details'));

    get('/testing/maintenance')->assertStatus(503)->assertSeeHtml('<title>We’ll Be Right Back | Mouse28</title>')->assertSee('We’re making a little magic')->assertSeeHtml('dispatch-error-sheet')->assertSeeHtml('/testing/maintenance')
        ->assertDontSee('Private maintenance details');
});
