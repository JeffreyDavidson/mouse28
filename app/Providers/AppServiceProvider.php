<?php

namespace App\Providers;

use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use App\Support\Monitoring\Nightwatch\RedactNightwatchCacheEvent;
use App\Support\Monitoring\Nightwatch\RedactNightwatchCommand;
use App\Support\Monitoring\Nightwatch\RedactNightwatchException;
use App\Support\Monitoring\Nightwatch\RedactNightwatchOutgoingRequest;
use App\Support\Monitoring\Nightwatch\RedactNightwatchQuery;
use App\Support\Monitoring\Nightwatch\RedactNightwatchRequest;
use App\Support\Monitoring\Nightwatch\ResolveNightwatchUser;
use App\Support\Monitoring\Sentry\RedactSentryBreadcrumb;
use App\Support\Monitoring\Sentry\RedactSentryEvent;
use App\Support\SafeReturnUrl;
use App\View\Composers\PodcastComposer;
use App\View\Composers\SocialProfilesComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Facades\Nightwatch;
use Sentry\ClientBuilder;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(ClientBuilder::class, function (ClientBuilder $clientBuilder): void {
            $options = $clientBuilder->getOptions();
            $options->setBeforeSendCallback($this->app->make(RedactSentryEvent::class));
            $options->setBeforeBreadcrumbCallback($this->app->make(RedactSentryBreadcrumb::class));
        });
    }

    public function boot(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());
        Model::preventLazyLoading(! $this->app->isProduction());

        Nightwatch::user(app(ResolveNightwatchUser::class));
        Nightwatch::redactCacheEvents(app(RedactNightwatchCacheEvent::class));
        Nightwatch::redactCommands(app(RedactNightwatchCommand::class));
        Nightwatch::redactExceptions(app(RedactNightwatchException::class));
        Nightwatch::redactOutgoingRequests(app(RedactNightwatchOutgoingRequest::class));
        Nightwatch::redactQueries(app(RedactNightwatchQuery::class));
        Nightwatch::redactRequests(app(RedactNightwatchRequest::class));

        Event::listen(DiagnosingHealth::class, function (): void {
            $migrations = DB::table('migrations');
            $migrations->limit(1);
            $migrations->exists();

            if (Config::boolean('health.runtime.enabled')) {
                app(RuntimeHealthMonitor::class)->ensureHealthy();
            }
        });

        View::composer('components.layouts.app', PodcastComposer::class);
        View::composer('components.layouts.app', SocialProfilesComposer::class);

        if (str_starts_with(Config::string('app.url'), 'https://')) {
            URL::forceRootUrl(Config::string('app.url'));
            URL::forceScheme('https');
        }

        RateLimiter::for('contact-form', fn (Request $request) => Limit::perMinute(Config::integer('mouse28.rate_limits.contact_form_per_minute'))->by($request->ip())->response(fn (Request $request) => redirect()->route('contact.create')
            ->withErrors(['contact_rate_limit' => 'Too many contact attempts. Please wait a minute and try again.'], 'contact')
            ->withInput($request->only(['name', 'email', 'subject', 'message']))));

        RateLimiter::for('newsletter', fn (Request $request) => Limit::perMinute(Config::integer('mouse28.rate_limits.newsletter_per_minute'))->by($request->ip())->response(fn (Request $request) => redirect(SafeReturnUrl::from($request, route('home')).'#newsletter')
            ->withErrors(['newsletter_rate_limit' => 'Too many signup attempts. Please wait a minute and try again.'], 'newsletter')
            ->withInput($request->only('email'))));

        RateLimiter::for('newsletter-delivery', fn (): Limit => Limit::perSecond(Config::integer('mouse28.rate_limits.newsletter_delivery_per_second')));

        RateLimiter::for('newsletter-confirm', fn (Request $request): Limit => Limit::perMinute(Config::integer('mouse28.rate_limits.newsletter_confirm_per_minute'))->by($request->ip()));

        RateLimiter::for('resend-webhook', fn (Request $request): Limit => Limit::perMinute(Config::integer('mouse28.rate_limits.resend_webhook_per_minute'))->by($request->ip()));

        RateLimiter::for('search', function (Request $request): Limit {
            if (blank($request->query('q'))) {
                return Limit::none();
            }

            return Limit::perMinute(Config::integer('mouse28.rate_limits.search_per_minute'))->by($request->ip());
        });
    }
}
