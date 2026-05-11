<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class RoleMiddleware
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $this->errorResponse(
                'Unauthenticated.',
                null,
                401
            );
        }

        if (!in_array($user->role, $roles)) {
            return $this->errorResponse(
                'Unauthorized access.',
                null,
                403
            );
        }

        return $next($request);
    }
}