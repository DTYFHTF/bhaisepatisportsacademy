<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Force https:// URL generation (asset(), url(), Filament's asset tags) in
        // production. Without this, Laravel derives the scheme from the request it
        // actually receives — if the reverse proxy terminates TLS and doesn't (or
        // can't be trusted to) forward X-Forwarded-Proto, every generated asset URL
        // comes out as http:// and gets silently blocked as mixed content by browsers,
        // leaving the page completely unstyled (this is what broke /admin).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perHour(20)->by($request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinutes(10, 10)->by($request->ip());
        });

        RateLimiter::for('restock', function (Request $request) {
            return Limit::perHour(10)->by($request->ip());
        });
    }
}
