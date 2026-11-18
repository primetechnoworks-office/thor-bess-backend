<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        // The Next.js server forwards website form posts; trusting it means rate limits and
        // lead records use the visitor's IP from X-Forwarded-For instead of the server's.
        if ($proxies = config('thor.trusted_proxies')) {
            TrustProxies::at($proxies);
        }

        // Website forms: 5 submissions / 10 minutes / IP (same as the Next.js limiter).
        RateLimiter::for('leads', fn (Request $request) => Limit::perMinutes(10, 5)
            ->by('leads:'.$request->ip())
            ->response(fn () => response()->json(['ok' => false, 'error' => 'Too many requests. Please try again in a few minutes.'], 429)));

        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
    }
}
