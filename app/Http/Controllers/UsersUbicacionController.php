<?php

namespace App\Http\Controllers;

use App\Models\UsersHistorialVisita;
use App\Models\UsersUbicacion;
use App\Models\UsersVisita;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class UsersUbicacionController extends Controller
{

    public function index()
    {
        //
    }

    public function create(Request $request)
    {

        $input = $request->all();

        if (isset(($input['lat']))) $input['lat'] = str_replace(',', '.', $input['lat']);
        if (isset(($input['lng']))) $input['lng'] = str_replace(',', '.', $input['lng']);
        if (isset(($input['cantidad_vendida']))) $input['cantidad_vendida'] = str_replace(',', '.', $input['cantidad_vendida']);

        $rules = [
            'cliente_id'        => ['required', 'integer', 'exists:users,user_id'],
            'lat'               => ['required', 'numeric', 'between:-90,90'],
            'lng'               => ['required', 'numeric', 'between:-180,180'],
            'direccion'         => ['nullable', 'string', 'max:255'],
            'visitado'          => ['required', 'in:1,2,3'],
            'fecha_visita'      => ['nullable', 'date'],
            'cantidad_vendida'  => ['nullable', 'numeric', 'min:0'],
            'descripcion_venta' => ['nullable', 'string'],
        ];

        $messages = [
            'cliente_id.required' => 'Seleccione un cliente.',
            'cliente_id.exists'   => 'El cliente no existe.',
            'lat.required'        => 'La latitud es requerida.',
            'lng.required'        => 'La longitud es requerida.',
            'visitado.in'         => 'Estado de visita inválido.',
            'fecha_visita.date'   => 'La fecha de visita no es válida.',
        ];

        $visitado = (string)($input['visitado'] ?? '');

        if ($visitado === '1') {
            $rules['fecha_visita'] = ['required', 'date'];
            $rules['cantidad_vendida'] = ['nullable'];
            $rules['descripcion_venta'] = ['nullable'];
        } elseif ($visitado === '2') {

            $rules['fecha_visita'] = ['required', 'date'];
            $rules['cantidad_vendida'] = ['required', 'numeric', 'min:0.01'];
            $rules['descripcion_venta'] = ['nullable', 'string'];
        } elseif ($visitado === '3') {
            $rules['fecha_visita'] = ['nullable'];
            $rules['cantidad_vendida'] = ['nullable'];
            $rules['descripcion_venta'] = ['nullable'];
        }

        $validaciones = validator::make($input, $rules, $messages);

        if ($validaciones->fails()) {
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'Errores de validación',
                'detalle' => $validaciones->errors(),
            ], 422);
        }

        $clienteId = (int)$input['cliente_id'];
        $lat = round((float)$input['lat'], 6);
        $lng = round((float)$input['lng'], 6);
        $direccion = $input['direccion'] ?? null;

        $estado      = (int)$input['visitado'];
        $fechaVisita = null;

        if (!empty($input['fecha_visita'])) {
            $fechaVisita = Carbon::parse($input['fecha_visita'])->format('Y-m-d H:i:s');
        }

        $venta = 0;
        $descVenta = null;

        if ($estado === 2) {
            $venta = (float)$input['cantidad_vendida'];
            $descVenta = $input['descripcion_venta'] ?? null;
        } elseif ($estado === 1) {
            $venta = 0.00;
            $descVenta = null;
        } else {
            $fechaVisita = null;
            $venta = 0.00;
            $descVenta = null;
        }

        try {

            $resultado = DB::transaction(function () use ($clienteId, $lat, $lng, $direccion, $estado, $fechaVisita, $venta, $descVenta) {

                $ubicacion = UsersUbicacion::create([
                    'ubi_user' => $clienteId,
                    'ubi_latitud' => $lat,
                    'ubi_longitud' => $lng,
                    'ubi_descripcion' => $direccion
                ]);

                $ubiId = $ubicacion->ubi_id;

                $visita = UsersVisita::create([
                    'visita_user' => $clienteId,
                    'visita_fecha' => $fechaVisita,
                    'visita_estado' => $estado,
                    'visita_venta' => $venta,
                    'visita_descripcion' => $descVenta
                ]);

                $visitaId = $visita->visita_id;

                $historial = UsersHistorialVisita::create([
                    'hist_visita_id' => $visitaId,
                    'hist_fecha_actualizacion' => now(),
                    'hist_estado_anterior' => null,
                    'hist_estado_nuevo' => $estado,
                    'hist_total_venta_anterior' => null,
                    'hist_total_venta_nuevo' => $venta,
                    'hist_descripcion' => $descVenta
                ]);

                $histId = $historial->hist_id;
            });

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Registro creado correctamente',
                'data' => $resultado
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al ingresar el Registro',
                'detalle' => $e->getMessage(),
            ], 404);
        }
    }

    public function getDatos(Request $request)
    {
        try {

            $incluirTodos = (string)$request->query('todos', '0') === '1';

            $Ubicaciones = DB::table('users_ubicaciones')
                ->selectRaw('MAX(ubi_id) AS ubi_id, ubi_user')
                ->groupBy('ubi_user');

            $UltimaVisita = DB::table('users_visitas')
                ->selectRaw('MAX(visita_id) AS visita_id, visita_user')
                ->groupBy('visita_user');

            $fullName = DB::raw("
            TRIM(CONCAT_WS(' ',
                u.user_primer_nombre,
                u.user_segundo_nombre,
                u.user_primer_apellido,
                u.user_segundo_apellido
            )) AS name
        ");


            $empresaNull = DB::raw('NULL AS user_empresa');

            $query = DB::table('users AS u')
                ->leftJoinSub($Ubicaciones, 'lu', function ($join) {
                    $join->on('lu.ubi_user', '=', 'u.user_id');
                })
                ->leftJoin('users_ubicaciones AS ub', 'ub.ubi_id', '=', 'lu.ubi_id')
                ->leftJoinSub($UltimaVisita, 'lv', function ($join) {
                    $join->on('lv.visita_user', '=', 'u.user_id');
                })
                ->leftJoin('users_visitas AS vv', 'vv.visita_id', '=', 'lv.visita_id')
                ->select([
                    'u.user_id',
                    $fullName,
                    $empresaNull,
                    'ub.ubi_id',
                    'ub.ubi_latitud',
                    'ub.ubi_longitud',
                    'ub.ubi_descripcion',
                    'ub.created_at AS ubi_created_at',
                    'vv.visita_id',
                    'vv.visita_estado',
                    'vv.visita_fecha',
                    'vv.visita_venta',
                    'vv.visita_descripcion',
                    'vv.visita_fecha AS visita_created_at',
                ])
                ->orderBy('name', 'asc');

            if (!$incluirTodos) {
                $query->whereNotNull('ub.ubi_id');
            }

            $rows = $query->get();

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'OK',
                'data'    => $rows,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'No se pudo obtener la lista',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }


    public function detalle($userId)
    {
        try {

            $name = DB::table('users as u')->selectRaw("
            TRIM(CONCAT_WS(' ',
                u.user_primer_nombre,
                u.user_segundo_nombre,
                u.user_primer_apellido,
                u.user_segundo_apellido
            )) as name
        ")->where('u.user_id', $userId)->value('name');


            $agg = DB::table('users_visitas')
                ->where('visita_user', $userId)
                ->selectRaw("
                COUNT(*) as total_visitas,
                SUM(CASE WHEN visita_estado=2 THEN 1 ELSE 0 END) as total_compras,
                SUM(CASE WHEN visita_estado=1 THEN 1 ELSE 0 END) as total_no_compra,
                SUM(CASE WHEN visita_estado=3 THEN 1 ELSE 0 END) as total_no_visitado,
                COALESCE(SUM(visita_venta),0) as total_venta,
                MAX(visita_fecha) as ult_visita_fecha
            ")
                ->first();

            $visitas = DB::table('users_visitas')
                ->where('visita_user', $userId)
                ->orderByDesc('visita_id')
                ->limit(50)
                ->get();

            $historial = DB::table('users_historial_visitas as h')
                ->join('users_visitas as v', 'h.hist_visita_id', '=', 'v.visita_id')
                ->where('v.visita_user', '=', $userId)  // ⚠️ Asegúrate que sea el user correcto
                ->orderByDesc('h.hist_fecha_actualizacion')
                ->select([
                    'h.hist_id',
                    'h.hist_visita_id',
                    'h.hist_fecha_actualizacion',
                    'h.hist_estado_anterior',
                    'h.hist_estado_nuevo',
                    'h.hist_total_venta_anterior',
                    'h.hist_total_venta_nuevo',
                    'h.hist_descripcion',
                    'v.visita_user'
                ])
                ->limit(100)
                ->get();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'OK',
                'data' => [
                    'user_id' => (int)$userId,
                    'name' => $name,
                    'total_visitas' => (int)($agg->total_visitas ?? 0),
                    'total_compras' => (int)($agg->total_compras ?? 0),
                    'total_no_compra' => (int)($agg->total_no_compra ?? 0),
                    'total_no_visitado' => (int)($agg->total_no_visitado ?? 0),
                    'total_venta' => (float)($agg->total_venta ?? 0),
                    'ult_visita_fecha' => $agg->ult_visita_fecha ?? null,
                    'visitas' => $visitas,
                    'historial' => $historial,
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'No se pudo cargar el detalle',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    // (opcional) borrar ubicación por ubi_id
    public function eliminarUbicacion($ubiId)
    {
        try {
            $deleted = DB::table('users_ubicaciones')->where('ubi_id', $ubiId)->delete();
            return response()->json([
                'codigo' => $deleted ? 1 : 0,
                'mensaje' => $deleted ? 'Ubicación eliminada' : 'No se encontró la ubicación',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'No se pudo eliminar',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request, $id = null)
    {
        $input = $request->all();

        if (!$id) {
            $id = $input['ubi_id'] ?? null;
        }


        if (!$id) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'ID de ubicación requerido',
            ], 400);
        }

        if (isset($input['lat'])) $input['lat'] = str_replace(',', '.', $input['lat']);
        if (isset($input['lng'])) $input['lng'] = str_replace(',', '.', $input['lng']);
        if (isset($input['cantidad_vendida'])) $input['cantidad_vendida'] = str_replace(',', '.', $input['cantidad_vendida']);

        $ubicacionExistente = UsersUbicacion::find($id);
        if (!$ubicacionExistente) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'La ubicación no existe',
            ], 404);
        }

        $rules = [
            'cliente_id'        => ['required', 'integer', 'exists:users,user_id'],
            'lat'               => ['required', 'numeric', 'between:-90,90'],
            'lng'               => ['required', 'numeric', 'between:-180,180'],
            'direccion'         => ['nullable', 'string', 'max:255'],
            'visitado'          => ['required', 'in:1,2,3'],
            'fecha_visita'      => ['nullable', 'date'],
            'cantidad_vendida'  => ['nullable', 'numeric', 'min:0'],
            'descripcion_venta' => ['nullable', 'string'],
        ];

        $messages = [
            'cliente_id.required' => 'Seleccione un cliente.',
            'cliente_id.exists'   => 'El cliente no existe.',
            'lat.required'        => 'La latitud es requerida.',
            'lng.required'        => 'La longitud es requerida.',
            'visitado.required'   => 'Seleccione el estado de la visita.',
            'visitado.in'         => 'Estado de visita inválido.',
            'fecha_visita.date'   => 'La fecha de visita no es válida.',
        ];

        $visitado = (string)($input['visitado'] ?? '');

        if ($visitado === '1') {
            $rules['fecha_visita'] = ['required', 'date'];
            $rules['cantidad_vendida'] = ['nullable'];
            $rules['descripcion_venta'] = ['nullable'];
        } elseif ($visitado === '2') {
            $rules['fecha_visita'] = ['required', 'date'];
            $rules['cantidad_vendida'] = ['required', 'numeric', 'min:0.01'];
            $rules['descripcion_venta'] = ['nullable', 'string'];
        } elseif ($visitado === '3') {
            $rules['fecha_visita'] = ['nullable'];
            $rules['cantidad_vendida'] = ['nullable'];
            $rules['descripcion_venta'] = ['nullable'];
        }

        $validaciones = validator::make($input, $rules, $messages);

        if ($validaciones->fails()) {
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'Errores de validación',
                'detalle' => $validaciones->errors(),
            ], 422);
        }

        $clienteId = (int)$input['cliente_id'];
        $lat = round((float)$input['lat'], 6);
        $lng = round((float)$input['lng'], 6);
        $direccion = $input['direccion'] ?? null;
        $estado = (int)$input['visitado'];
        $fechaVisita = null;

        if (!empty($input['fecha_visita'])) {
            $fechaVisita = Carbon::parse($input['fecha_visita'])->format('Y-m-d H:i:s');
        }

        $venta = 0;
        $descVenta = null;

        if ($estado === 2) {
            $venta = (float)$input['cantidad_vendida'];
            $descVenta = $input['descripcion_venta'] ?? null;
        } elseif ($estado === 1) {
            $venta = 0.00;
            $descVenta = null;
        } else {
            $fechaVisita = null;
            $venta = 0.00;
            $descVenta = null;
        }

        try {
            $resultado = DB::transaction(function () use ($id, $clienteId, $lat, $lng, $direccion, $estado, $fechaVisita, $venta, $descVenta, $ubicacionExistente) {

                $ubicacionExistente->update([
                    'ubi_user' => $clienteId,
                    'ubi_latitud' => $lat,
                    'ubi_longitud' => $lng,
                    'ubi_descripcion' => $direccion
                ]);

                $visitaExistente = UsersVisita::where('visita_user', $clienteId)
                    ->orderBy('visita_id', 'desc')
                    ->first();

                $estadoAnterior = null;
                $ventaAnterior = 0;
                $visitaId = null;

                if ($visitaExistente) {

                    $estadoAnterior = $visitaExistente->visita_estado;
                    $ventaAnterior = $visitaExistente->visita_venta;

                    $visitaExistente->update([
                        'visita_fecha' => $fechaVisita,
                        'visita_estado' => $estado,
                        'visita_venta' => $venta,
                        'visita_descripcion' => $descVenta
                    ]);

                    $visitaId = $visitaExistente->visita_id;
                } else {
                    // CREAR nueva visita si no existe ninguna para este cliente
                    $nuevaVisita = UsersVisita::create([
                        'visita_user' => $clienteId,
                        'visita_fecha' => $fechaVisita,
                        'visita_estado' => $estado,
                        'visita_venta' => $venta,
                        'visita_descripcion' => $descVenta
                    ]);

                    $visitaId = $nuevaVisita->visita_id;
                    $estadoAnterior = null;
                    $ventaAnterior = 0;
                }

                // 3. REGISTRAR EN EL HISTORIAL
                $historial = UsersHistorialVisita::create([
                    'hist_visita_id' => $visitaId,
                    'hist_fecha_actualizacion' => now(),
                    'hist_estado_anterior' => $estadoAnterior,
                    'hist_estado_nuevo' => $estado,
                    'hist_total_venta_anterior' => $ventaAnterior,
                    'hist_total_venta_nuevo' => $venta,
                    'hist_descripcion' => $this->generarDescripcionHistorial($estadoAnterior, $estado, $ventaAnterior, $venta, $descVenta)
                ]);

                return [
                    'ubicacion_id' => $ubicacionExistente->ubi_id,
                    'visita_id' => $visitaId,
                    'historial_id' => $historial->hist_id,
                    'cambios' => [
                        'estado' => ['anterior' => $estadoAnterior, 'nuevo' => $estado],
                        'venta' => ['anterior' => $ventaAnterior, 'nueva' => $venta]
                    ]
                ];
            });

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Ubicación y visita actualizadas correctamente. Historial registrado.',
                'data' => $resultado
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al actualizar el registro',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar descripción para el historial basada en los cambios
     */
    private function generarDescripcionHistorial($estadoAnterior, $estadoNuevo, $ventaAnterior, $ventaNueva, $descripcionVenta)
    {
        $estados = [
            1 => 'Visitado sin venta',
            2 => 'Visitado con venta',
            3 => 'No visitado'
        ];

        if ($estadoAnterior === null) {
            $descripcion = "Primera visita registrada: " . $estados[$estadoNuevo];
        } else {
            $descripcion = "Modificación: Estado cambió de '{$estados[$estadoAnterior]}' a '{$estados[$estadoNuevo]}'";
        }

        if ($ventaAnterior != $ventaNueva) {
            $descripcion .= " | Venta cambió de Q" . number_format($ventaAnterior, 2) . " a Q" . number_format($ventaNueva, 2);
        }

        if (!empty($descripcionVenta)) {
            $descripcion .= " | Nota: " . $descripcionVenta;
        }

        return $descripcion;
    }


    public function agregarVisita(Request $request)
    {
        $input = $request->all();

        $rules = [
            'user_id' => ['required', 'exists:users,user_id'],
            'estado' => ['required', 'in:1,2,3'],
            'fecha' => ['nullable', 'date'],
            'venta' => ['nullable', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string'],
        ];

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json(['codigo' => 0, 'mensaje' => 'Validación fallida', 'detalle' => $validator->errors()], 422);
        }

        $userId = (int)$input['user_id'];
        $estado = (int)$input['estado'];
        $fecha = !empty($input['fecha']) ? Carbon::parse($input['fecha'])->format('Y-m-d H:i:s') : null;
        $venta = $estado === 2 ? (float)($input['venta'] ?? 0) : 0;
        $descripcion = $input['descripcion'] ?? null;

        try {
            DB::transaction(function () use ($userId, $estado, $fecha, $venta, $descripcion) {
                // Obtener visita anterior
                $visitaAnterior = UsersVisita::where('visita_user', $userId)
                    ->orderBy('visita_id', 'desc')
                    ->first();

                $estadoAnt = $visitaAnterior->visita_estado ?? null;
                $ventaAnt = $visitaAnterior->visita_venta ?? 0;

                // Crear nueva visita
                $nuevaVisita = UsersVisita::create([
                    'visita_user' => $userId,
                    'visita_fecha' => $fecha,
                    'visita_estado' => $estado,
                    'visita_venta' => $venta,
                    'visita_descripcion' => $descripcion
                ]);

                // Registrar historial
                UsersHistorialVisita::create([
                    'hist_visita_id' => $nuevaVisita->visita_id,
                    'hist_fecha_actualizacion' => now(),
                    'hist_estado_anterior' => $estadoAnt,
                    'hist_estado_nuevo' => $estado,
                    'hist_total_venta_anterior' => $ventaAnt,
                    'hist_total_venta_nuevo' => $venta,
                    'hist_descripcion' => "Nueva visita registrada"
                ]);
            });

            return response()->json(['codigo' => 1, 'mensaje' => 'Visita registrada correctamente'], 200);
        } catch (\Throwable $e) {
            return response()->json(['codigo' => 0, 'mensaje' => 'Error', 'detalle' => $e->getMessage()], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
