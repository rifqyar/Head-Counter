<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isActive') && ! $user->isActive()) {
            if ($request->expectsJson()) {
                abort(403);
            }

            auth()->logout();
            $request->session()?->invalidate();
            $request->session()?->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'These credentials do not match our records.']);
        }

        return $next($request);
    }
}
