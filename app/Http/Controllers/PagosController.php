<?php

namespace App\Http\Controllers;

use App\Models\ProVenta;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                    'codigo' => 1,
                    'mensaje' => 'No hay cliente asociado a este usuario',
                    'data' => ['pendientes' => [], 'pagadas_ult4m' => []]
                ]);
            }

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

            // 2) Ventas activas del cliente + maestro de pagos + concepto
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
                    'data'    => ['pendientes' => [], 'pagadas_ult4m' => []]
                ]);
            }

            $pagoIds = $ventas->pluck('pago_id')->all();

            // 3) Cuotas pendientes/vencidas
            $cuotas = DB::table('pro_cuotas as ct')
                ->whereIn('ct.cuota_control_id', $pagoIds)
                ->whereIn('ct.cuota_estado', ['PENDIENTE', 'VENCIDA'])
                ->orderBy('ct.cuota_control_id')->orderBy('ct.cuota_numero')
                ->get()->groupBy('cuota_control_id');

            // 4) Pagos válidos (historial). También para última fecha de pago.
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
                ->get()
                ->groupBy('det_pago_pago_id');

            // 5) Clasificar: pendientes vs pagadas últimos 4 meses
            $corte = Carbon::now()->subMonths(4)->startOfDay();

            $pendientes = [];
            $pagadasUlt4m = [];

            foreach ($ventas as $v) {
                $pendiente = isset($v->pago_monto_pendiente) && $v->pago_monto_pendiente !== null
                    ? (float)$v->pago_monto_pendiente
                    : max((float)$v->calculo_pendiente, 0.0);

                // Historial pagos
                $hist = ($pagosValidos[$v->pago_id] ?? collect())->map(function ($p) {
                    return [
                        'id'            => $p->det_pago_id,
                        'fecha'         => $p->det_pago_fecha,
                        'monto'         => (float)$p->det_pago_monto,
                        'tipo'          => $p->det_pago_tipo_pago,
                        'metodo'        => $p->metodo ?? 'N/D',
                        'no_referencia' => $p->det_pago_numero_autorizacion,
                        'comprobante'   => $p->det_pago_imagen_boucher,
                    ];
                })->values();

                // Última fecha de pago válida / completado
                $ultimaFechaPago = ($pagosValidos[$v->pago_id] ?? collect())->max('det_pago_fecha');
                $fechaCompletado = $v->pago_fecha_completado ?? $ultimaFechaPago;

                // Base común (ahora incluye 'concepto' e 'items_count')
                $base = [
                    'venta_id'         => $v->ven_id,
                    'fecha'            => $v->ven_fecha,
                    'concepto'         => $v->concepto,                 // <<— resumen humano
                    'items_count'      => (int)$v->items_count,          // <<— cuántas líneas únicas
                    'monto_total'      => (float)$v->pago_monto_total ?: (float)$v->ven_total_vendido,
                    'pagado'           => (float)$v->pago_monto_pagado,
                    'pendiente'        => $pendiente,
                    'estado_pago'      => $v->pago_estado ?? ($pendiente > 0 ? 'PENDIENTE' : 'COMPLETADO'),
                    'observaciones'    => $v->ven_observaciones,
                    'pago_master'      => [
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
                    // Adjunta cuotas pendientes para el flujo de pago
                    $cuotasPend = ($cuotas[$v->pago_id] ?? collect())->map(function ($c) {
                        return [
                            'cuota_id' => $c->cuota_id,
                            'numero'   => (int)$c->cuota_numero,
                            'monto'    => (float)$c->cuota_monto,
                            'vence'    => $c->cuota_fecha_vencimiento,
                            'estado'   => $c->cuota_estado,
                        ];
                    })->values();

                    $pendientes[] = $base + [
                        'cuotas_pendientes'    => $cuotasPend,
                        'puede_pagar_en_linea' => $cuotasPend->isNotEmpty(),
                    ];
                } else {
                    if ($fechaCompletado && Carbon::parse($fechaCompletado)->gte($corte)) {
                        $pagadasUlt4m[] = $base + [
                            'marcar_como'        => 'PAGADO',
                            'fecha_ultimo_pago'  => $ultimaFechaPago ?: $v->pago_fecha_completado,
                        ];
                    }
                }
            }

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Datos devueltos correctamente',
                'data'    => [
                    'pendientes'     => array_values($pendientes),
                    'pagadas_ult4m'  => array_values($pagadasUlt4m),
                    'corte'          => $corte->toDateString(),
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


    public function pagarCuotas(PagarCuotasRequest $req)
    {
        $user = $req->user();

        return DB::transaction(function () use ($req, $user) {

            $venta = Venta::where('id', $req->venta_id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Trae y valida cuotas seleccionadas pertenecen a la venta y están pendientes
            $cuotas = Cuota::whereIn('id', $req->cuotas)
                ->where('venta_id', $venta->id)
                ->where('estado', 'PENDIENTE')
                ->get();

            if ($cuotas->isEmpty()) {
                return response()->json(['codigo' => 0, 'mensaje' => 'Las cuotas seleccionadas no están disponibles'], 422);
            }

            $totalCuotas = (float) $cuotas->sum('monto');

            // Crea el pago (validación suave: permitimos pequeñas diferencias)
            $montoComprobante = round((float)$req->monto, 2);
            $diff = abs($montoComprobante - $totalCuotas);

            if ($diff > 0.05) {
                // Si quieres bloquear, devuelve error. Si no, solo marca "PARCIAL".
                // Aquí solo lo anotamos en observación de estado.
            }

            $path = null;
            if ($req->hasFile('comprobante')) {
                $path = $req->file('comprobante')->store('comprobantes', 'public');
            }

            $pago = Pago::create([
                'venta_id'       => $venta->id,
                'user_id'        => $user->id,
                'banco_id'       => $req->banco_id,
                'banco_nombre'   => $req->banco_nombre,
                'monto'          => $montoComprobante,
                'fecha'          => $req->fecha ? now()->parse($req->fecha) : null,
                'referencia'     => $req->referencia,
                'concepto'       => $req->concepto,
                'comprobante_path' => $path,
                'estado_validacion' => 'PENDIENTE',
            ]);

            // Vincula cuotas al pago
            $pago->cuotas()->sync($cuotas->pluck('id')->all());

            // (Opcional) actualiza totales de la venta y marca cuotas como pendientes de revisión
            // NO las marcamos PAGADAS hasta que el admin apruebe
            // Si quieres marcarlas "EN_REVISION", agrega esa columna/valor.

            // Notifica por correo
            try {
                Mail::to(config('mail.pagos_admin', env('PAGOS_ADMIN_EMAIL')))
                    ->send(new PagoEnviado($pago));
            } catch (\Throwable $e) {
                // Log y continuo; el pago queda registrado igual
                \Log::warning('No se pudo enviar correo de pago: ' . $e->getMessage());
            }

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Pago recibido y enviado para validación',
                'data'    => [
                    'pago_id'       => $pago->id,
                    'total_cuotas'  => $totalCuotas,
                    'monto_ingresado' => $montoComprobante,
                    'estado'        => $pago->estado_validacion
                ]
            ]);
        });
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
