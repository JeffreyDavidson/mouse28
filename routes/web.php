<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PreviewEpisodeController;
use App\Http\Controllers\PreviewGuideController;
use App\Http\Controllers\PreviewPostController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{guide:slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/episodes', [EpisodeController::class, 'index'])->name('episodes.index');
Route::get('/episodes/{episode:slug}', [EpisodeController::class, 'show'])->name('episodes.show');
Route::get('/search', SearchController::class)->name('search');
Route::get('/preview/posts/{post}', PreviewPostController::class)->name('preview.posts');
Route::get('/preview/guides/{guide}', PreviewGuideController::class)->name('preview.guides');
Route::get('/preview/episodes/{episode}', PreviewEpisodeController::class)->name('preview.episodes');
Route::get('/about', fn () => view('about'))->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('contact.store');

Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:newsletter')->name('newsletter.store');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/rss/blog', [RssController::class, 'blog'])->name('rss.blog');
Route::get('/rss/podcast', [RssController::class, 'podcast'])->name('rss.podcast');
