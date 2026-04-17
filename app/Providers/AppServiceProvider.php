<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forzar HTTPS cuando el webserver esta tras proxy SSL (nginx/haproxy).
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
