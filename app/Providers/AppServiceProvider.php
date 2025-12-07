<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        // Rate limiter for AI chat endpoint to prevent abuse
        // - Authenticated users get a higher quota
        // - Guests (by IP) get a lower quota
        RateLimiter::for('ai-chat', function (Request $request) {
            if ($user = $request->user()) {
                return Limit::perMinute(60)->by($user->id);
            }

            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
