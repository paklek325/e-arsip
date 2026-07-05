<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Force HTTPS when APP_URL uses https, so route() generates correct URLs
        // behind Cloudflare even if APP_ENV is not set to production.
        if (str_starts_with(config('app.url', ''), 'https://')) {
            \URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
    }
}
