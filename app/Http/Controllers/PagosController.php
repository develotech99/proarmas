<?php

namespace App\Http\Controllers;

use App\Mail\NotificarpagoMail;
use App\Models\ProVenta;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;
use Mail;
use PhpParser\Node\Expr;

class PagosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pagos.mispagos');
    }

    public function index2()
    {
        return view('pagos.mispagos');
    }

    public function index3()
    {
        return view('pagos.administrar');
    }

    public function MisFacturasPendientes(Request $request)
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $cliente = DB::table('pro_clientes')
                ->where('cliente_user_id', $userId)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'codigo'  => 1,
                    'mensaje' => 'No hay cliente asociado a este usuario',
                    'data'    => [
                        'pendientes' => [],
                        'pagadas_ult4m' => [],
                        'facturas_pendientes_all' => [],
                        'all' => false
                    ]
                ]);
            }

            $verTodas = (bool) $request->boolean('all', false);
            $corte    = $verTodas ? Carbon::create(1900, 1, 1) : Carbon::now()->subMonths(4)->startOfDay();

            // ===== Concepto por venta =====
            $labelsAgg = DB::table('pro_detalle_ventas as d')
                ->join('pro_productos as p', 'p.producto_id', '=', 'd.det_producto_id')
                ->leftJoin('pro_marcas as ma',  'ma.marca_id',  '=', 'p.producto_marca_id')
                ->leftJoin('pro_modelo as mo',  'mo.modelo_id', '=', 'p.producto_modelo_id')
                ->leftJoin('pro_calibres as ca', 'ca.calibre_id', '=', 'p.producto_calibre_id')
                ->select([
                    'd.det_ven_id',
                    DB::raw("
                    TRIM(CONCAT_WS(' ',
                        ma.marca_descripcion,
                        mo.modelo_descripcion,
                        p.producto_nombre,
                        IF(ca.calibre_nombre IS NULL OR ca.calibre_nombre = '', '', CONCAT('(', ca.calibre_nombre, ')'))
                    )) as label
                "),
                    DB::raw('SUM(d.det_cantidad) as qty'),
                    DB::raw('MAX(d.det_id) as ord')
                ])
                ->groupBy('d.det_ven_id', 'label');

            $conceptoSub = DB::query()->fromSub($labelsAgg, 'x')
                ->select([
                    'x.det_ven_id',
                    DB::raw("GROUP_CONCAT(CONCAT(x.qty, ' ', x.label) ORDER BY x.ord SEPARATOR ', ') as concepto_resumen"),
                    DB::raw('COUNT(*) as items_count')
                ])
                ->groupBy('x.det_ven_id');

            // ===== Ventas activas =====
            $ventas = DB::table('pro_ventas as v')
                ->join('pro_pagos as pg', 'pg.pago_venta_id', '=', 'v.ven_id')
                ->leftJoinSub($conceptoSub, 'cx', fn($j) => $j->on('cx.det_ven_id', '=', 'v.ven_id'))
                ->where('v.ven_cliente', $cliente->cliente_id)
                ->where('v.ven_situacion', 'ACTIVA')
                ->select([
                    'v.ven_id',
                    'v.ven_fecha',
                    'v.ven_total_vendido',
                    'v.ven_descuento',
                    'v.ven_observaciones',
                    'pg.pago_id',
                    'pg.pago_tipo_pago',
                    'pg.pago_monto_total',
                    'pg.pago_monto_pagado',
                    'pg.pago_monto_pendiente',
                    'pg.pago_estado',
                    'pg.pago_cantidad_cuotas',
                    'pg.pago_abono_inicial',
                    'pg.pago_fecha_inicio',
                    'pg.pago_fecha_completado',
                    DB::raw('(pg.pago_monto_total - pg.pago_monto_pagado) as calculo_pendiente'),
                    DB::raw('COALESCE(cx.concepto_resumen, "—") as concepto'),
                    DB::raw('COALESCE(cx.items_count, 0) as items_count'),
                ])
                ->orderBy('v.ven_fecha', 'desc')
                ->get();

            if ($ventas->isEmpty()) {
                return response()->json([
                    'codigo'  => 1,
                    'mensaje' => 'Sin ventas activas',
                    'data'    => [
                        'pendientes' => [],
                        'pagadas_ult4m' => [],
                        'facturas_pendientes_all' => [],
                        'all' => $verTodas
                    ]
                ]);
            }

            $pagoIds  = $ventas->pluck('pago_id')->all();
            $ventaIds = $ventas->pluck('ven_id')->all();

            // ===== Cuotas pendientes o vencidas =====
            $cuotas = DB::table('pro_cuotas as ct')
                ->whereIn('ct.cuota_control_id', $pagoIds)
                ->whereIn('ct.cuota_estado', ['PENDIENTE', 'VENCIDA'])
                ->orderBy('ct.cuota_control_id')->orderBy('ct.cuota_numero')
                ->get()->groupBy('cuota_control_id');

            // ===== Pagos válidos (historial) =====
            $pagosValidos = DB::table('pro_detalle_pagos as dp')
                ->whereIn('dp.det_pago_pago_id', $pagoIds)
                ->where('dp.det_pago_estado', 'VALIDO')
                ->leftJoin('pro_metodos_pago as mp', 'mp.metpago_id', '=', 'dp.det_pago_metodo_pago')
                ->select([
                    'dp.det_pago_id',
                    'dp.det_pago_pago_id',
                    'dp.det_pago_fecha',
                    'dp.det_pago_monto',
                    'dp.det_pago_tipo_pago',
                    'dp.det_pago_numero_autorizacion',
                    'dp.det_pago_imagen_boucher',
                    'mp.metpago_descripcion as metodo'
                ])
                ->orderBy('dp.det_pago_fecha', 'asc')
                ->get()->groupBy('det_pago_pago_id');

            // ===== Cuotas EN REVISIÓN por venta (de la bandeja) =====
            $pendRows = DB::table('pro_pagos_subidos')
                ->whereIn('ps_venta_id', $ventaIds)
                ->where('ps_estado', 'PENDIENTE_VALIDACION')
                ->get(['ps_venta_id', 'ps_cuotas_json']);

            $cuotasEnRevisionPorVenta = [];
            foreach ($pendRows as $row) {
                $lista = json_decode($row->ps_cuotas_json, true) ?: [];
                $vid   = (int) $row->ps_venta_id;
                $cuotasEnRevisionPorVenta[$vid] = array_values(array_unique(array_merge($cuotasEnRevisionPorVenta[$vid] ?? [], array_map('intval', $lista))));
            }

            // ===== Clasificación =====
            $pendientes    = [];
            $pagadasUlt4m  = [];

            foreach ($ventas as $v) {
                $pendiente = isset($v->pago_monto_pendiente) && $v->pago_monto_pendiente !== null
                    ? (float)$v->pago_monto_pendiente
                    : max((float)$v->calculo_pendiente, 0.0);

                $hist = ($pagosValidos[$v->pago_id] ?? collect())->map(fn($p) => [
                    'id'            => $p->det_pago_id,
                    'fecha'         => $p->det_pago_fecha,
                    'monto'         => (float)$p->det_pago_monto,
                    'tipo'          => $p->det_pago_tipo_pago,
                    'metodo'        => $p->metodo ?? 'N/D',
                    'no_referencia' => $p->det_pago_numero_autorizacion,
                    'comprobante'   => $p->det_pago_imagen_boucher,
                ])->values();

                $ultimaFechaPago = ($pagosValidos[$v->pago_id] ?? collect())->max('det_pago_fecha');
                $fechaCompletado = $v->pago_fecha_completado ?? $ultimaFechaPago;

                // Cuotas en revisión para esta venta:
                $enRevIds = collect($cuotasEnRevisionPorVenta[$v->ven_id] ?? []);

                // Todas las cuotas pendientes + bandera en_revision
                $cuotasPend = ($cuotas[$v->pago_id] ?? collect())->map(function ($c) use ($enRevIds) {
                    $id = (int)$c->cuota_id;
                    return [
                        'cuota_id'     => $id,
                        'numero'       => (int)$c->cuota_numero,
                        'monto'        => (float)$c->cuota_monto,
                        'vence'        => $c->cuota_fecha_vencimiento,
                        'estado'       => $c->cuota_estado,
                        'en_revision'  => $enRevIds->contains($id),
                    ];
                })->values();

                $disponibles = $cuotasPend->filter(fn($q) => !$q['en_revision'])->count();

                $base = [
                    'venta_id'         => $v->ven_id,
                    'fecha'            => $v->ven_fecha,
                    'concepto'         => $v->concepto,
                    'items_count'      => (int)$v->items_count,
                    'monto_total'      => (float)$v->pago_monto_total ?: (float)$v->ven_total_vendido,
                    'pagado'           => (float)$v->pago_monto_pagado,
                    'pendiente'        => $pendiente,
                    'estado_pago'      => $v->pago_estado ?? ($pendiente > 0 ? 'PENDIENTE' : 'COMPLETADO'),
                    'observaciones'    => $v->ven_observaciones,

                    // lista explícita para el front:
                    'cuotas_en_revision'   => $enRevIds->values(),
                    'cuotas_disponibles'   => $disponibles,

                    'pago_master' => [
                        'pago_id'        => (int)$v->pago_id,
                        'tipo'           => $v->pago_tipo_pago,
                        'cuotas_totales' => (int)($v->pago_cantidad_cuotas ?? 0),
                        'abono_inicial'  => (float)($v->pago_abono_inicial ?? 0),
                        'inicio'         => $v->pago_fecha_inicio,
                        'fin'            => $v->pago_fecha_completado,
                    ],
                    'pagos_realizados' => $hist,
                ];

                if ($pendiente > 0) {
                    $pendientes[] = $base + [
                        'cuotas_pendientes'    => $cuotasPend,                      // todas, con flag en_revision
                        'puede_pagar_en_linea' => $disponibles > 0,                // solo si hay cuotas sin revisión
                    ];
                } else {
                    if ($fechaCompletado && Carbon::parse($fechaCompletado)->gte($corte)) {
                        $pagadasUlt4m[] = $base + [
                            'marcar_como'       => 'PAGADO',
                            'fecha_ultimo_pago' => $ultimaFechaPago ?: $v->pago_fecha_completado,
                        ];
                    }
                }
            }

            // “facturas pendientes” = ventas con saldo (plano)
            $facturasPendientesAll = collect($pendientes)->map(fn($r) => [
                'venta_id'  => $r['venta_id'],
                'fecha'     => $r['fecha'],
                'concepto'  => $r['concepto'],
                'total'     => $r['monto_total'],
                'pagado'    => $r['pagado'],
                'pendiente' => $r['pendiente'],
                'estado'    => $r['estado_pago'],
            ])->values();

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Datos devueltos correctamente',
                'data'    => [
                    'pendientes'               => array_values($pendientes),
                    'pagadas_ult4m'            => array_values($pagadasUlt4m),
                    'facturas_pendientes_all'  => $facturasPendientesAll,
                    'all'                      => $verTodas,
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'Error al obtener datos',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }



    public function pagarCuotas(Request $request)
    {
        try {
            $user = $request->user();

            $data = $request->validate([
                'venta_id'     => ['required', 'integer'],
                'cuotas'       => ['required', 'string'], // JSON: [10,11]
                'monto_total'  => ['required', 'numeric'], // suma de cuotas seleccionadas (front)
                'fecha'        => ['nullable', 'date_format:Y-m-d\TH:i'],
                'monto'        => ['required', 'numeric'], // monto del comprobante
                'referencia'   => ['required', 'string', 'min:6', 'max:64'],
                'concepto'     => ['nullable', 'string', 'max:255'],
                'banco_id'     => ['nullable', 'integer'],        // <- bigint en tu BD
                'banco_nombre' => ['nullable', 'string', 'max:64'],
                'comprobante'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);

            $ventaId   = (int)$data['venta_id'];
            $cuotasArr = json_decode($data['cuotas'], true) ?: [];

            // 1) Verificar que la venta exista (y opcionalmente que pertenezca al usuario)
            $venta = DB::table('pro_ventas as v')
                ->join('pro_pagos as pg', 'pg.pago_venta_id', '=', 'v.ven_id')
                ->where('v.ven_id', $ventaId)
                ->select('v.ven_id', 'pg.pago_id')
                ->first();

            if (!$venta) {
                return response()->json(['codigo' => 0, 'mensaje' => 'Venta no encontrada'], 404);
            }

            // 2) BLOQUEO: ¿ya hay un envío en PENDIENTE_VALIDACION para esta venta?
            $yaPendiente = DB::table('pro_pagos_subidos')
                ->where('ps_venta_id', $ventaId)
                ->where('ps_estado', 'PENDIENTE_VALIDACION')
                ->exists();

            if ($yaPendiente) {
                return response()->json([
                    'codigo'  => 0,
                    'mensaje' => 'Ya existe un pago en revisión para esta venta. Espera la validación antes de enviar otro.',
                ], 200);
            }

            // 3) Subir archivo (opcional)
            $path = null;
            if ($request->hasFile('comprobante')) {
                $path = $request->file('comprobante')->store('pagos_subidos', 'public'); // requiere storage:link
            }

            // 4) Insert en TU ESQUEMA
            $montoTotalCuotas = (float) $data['monto_total'];
            $montoComprobante = (float) $data['monto'];
            $diferencia       = $montoComprobante - $montoTotalCuotas;

            DB::beginTransaction();

            $insert = [
                'ps_venta_id'                => $ventaId,
                'ps_cliente_user_id'         => $user->id ?? $user->user_id ?? null,
                'ps_estado'                  => 'PENDIENTE_VALIDACION',
                'ps_canal'                   => 'WEB',

                'ps_fecha_comprobante'       => $data['fecha'] ? Carbon::parse($data['fecha']) : null,
                'ps_monto_comprobante'       => $montoComprobante,
                'ps_monto_total_cuotas_front' => $montoTotalCuotas,
                'ps_diferencia'              => $diferencia,

                'ps_banco_id'                => $data['banco_id'] ?? null,
                'ps_banco_nombre'            => $data['banco_nombre'] ?? null,

                'ps_referencia'              => $data['referencia'],
                'ps_concepto'                => $data['concepto'] ?? null,
                'ps_cuotas_json'             => json_encode(array_values($cuotasArr), JSON_UNESCAPED_UNICODE),

                'ps_imagen_path'             => $path,
                // opcionales:
                'ps_checksum'                => null, // si quieres, calcula hash del archivo/combinación
                'created_at'                 => now(),
                'updated_at'                 => now(),
            ];

            $psId = DB::table('pro_pagos_subidos')->insertGetId($insert);

            DB::commit();

            // 5) Notificación (no bloqueante)
            try {
                $payload = [
                    'venta_id'     => $ventaId,
                    'pago_id'      => $venta->pago_id,
                    'cuotas'       => $cuotasArr,
                    'monto_total'  => $montoTotalCuotas,
                    'fecha'        => $data['fecha'] ?? null,
                    'monto'        => $montoComprobante,
                    'referencia'   => $data['referencia'],
                    'concepto'     => $data['concepto'] ?? null,
                    'banco_id'     => $data['banco_id'] ?? null,
                    'banco_nombre' => $data['banco_nombre'] ?? null,
                    'cliente'      => [
                        'id'     => $user->id ?? $user->user_id ?? null,
                        'nombre' => $user->name ?? ($user->nombre ?? 'Cliente'),
                        'email'  => $user->email ?? 'sin-correo',
                    ],
                    'ps_id'            => $psId,
                    'comprobante_path' => $path,
                ];
                $destinatario = env('PAYMENTS_TO', env('MAIL_FROM_ADMIN') ?: config('mail.from.address'));
                if ($destinatario) {
                    Mail::to($destinatario)->send(new NotificarpagoMail($payload, $request->file('comprobante')));
                }
            } catch (\Throwable $me) {
                Log::warning('Fallo al enviar correo de pago pendiente', ['error' => $me->getMessage()]);
            }

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Pago enviado. Quedó en revisión (PENDIENTE_VALIDACION).',
                'ps_id'   => $psId,
                'path'    => $path,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('pagarCuotas error', ['msg' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()]);
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'No se pudo registrar el pago: ' . $e->getMessage(),
            ], 200);
        }
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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
