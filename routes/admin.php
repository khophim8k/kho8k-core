<?php

use Illuminate\Support\Facades\Route;
use CKSource\CKFinderBridge\Controller\CKFinderController;
use Kho8k\Core\Controllers\Admin\ThemeManagementController;
use Kho8k\Core\Controllers\VastController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'Kho8k\Core\Controllers\Admin',
], function () {
    if (config('backpack.base.setup_dashboard_routes')) {
        Route::get('dashboard', 'AdminController@dashboard')->name('backpack.dashboard');
        Route::get('/', 'AdminController@redirect')->name('backpack');
    }

    Route::crud('catalog', 'CatalogCrudController');
    Route::crud('category', 'CategoryCrudController');
    Route::crud('region', 'RegionCrudController');
    Route::crud('movie', 'MovieCrudController');
    Route::crud('actor', 'ActorCrudController');
    Route::crud('director', 'DirectorCrudController');
    Route::crud('studio', 'StudioCrudController');
    Route::crud('tag', 'TagCrudController');
    Route::crud('menu', 'MenuCrudController');
    Route::crud('episode', 'EpisodeCrudController');
    Route::crud('theme', 'ThemeManagementController');
    Route::crud('sitemap', 'SiteMapController');
    Route::get('quick-action/delete-cache', 'QuickActionController@delete_cache');
    Route::get('quick-action/updateCMS', 'QuickActionController@updateCMS');
    Route::get('quick-action/relink', 'QuickActionController@reLinkStorage');
    Route::get('quick-action/refile', 'QuickActionController@refile');
});

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        [
            \Kho8k\Core\Middleware\SetLocale::class,
            \Kho8k\Core\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class
        ],
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    Route::prefix('/ckfinder')->group(function () {
        Route::any('/connector', [CKFinderController::class, 'requestAction'])->name('ckfinder_connector');
        Route::any('/browser', [CKFinderController::class, 'browserAction'])->name('ckfinder_browser');
    });
});
Route::get('/kichi', function () {
    return view('themes::kichi');
});
Route::get('set-locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) {
        session(['locale' => $locale]);
    }
  
    return redirect()->back();
})->name('set-locale');