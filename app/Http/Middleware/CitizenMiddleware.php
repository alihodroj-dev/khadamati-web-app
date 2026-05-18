<?php
// app/Http/Middleware/CitizenMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CitizenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()  && Auth::user()->role === 'citizen') {
            return $next($request);
        }

        abort(403, 'Access denied. Citizen area only.');
    }
}