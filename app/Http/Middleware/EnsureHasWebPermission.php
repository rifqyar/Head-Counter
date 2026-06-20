<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasWebPermission
{
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        foreach (explode('|', $permissions) as $permission) {
            if ($user->isSuperAdmin() || $user->hasPermissionTo($permission, 'web')) {
                return $next($request);
            }
        }

        abort(403);
    }
}
