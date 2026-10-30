<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // each limiter has its own counter, so using one form never blocks another
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(10)->by('login|' . $request->ip()));
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by('contact|' . $request->ip()));
        RateLimiter::for('inquiry', fn (Request $request) => Limit::perMinute(5)->by('inquiry|' . $request->ip()));
        RateLimiter::for('newsletter', fn (Request $request) => Limit::perMinute(5)->by('newsletter|' . $request->ip()));
        RateLimiter::for('results', fn (Request $request) => Limit::perMinute(10)->by('results|' . $request->ip()));
    }
}
