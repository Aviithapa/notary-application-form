<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the locale chosen by the visitor (stored in the session).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (! array_key_exists($locale, config('app.available_locales'))) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        // The app uses "np" for Nepali, Carbon only knows the ISO code "ne".
        Carbon::setLocale($locale === 'np' ? 'ne' : $locale);

        return $next($request);
    }
}
