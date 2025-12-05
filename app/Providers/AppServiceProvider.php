<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // [Tambahkan ini]

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Paksa HTTPS jika aplikasi berjalan di environment production (Vercel)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
