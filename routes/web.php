<?php

use App\Http\Controllers\BlogRssController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterConfirmationController;
use App\Http\Controllers\NewsletterIssueController;
use App\Http\Controllers\NewsletterRssController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NewsletterUnsubscriptionController;
use App\Http\Controllers\PodcastFeedRedirectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PreviewEpisodeController;
use App\Http\Controllers\PreviewGuideController;
use App\Http\Controllers\PreviewNewsletterIssueController;
use App\Http\Controllers\PreviewPostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use JeffreyDavidson\CreatorKit\Http\Controllers\RobotsTxtController;
use JeffreyDavidson\CreatorKit\Routing\NewsletterRoutes;

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

// The newsletter's subscription routes and the Resend webhook come from creator-kit: the
// package owns the URLs, route names, signature checks and limiter names; these are Mouse28's
// pages. They come before /newsletter/{newsletterIssue:slug}, so "confirmed" is never an issue slug.
NewsletterRoutes::register(
    subscribe: [NewsletterSubscriptionController::class, 'store'],
    confirm: [NewsletterConfirmationController::class, 'create'],
    confirmStore: [NewsletterConfirmationController::class, 'store'],
    confirmed: 'pages.newsletter.confirmed',
    unsubscribe: [NewsletterUnsubscriptionController::class, 'create'],
    unsubscribeDestroy: [NewsletterUnsubscriptionController::class, 'destroy'],
);

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
Route::get('/robots.txt', RobotsTxtController::class)
    ->withoutMiddleware('web')
    ->name('robots');
// Feed readers and podcast apps poll /rss/blog and /rss/podcast: no session, so a hit writes no session row or cookie.
Route::get('/rss/blog', BlogRssController::class)
    ->withoutMiddleware('web')
    ->name('rss.blog');
Route::get('/rss/podcast', PodcastFeedRedirectController::class)
    ->withoutMiddleware('web')
    ->name('rss.podcast');

// Contact notification emails sent before the inquiry page moved to creator-kit link to its old address.
Route::redirect('/admin/contact-inquiries/{record}', '/admin/contact-inquiries/{record}/edit')
    ->whereNumber('record')
    ->name('legacyContactInquiry');
