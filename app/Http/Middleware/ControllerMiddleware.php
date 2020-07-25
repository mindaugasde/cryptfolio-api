<?php

namespace App\Http\Middleware;

use Closure;

class ControllerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->isJson()) {
            return response()->json(['error' => 'Expecting Content-Type application/json'], 415);
        }

        return $next($request);
    }
}
