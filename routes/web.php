<?php

use App\Http\Controllers\BlogRssController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PodcastFeedRedirectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PreviewEpisodeController;
use App\Http\Controllers\PreviewGuideController;
use App\Http\Controllers\PreviewPostController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{guide:slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/episodes', [EpisodeController::class, 'index'])->name('episodes.index');
Route::get('/episodes/{episode:slug}', [EpisodeController::class, 'show'])->name('episodes.show');
Route::get('/search', SearchController::class)->middleware('throttle:search')->name('search');
Route::middleware('signed')->prefix('preview')->group(function (): void {
    Route::get('/posts/{post:slug}', PreviewPostController::class)->name('preview.post');
    Route::get('/guides/{guide:slug}', PreviewGuideController::class)->name('preview.guide');
    Route::get('/episodes/{episode:slug}', PreviewEpisodeController::class)->name('preview.episode');
});
Route::view('/about', 'pages.about')->name('about');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('contact.store');

Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:newsletter')->name('newsletter.store');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/rss/blog', BlogRssController::class)->name('rss.blog');
Route::get('/rss/podcast', PodcastFeedRedirectController::class)->name('rss.podcast');
