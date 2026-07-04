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
        // Force HTTPS for all URLs when running in production (behind Cloudflare proxy).
        // Skipped in local dev, since the local server doesn't speak SSL/TLS.
        if (! $this->app->environment('local')) {
            \URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
    }
}
