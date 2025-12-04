<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComicController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Make manga detail public so users can view covers and chapters without login
Route::get('/comics/{mangaId}', [ComicController::class, 'showChapters'])->name('comics.showChapters');
// Public proxy endpoint for cover images to avoid client-side blocking (adblock/CORS)
Route::get('/comics/cover-proxy', [ComicController::class, 'proxyChapterImage'])->name('comics.coverProxy');

// Make index public so users can view list without login
Route::get('/comics', [ComicController::class, 'index'])->name('comics.index');

Route::middleware('auth')->group(function () {
    Route::get('/comics/chapter/{chapterId}', [ComicController::class, 'readChapter'])->name('comics.readChapter');
    Route::get('/comics/proxy-image', [ComicController::class, 'proxyChapterImage'])->name('comics.proxyImage');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
