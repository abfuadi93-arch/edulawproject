<?php

use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\MultimediaController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tentang', [PageController::class, 'about'])->name('about');
Route::get('/profil/{author:slug}', [ProfileController::class, 'show'])->name('profiles.show');
Route::get('/kolaborasi', [CollaborationController::class, 'index'])->name('collaboration.index');
Route::post('/kolaborasi', [CollaborationController::class, 'store'])->name('collaboration.store');
Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');
Route::view('/kebijakan-privasi', 'pages.privacy')->name('privacy');
Route::view('/syarat-ketentuan', 'pages.terms')->name('terms');

Route::get('/insight', [InsightController::class, 'index'])->name('insights.index');
Route::get('/insight/{slug}', [InsightController::class, 'show'])->name('insights.show');

Route::get('/riset-publikasi', [PublicationController::class, 'index'])->name('publications.index');
Route::get('/riset-publikasi/{slug}', [PublicationController::class, 'show'])->name('publications.show');
Route::redirect('/publikasi', '/riset-publikasi', 301);
Route::redirect('/publikasi/{slug}', '/riset-publikasi/{slug}', 301);

Route::get('/program', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/program/archive', [ProgramController::class, 'archive'])->name('programs.archive');
Route::get('/program/{slug}', [ProgramController::class, 'show'])->name('programs.show');

Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
Route::redirect('/peluang', '/opportunities', 301);
Route::get('/peluang/{slug}', function () {
    return redirect('/opportunities', 301);
});

Route::get('/multimedia', [MultimediaController::class, 'index'])->name('multimedia.index');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
