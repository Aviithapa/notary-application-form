<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Blocks changes to the applicant form once an application has been approved (see User::profileLocked()). */
class EnsureProfileEditable
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->profileLocked()) {
            return back()->with('error', __('Your details are locked because an application has been approved. Contact the council to correct them.'));
        }

        return $next($request);
    }
}
