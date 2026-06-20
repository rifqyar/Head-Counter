<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\Audit\AuditLogger;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    public function username()
    {
        return 'username';
    }

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function attemptLogin(Request $request): bool
    {
        $authenticated = $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );

        if (! $authenticated) {
            app(AuditLogger::class)->record('auth.login.failed', null, null, null, [
                'username' => $request->input($this->username()),
                'ip' => $request->ip(),
            ]);
        }

        return $authenticated;
    }

    protected function authenticated(Request $request, $user): void
    {
        $user->forceFill(['last_login_at' => now()])->save();

        app(AuditLogger::class)->record('auth.login.succeeded', $user->hotel_id, $user->id, $user, [
            'username' => $user->username,
        ]);
    }

    protected function credentials(Request $request): array
    {
        return array_merge($request->only($this->username(), 'password'), ['status' => 'ACTIVE']);
    }

    protected function loggedOut(Request $request): void
    {
        app(AuditLogger::class)->record('auth.logout', null, null, null, [
            'ip' => $request->ip(),
        ]);
    }
}
