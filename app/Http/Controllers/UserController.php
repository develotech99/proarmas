<?php
// app/Http/Controllers/UsuarioController.php
namespace App\Http\Controllers;

use App\Mail\VerificarCorreoMailable;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $usuarios = User::with('rol')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('user_primer_nombre', 'like', "%{$q}%")
                        ->orWhere('user_segundo_nombre', 'like', "%{$q}%")
                        ->orWhere('user_primer_apellido', 'like', "%{$q}%")
                        ->orWhere('user_segundo_apellido', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('user_primer_nombre')
            ->paginate(15)
            ->withQueryString();

        $roles = Rol::orderBy('nombre')->get();

        // Contadores para las tarjetas
        $totalUsuarios     = $usuarios->total();
        $conRolAsignado    = User::whereNotNull('user_rol')->count();
        $registradosHoy    = User::whereDate('user_fecha_creacion', now()->toDateString())->count();

        return view('usuarios.index', compact(
            'usuarios',
            'roles',
            'q',
            'totalUsuarios',
            'conRolAsignado',
            'registradosHoy'
        ));
    }


    public function indexMapa()
    {

        $usuarios = User::where('user_rol', 2)
            ->select('user_id', 'user_primer_nombre', 'user_primer_apellido', 'user_empresa')
            ->orderBy('user_primer_nombre')
            ->get();

        return view('usuarios.mapa', compact('usuarios'));
    }

    public function confirmEmailSucess(Request $request)
    {
        return view('emails.confirmEmailRegister');
    }

    public function getUsers(Request $request)
    {
        try {
            $search = $request->get('search');
            $rol = $request->get('rol');

            $usuarios = User::with('rol')
                ->where('user_situacion', 1)
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('user_primer_nombre', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->when($rol, function ($query) use ($rol) {
                    $query->whereHas('rol', function ($q) use ($rol) {
                        $q->where('nombre', $rol);
                    });
                })
                ->orderBy('user_primer_nombre')
                ->get();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Usuarios encontrados',
                'datos' => $usuarios
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error obteniendo usuarios',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function registroAPI(Request $request)
    {

        $in = [
            'user_primer_nombre'     => $request->input('usu_primer_nombre', $request->input('user_primer_nombre')),
            'user_segundo_nombre'    => $request->input('usu_segundo_nombre', $request->input('user_segundo_nombre')),
            'user_primer_apellido'   => $request->input('usu_primer_apellido', $request->input('user_primer_apellido')),
            'user_segundo_apellido'  => $request->input('usu_segundo_apellido', $request->input('user_segundo_apellido')),
            'email'             => $request->input('usu_correo_electronico', $request->input('email')),
            'user_dpi_dni'           => $request->input('usu_dpi', $request->input('user_dpi_dni')),
            'user_rol'               => $request->input('usu_rol', $request->input('user_rol')),
            'password'          => $request->input('usu_password', $request->input('password')),
            'password2'         => $request->input('usu_password2', $request->input('password_confirmation')),
            'user_empresa'         => $request->input('user_empresa', $request->input('user_empresa')),
        ];

        $rules = [
            'user_primer_nombre'   => ['required', 'string', 'max:100'],
            'user_primer_apellido' => ['required', 'string', 'max:100'],
            'user_empresa' => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:100', 'unique:users,email'],
            'user_dpi_dni'         => ['nullable', 'string', 'max:20', 'unique:users,user_dpi_dni'],
            'password'        => ['required', 'string', 'min:8'],
        ];
        $messages = [
            'user_primer_nombre.required'   => 'El primer nombre es obligatorio',
            'user_primer_apellido.required' => 'El primer apellido es obligatorio',
            'user_empresa.required'           => 'El usuario debe de tener una tienda o empresa asociada',
            'email.required'           => 'El correo electrónico es obligatorio',
            'email.email'              => 'Correo inválido',
            'email.unique'             => 'El correo ya está en uso',
            'user_dpi_dni.unique'           => 'El DPI/DNI ya está en uso',
            'password.required'        => 'La contraseña es obligatoria',
            'password.min'             => 'La contraseña debe tener al menos 8 caracteres',
        ];
        $val = validator($in, $rules, $messages);

        if ($val->fails()) {
            return response()->json([
                'codigo'  => 2,
                'mensaje' => 'Errores de validación',
                'datos'   => $val->errors(),
            ], 422);
        }

        if ($in['password'] !== $in['password2']) {
            return response()->json([
                'codigo'  => 2,
                'mensaje' => 'Las contraseñas no coinciden',
            ], 400);
        }

        try {
            return DB::transaction(function () use ($in) {
                $token = Str::random(64);

                $user = new User();
                $user->user_primer_nombre      = $in['user_primer_nombre'];
                $user->user_segundo_nombre     = $in['user_segundo_nombre'] ?? null;
                $user->user_primer_apellido    = $in['user_primer_apellido'];
                $user->user_segundo_apellido   = $in['user_segundo_apellido'] ?? null;
                $user->email              = $in['email'];
                $user->password           = Hash::make($in['password']);
                $user->user_dpi_dni            = $in['user_dpi_dni'] ?? null;
                $user->user_rol                = $in['user_rol'] ?? null;
                $user->user_fecha_contrasena   = now();
                $user->user_token              = $token;
                $user->user_fecha_verificacion = null;
                $user->user_empresa = $in['user_empresa'] ?? null;
                $user->user_situacion          = 0; // 0=pendiente, 1=activo
                $user->save();


                $link = config('app.url') . '/api/usuarios/verificar?token=' . urlencode($token);
                Mail::to($user->email)->send(new VerificarCorreoMailable($user, $link));

                return response()->json([
                    'codigo'  => 1,
                    'mensaje' => 'Solicitud registrada. Revisa tu correo para verificar la cuenta.',
                    'datos'   => ['user_id' => $user->user_id],
                ], 200);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'Error generando solicitud de usuario',
                'detalle' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }


    public function verificarCorreoAPI(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return response()->json(['codigo' => 2, 'mensaje' => 'Token requerido'], 400);
        }

        $user = User::where('user_token', $token)->first();
        if (!$user) {
            return response()->json(['codigo' => 2, 'mensaje' => 'Token inválido o ya utilizado'], 404);
        }

        $user->user_fecha_verificacion = now();
        $user->user_situacion = 1;
        $user->user_token = null;
        $user->save();

        if (!$request->expectsJson()) {
            return redirect()->route('confirmemail.success');
        }
    }

    public function reenviarVerificacionAPI(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(['codigo' => 2, 'mensaje' => 'Correo requerido'], 400);
        }

        $user = User::where('email', $email)
            ->whereNull('user_fecha_verificacion')
            ->first();

        if (!$user) {
            return response()->json([
                'codigo' => 2,
                'mensaje' => 'No hay solicitudes pendientes para este correo',
            ], 404);
        }

        try {
            $token = Str::random(64);
            $user->user_token = $token;
            $user->save();
            $link = route('usuarios.verificar', ['token' => $token], true);

            DB::afterCommit(function () use ($user, $link) {
                Mail::to($user->email)->send(new VerificarCorreoMailable($user, $link));
            });

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Se envió un nuevo correo de verificación',
            ], 200);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['codigo' => 0, 'mensaje' => 'Error reenviando verificación'], 500);
        }
    }
}
