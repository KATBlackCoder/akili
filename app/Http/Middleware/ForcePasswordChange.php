<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user() &&
            $request->user()->must_change_password &&
            ! $request->is('first-login') &&
            ! $request->routeIs('logout')
        ) {
            if ($request->header('HX-Request')) {
                return response('', 204)
                    ->header('HX-Redirect', route('first-login'));
            }

            return redirect()->route('first-login');
        }

        return $next($request);
    }
}
