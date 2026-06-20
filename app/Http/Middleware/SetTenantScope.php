<?php

namespace App\Http\Middleware;

use App\Domain\Hotel\Hotel;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $context = app(TenantContext::class);

        if (! $user) {
            return $next($request);
        }

        abort_unless($user->isActive(), 403);

        if ($user->isSuperAdmin() && $request->hasSession() && $request->session()->has('tenant_hotel_id')) {
            $hotel = Hotel::whereKey($request->session()->get('tenant_hotel_id'))->first();
            abort_unless($hotel && ($hotel->status->value ?? $hotel->status) === 'ACTIVE', 403);
            $context->set($hotel);

            return $next($request);
        }

        if (! $user->isSuperAdmin()) {
            $hotel = Hotel::withoutGlobalScopes()->find($user->hotel_id);
            abort_unless($hotel && ($hotel->status->value ?? $hotel->status) === 'ACTIVE', 403);
            $context->set($hotel);

            return $next($request);
        }

        $context->set($user->hotel);

        return $next($request);
    }
}
