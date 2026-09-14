<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\LanguageController;

Auth::routes([
    'register' => false,
]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/movies', [App\Http\Controllers\MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/{movie:slug}', [App\Http\Controllers\MovieController::class, 'show'])->name('movies.show');
Route::get('/downloads/{link}', [App\Http\Controllers\DownloadController::class, 'redirect'])->name('downloads.redirect');

Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('movies', MovieController::class);
    Route::post('movies/{movie}/links', [App\Http\Controllers\Admin\MovieLinkController::class, 'store'])->name('movies.links.store');
    Route::delete('movies/{movie}/links/{link}', [App\Http\Controllers\Admin\MovieLinkController::class, 'destroy'])->name('movies.links.destroy');
    Route::resource('genres', GenreController::class);
    Route::resource('languages', LanguageController::class);
});
