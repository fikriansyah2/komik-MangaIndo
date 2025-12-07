<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AiChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/comics', [ComicController::class, 'index'])->name('comics.index');
    Route::get('/comics/{mangaId}', [ComicController::class, 'showChapters'])->name('comics.showChapters');
    Route::get('/comics/chapter/{chapterId}', [ComicController::class, 'readChapter'])->name('comics.readChapter');
    Route::get('/comics/proxy-image', [ComicController::class, 'proxyChapterImage'])->name('comics.proxyImage');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public routes for cover image proxy (no auth required, can be cached)
Route::get('/comics/cover-proxy', [ComicController::class, 'proxyCoverImage'])->name('comics.coverProxy');

require __DIR__.'/auth.php';

// AI Chat endpoint (uses server-side proxy to OpenAI)
// Public: visitors allowed (rate-limited). Authenticated users receive higher quota.
Route::middleware(['throttle:ai-chat'])->post('/ai/chat', [AiChatController::class, 'chat'])->name('ai.chat');
