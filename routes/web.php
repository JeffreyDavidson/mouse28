<?php

use App\Http\Controllers\CommunityStoryController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/blog', [PostController::class, 'index']);
Route::get('/blog/{post:slug}', [PostController::class, 'show']);
Route::get('/episodes', [EpisodeController::class, 'index']);
Route::get('/episodes/{episode:slug}', [EpisodeController::class, 'show']);
Route::get('/stories', [CommunityStoryController::class, 'index']);
Route::get('/stories/share', [CommunityStoryController::class, 'create']);
Route::post('/stories', [CommunityStoryController::class, 'store']);
Route::get('/about', fn () => view('about'));
Route::get('/contact', [ContactController::class, 'show']);
Route::post('/contact', [ContactController::class, 'store']);

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/robots.txt', [SitemapController::class, 'robots']);
Route::get('/rss/blog', [RssController::class, 'blog']);
Route::get('/rss/podcast', [RssController::class, 'podcast']);
