<?php

use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageVariantController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\MultimediaController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\TrackPageVisit;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', fn () => response(
    file_get_contents(public_path('robots.txt')),
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8'],
))->name('robots');

Route::get('/media/image/{token}/{width}.webp', ImageVariantController::class)
    ->where('token', '[A-Za-z0-9_-]+')
    ->whereNumber('width')
    ->name('media.variant');

Route::middleware(TrackPageVisit::class)->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/tentang', [PageController::class, 'about'])->name('about');
    Route::get('/profil/{slug}', [ProfileController::class, 'show'])->name('profiles.show');
    Route::get('/kolaborasi', [CollaborationController::class, 'index'])->name('collaboration.index');
    Route::post('/kolaborasi', [CollaborationController::class, 'store'])->name('collaboration.store');
    Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
    Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');
    Route::view('/kebijakan-privasi', 'pages.privacy')->name('privacy');
    Route::view('/syarat-ketentuan', 'pages.terms')->name('terms');
    Route::view('/standar-editorial', 'pages.editorial-standards')->name('editorial-standards');
    Route::view('/kebijakan-koreksi', 'pages.corrections-policy')->name('corrections-policy');

    Route::get('/insight', [InsightController::class, 'index'])->name('insights.index');
    Route::get('/insight/kategori/{categorySlug}', [InsightController::class, 'category'])
        ->where('categorySlug', 'law-governance|legal-101|regulatory-update|edulaw-insight')
        ->name('insights.categories.show');
    Route::get('/insight/{slug}', [InsightController::class, 'show'])->name('insights.show');

    Route::get('/riset-publikasi', [PublicationController::class, 'index'])->name('publications.index');
    Route::get('/riset-publikasi/{slug}/preview', [PublicationController::class, 'preview'])->name('publications.preview');
    Route::get('/riset-publikasi/{slug}/download', [PublicationController::class, 'download'])->name('publications.download');
    Route::get('/riset-publikasi/{slug}', [PublicationController::class, 'show'])->name('publications.show');
    Route::redirect('/publikasi', '/riset-publikasi', 301);
    Route::redirect('/publikasi/{slug}', '/riset-publikasi/{slug}', 301);

    Route::get('/program', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('/program/archive', [ProgramController::class, 'archive'])->name('programs.archive');
    Route::get('/program/{slug}', [ProgramController::class, 'show'])->name('programs.show');

    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/{slug}', [OpportunityController::class, 'retired'])->name('opportunities.show');
    Route::redirect('/peluang', '/opportunities', 301);

    Route::get('/multimedia', [MultimediaController::class, 'index'])->name('multimedia.index');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::get('/sitemap.xml', [SitemapController::class, 'index'])
        ->name('sitemap');
});
