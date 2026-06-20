<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\TransientToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && ! $token instanceof TransientToken && ! $token->can($ability)) {
            abort(403);
        }

        return $next($request);
    }
}
