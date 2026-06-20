<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserTokenRequest;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class UserTokenController extends Controller
{
    public function store(CreateUserTokenRequest $request, User $user, AuditLogger $auditLogger)
    {
        $data = $request->validated();
        $expiresAt = isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null;
        $token = $user->createToken($data['name'], $data['abilities'], $expiresAt);

        $auditLogger->record('user.api_token.created', $user->hotel_id, $request->user()->id, $user, [
            'token_id' => $token->accessToken->id,
            'abilities' => $data['abilities'],
            'expires_at' => $expiresAt?->toIso8601String(),
        ]);

        return redirect()->route('users.show', $user)->with('status', 'API token created. Plain text token is shown once.')->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, User $user, PersonalAccessToken $token, AuditLogger $auditLogger)
    {
        $this->authorize('manage', $user);
        abort_unless((int) $token->tokenable_id === (int) $user->id && $token->tokenable_type === User::class, 404);

        $tokenId = $token->id;
        $token->delete();
        $auditLogger->record('user.api_token.revoked', $user->hotel_id, $request->user()->id, $user, ['token_id' => $tokenId]);

        return back()->with('status', 'API token revoked.');
    }

    public function destroyAll(Request $request, User $user, AuditLogger $auditLogger)
    {
        $this->authorize('manage', $user);
        $count = $user->tokens()->count();
        $user->tokens()->delete();
        $auditLogger->record('user.tokens_revoked', $user->hotel_id, $request->user()->id, $user, ['count' => $count]);

        return back()->with('status', 'All API tokens revoked.');
    }
}
