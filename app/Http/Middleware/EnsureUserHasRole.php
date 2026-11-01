<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage: ->middleware('role:applicant') or 'role:admin,super_admin'.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! in_array($request->user()?->mainRole()?->name, $roles, true)) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
