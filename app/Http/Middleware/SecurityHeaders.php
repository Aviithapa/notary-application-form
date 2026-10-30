<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add browser security headers to every response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;
        // block clickjacking: only this site may frame its pages
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Content-Security-Policy', "frame-ancestors 'self'");
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), payment=(), usb=()');

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // don't advertise the PHP version
        $headers->remove('X-Powered-By');
        if (!headers_sent()) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}
