<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $u = auth()->user();

        $rolName = match ((int) $u->user_rol) {
            1 => 'Admin',
            2 => 'Usuario',
            default => 'Desconocido',
        };

        session([
            'auth.id'        => $u->user_id,
            'auth.email'     => $u->email,
            'auth.name'      => $u->name,
            'auth.rol_id'    => (int) $u->user_rol,
            'auth.rol_name'  => $rolName,
            'auth.empresa'   => $u->user_empresa,
            'auth.foto'      => $u->user_foto,
            'auth.situacion' => (bool) $u->user_situacion,
            'auth.ip'        => $request->ip(),
            'auth.login_at'  => now()->toDateTimeString(),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
