<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('ingest', function (Request $request): Limit {
            $perMinute = max(1, (int) config('services.ingest.rate_per_minute', 60));

            return Limit::perMinute($perMinute)->by($request->bearerToken() ?: $request->ip());
        });
    }
}
