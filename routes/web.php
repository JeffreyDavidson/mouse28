<?php

use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/episodes', [EpisodeController::class, 'index']);
Route::get('/episodes/{episode:slug}', [EpisodeController::class, 'show']);
Route::get('/blog', [PostController::class, 'index']);
Route::get('/blog/{post:slug}', [PostController::class, 'show']);
Route::get('/about', fn () => view('about'));
