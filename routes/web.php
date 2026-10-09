<?php

use App\Http\Controllers\BlogRssController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterConfirmationController;
use App\Http\Controllers\NewsletterIssueController;
use App\Http\Controllers\NewsletterOneClickUnsubscriptionController;
use App\Http\Controllers\NewsletterRssController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NewsletterUnsubscriptionController;
use App\Http\Controllers\PodcastFeedRedirectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PreviewEpisodeController;
use App\Http\Controllers\PreviewGuideController;
use App\Http\Controllers\PreviewNewsletterIssueController;
use App\Http\Controllers\PreviewPostController;
use App\Http\Controllers\ResendWebhookController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureValidNewsletterConfirmationLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{guide:slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/episodes', [EpisodeController::class, 'index'])->name('episodes.index');
Route::get('/episodes/{episode:slug}', [EpisodeController::class, 'show'])->name('episodes.show');
Route::get('/search', SearchController::class)
    ->middleware('throttle:search')
    ->name('search');
Route::middleware('signed')
    ->prefix('preview')
    ->group(function (): void {
        Route::get('/posts/{post:slug}', PreviewPostController::class)->name('preview.post');
        Route::get('/guides/{guide:slug}', PreviewGuideController::class)->name('preview.guide');
        Route::get('/episodes/{episode:slug}', PreviewEpisodeController::class)->name('preview.episode');
        Route::get('/newsletter/{newsletterIssue:slug}', PreviewNewsletterIssueController::class)->name('preview.newsletter-issue');
    });
Route::view('/about', 'pages.about')->name('about');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact-form')
    ->name('contact.store');

Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])
    ->middleware('throttle:newsletter')
    ->name('newsletter.subscribe');
// The middleware checks the signature and token; an unusable link, including
// one whose subscriber was removed, goes back to the sign-up form.
Route::middleware([EnsureValidNewsletterConfirmationLink::class, 'throttle:newsletter-confirm'])
    ->missing(fn (): RedirectResponse => EnsureValidNewsletterConfirmationLink::redirectToSignupForm())
    ->group(function (): void {
        // Not stored anywhere, so the back button reloads the page instead of restoring
        // a form already submitted, and the CSRF token and email are never cached.
        Route::get('/newsletter/confirm/{subscriber}/{token}', [NewsletterConfirmationController::class, 'create'])
            ->middleware('cache.headers:no_store;private')
            ->name('newsletter.confirm');
        Route::post('/newsletter/confirm/{subscriber}/{token}', [NewsletterConfirmationController::class, 'store'])
            ->name('newsletter.confirm.store');
    });
Route::view('/newsletter/confirmed', 'pages.newsletter.confirmed')->name('newsletter.confirmed');
// Shows the subscriber's email and a CSRF token, so it is never cached either.
Route::get('/newsletter/unsubscribe/{subscriber}', [NewsletterUnsubscriptionController::class, 'create'])
    ->middleware(['signed', 'throttle:newsletter-unsubscribe', 'cache.headers:no_store;private'])
    ->name('newsletter.unsubscribe');
Route::delete('/newsletter/unsubscribe/{subscriber}', [NewsletterUnsubscriptionController::class, 'destroy'])
    ->middleware(['signed', 'throttle:newsletter-unsubscribe'])
    ->name('newsletter.unsubscribe.store');
Route::post('/newsletter/unsubscribe/{subscriber}', NewsletterOneClickUnsubscriptionController::class)
    ->middleware(['signed', 'throttle:newsletter-unsubscribe'])
    ->name('newsletter.unsubscribe.oneClick');

Route::get('/newsletter', [NewsletterIssueController::class, 'index'])->name('newsletter.index');
// Feed readers poll this: no session, so a hit writes no session row or cookie.
Route::get('/newsletter/rss', NewsletterRssController::class)
    ->withoutMiddleware('web')
    ->name('newsletter.rss');
Route::get('/newsletter/{newsletterIssue:slug}', [NewsletterIssueController::class, 'show'])->name('newsletter.issue');

// Crawlers poll this: no session, so a hit writes no session row or cookie.
Route::get('/sitemap.xml', SitemapController::class)
    ->withoutMiddleware('web')
    ->name('sitemap');
// Served like a static file (no session cookies, publicly cacheable) so crawlers and caches keep it.
Route::get('/robots.txt', RobotsController::class)
    ->withoutMiddleware('web')
    ->name('robots');
// Server-to-server: no session, cookies or forgery token; the Resend signature is the credential.
Route::post('/webhooks/resend', ResendWebhookController::class)
    ->withoutMiddleware('web')
    ->middleware('throttle:resend-webhook')
    ->name('webhooks.resend');
// Feed readers and podcast apps poll /rss/blog and /rss/podcast: no session, so a hit writes no session row or cookie.
Route::get('/rss/blog', BlogRssController::class)
    ->withoutMiddleware('web')
    ->name('rss.blog');
Route::get('/rss/podcast', PodcastFeedRedirectController::class)
    ->withoutMiddleware('web')
    ->name('rss.podcast');
