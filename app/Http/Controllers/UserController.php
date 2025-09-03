<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('rol')->paginate(15);
        $roles = Rol::orderBy('nombre')->get(); // ← AGREGAR ESTA LÍNEA

        return view('usuarios.index', compact('usuarios', 'roles')); // ← PASAR $roles
    }


    public function indexMapa()
    {

        $usuarios = User::where('rol_id', 2)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('usuarios.mapa', compact('usuarios'));
    }

    public function create()
    {
        $roles = Rol::orderBy('nombre')->get();

        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol_id' => 'nullable|exists:roles,id',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'rol_id.exists' => 'El rol seleccionado no es válido.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $usuario)
    {
        $usuario->load(['rol', 'registrosConsumo' => function ($query) {
            $query->with('ingrediente')->orderBy('fecha_uso', 'desc')->limit(10);
        }]);

        // Estadísticas del usuario
        $estadisticas = [
            'total_consumos' => $usuario->registrosConsumo()->count(),
            'consumos_este_mes' => $usuario->registrosConsumo()
                ->whereMonth('fecha_uso', now()->month)
                ->whereYear('fecha_uso', now()->year)
                ->count(),
            'ingredientes_usados' => $usuario->registrosConsumo()
                ->distinct('ingrediente_id')
                ->count(),
            'ultimo_consumo' => $usuario->registrosConsumo()
                ->orderBy('fecha_uso', 'desc')
                ->first()?->fecha_uso,
        ];

        return view('usuarios.show', compact('usuario', 'estadisticas'));
    }

    public function edit(User $usuario)
    {
        $roles = Rol::orderBy('nombre')->get();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'rol_id' => 'nullable|exists:roles,id',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'rol_id.exists' => 'El rol seleccionado no es válido.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'rol_id' => $request->rol_id,
        ];

        // Solo actualizar la contraseña si se proporcionó una nueva
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        // Verificar si el usuario tiene registros de consumo
        if ($usuario->registrosConsumo()->count() > 0) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene registros de consumo asociados.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Cambiar rol de usuario
     */
    public function cambiarRol(Request $request, User $usuario)
    {
        $request->validate([
            'rol_id' => 'nullable|exists:roles,id',
        ]);

        $usuario->update(['rol_id' => $request->rol_id]);

        return redirect()->back()
            ->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Obtener usuarios para select/API
     */
    public function obtenerUsuarios()
    {
        $usuarios = User::select('id', 'name', 'email')
            ->with('rol:id,nombre')
            ->orderBy('name')
            ->get();

        return response()->json($usuarios);
    }

    /**
     * Estadísticas de usuarios
     */
    public function estadisticas()
    {
        $stats = [
            'total_usuarios' => User::count(),
            'usuarios_con_rol' => User::whereNotNull('rol_id')->count(),
            'usuarios_sin_rol' => User::whereNull('rol_id')->count(),
            'distribucion_roles' => Rol::withCount('usuarios')->get(),
            'usuarios_activos_mes' => User::whereHas('registrosConsumo', function ($query) {
                $query->whereMonth('fecha_uso', now()->month)
                    ->whereYear('fecha_uso', now()->year);
            })->count(),
        ];

        return response()->json($stats);
    }
}
