<?php

namespace App\Http\Middleware;

use App\Services\IntegrationApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateIntegrationApiKey
{
    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        $secret = $request->header('X-API-Key');
        if (! $secret) {
            abort(401);
        }

        $hotelId = $request->user()?->hotel_id;
        $key = app(IntegrationApiKeyService::class)->validate($secret, $ability, $hotelId);
        if (! $key) {
            abort(401);
        }

        $request->attributes->set('integration_api_key', $key);

        return $next($request);
    }
}
