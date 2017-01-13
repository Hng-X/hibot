<?php

namespace App\Http\Middleware;

use Closure;

class EventsMiddleware
{
    /**
     * Handle an incoming request. Handles verification and authentication of token
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->input("type") == "url_verification") {
            return response($request->input("challenge"), 200);
        }
        return $next($request);
    }
}
