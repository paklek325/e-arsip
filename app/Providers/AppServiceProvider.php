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
        // Force HTTPS hanya di production (untuk Cloudflare/reverse proxy).
        // Di local/testing tidak perlu agar php artisan serve tetap HTTP.
        if (!app()->isLocal() && str_starts_with(config('app.url', ''), 'https://')) {
            \URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
    }
}
