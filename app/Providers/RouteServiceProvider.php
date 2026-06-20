<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('attendance', function (Request $request) {
            $token = (string) $request->route('token');

            return Limit::perMinute(30)->by(hash('sha256', $token).'|'.$request->ip());
        });

        RateLimiter::for('scanner-validate', function (Request $request) {
            return Limit::perMinute(120)->by(implode('|', [
                $request->user()?->id ?: 'guest',
                $request->user()?->hotel_id ?: 'no-hotel',
                $request->input('device_id', 'no-device'),
            ]));
        });

        RateLimiter::for('scanner-redeem', function (Request $request) {
            return Limit::perMinute(240)->by(implode('|', [
                $request->user()?->id ?: 'guest',
                $request->user()?->hotel_id ?: 'no-hotel',
                $request->input('device_id', 'no-device'),
            ]));
        });

        RateLimiter::for('sensitive-admin', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->prefix('master-data')
                ->group(base_path('routes/masterdata.php'));

            Route::middleware('web')
                ->prefix('setting')
                ->group(base_path('routes/setting.php'));

            Route::middleware('web')
                ->prefix('transaction')
                ->group(base_path('routes/transaction.php'));
        });
    }
}
