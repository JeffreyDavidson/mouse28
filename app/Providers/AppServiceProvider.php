<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceRootUrl((string) config('app.url'));
            URL::forceScheme('https');
        }

        RateLimiter::for('contact-form', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return back()
                    ->withErrors(['contact_rate_limit' => 'Too many contact attempts. Please wait a minute and try again.'])
                    ->withInput();
            });
        });
    }
}
