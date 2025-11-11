<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Limitar la ruta de login: 10 intentos por minuto por IP y por email
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute(10)->by(strtolower($request->input('email', 'no-email'))),
            ];
        });

        // (Opcional) Limitar forgot-password
        RateLimiter::for('password-email', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });
    }
}
