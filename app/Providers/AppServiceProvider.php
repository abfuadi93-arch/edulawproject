<?php

namespace App\Providers;

use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        View::composer('*', function ($view): void {
            static $siteSettings = null;

            $siteSettings ??= EdulawSite::settings();

            $view->with('siteSettings', $siteSettings);
        });
    }
}
