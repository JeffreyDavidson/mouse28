<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ServiceErrorPageTest extends TestCase
{
    public function test_unexpected_errors_render_a_safe_branded_response(): void
    {
        config()->set('app.debug', false);
        Route::get('/testing/server-error', function (): never {
            throw new RuntimeException('Sensitive database connection details');
        });

        $this->get('/testing/server-error')
            ->assertStatus(500)
            ->assertSee('<title>Something Went Wrong — Mouse28</title>', false)
            ->assertSee('<meta name="robots" content="none">', false)
            ->assertDontSee('<link rel="canonical"', false)
            ->assertSee('The magic hit a snag')
            ->assertDontSee('Sensitive database connection details');
    }

    public function test_maintenance_responses_offer_a_safe_retry_path(): void
    {
        Route::get('/testing/maintenance', fn () => abort(503, 'Private maintenance details'));

        $this->get('/testing/maintenance')
            ->assertStatus(503)
            ->assertSee('<title>We’ll Be Right Back — Mouse28</title>', false)
            ->assertSee('We’re making a little magic')
            ->assertSee('/testing/maintenance', false)
            ->assertDontSee('Private maintenance details');
    }

    public function test_expired_sessions_explain_how_to_recover(): void
    {
        Route::get('/testing/expired-session', fn () => abort(419, 'Private session details'));

        $this->get('/testing/expired-session')
            ->assertStatus(419)
            ->assertSee('<title>Page Expired — Mouse28</title>', false)
            ->assertSee('Your session took a break')
            ->assertSee(route('contact.show'), false)
            ->assertDontSee('Private session details');
    }
}
