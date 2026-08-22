<?php

namespace App\Providers;

use App\Support\SafeReturnUrl;
use App\View\Composers\PodcastComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        Event::listen(DiagnosingHealth::class, function (): void {
            DB::table('migrations')->limit(1)->exists();
        });

        View::composer('components.layouts.app', PodcastComposer::class);

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceRootUrl((string) config('app.url'));
            URL::forceScheme('https');
        }

        RateLimiter::for('contact-form', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $request) {
                return redirect()->route('contact.show')
                    ->withErrors(['contact_rate_limit' => 'Too many contact attempts. Please wait a minute and try again.'], 'contact')
                    ->withInput($request->only(['name', 'email', 'subject', 'message']));
            });
        });

        RateLimiter::for('newsletter', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $request) {
                return redirect(SafeReturnUrl::from($request, route('home')).'#newsletter')
                    ->withErrors(['newsletter_rate_limit' => 'Too many signup attempts. Please wait a minute and try again.'], 'newsletter')
                    ->withInput($request->only('email'));
            });
        });
    }
}
