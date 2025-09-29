<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CajaSaldo;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminPagosController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $saldos = DB::table('caja_saldos as s')
                ->join('pro_metodos_pago as m', 'm.metpago_id', '=', 's.caja_saldo_metodo_pago')
                ->select(
                    's.caja_saldo_metodo_pago as metodo_id',
                    'm.metpago_descripcion as metodo',
                    's.caja_saldo_moneda',
                    's.caja_saldo_monto_actual',
                    's.caja_saldo_actualizado'
                )
                ->orderBy('m.metpago_descripcion')
                ->get();

            $totalGTQ   = (float) $saldos->where('caja_saldo_moneda', 'GTQ')->sum('caja_saldo_monto_actual');
            $pendientes = DB::table('pro_pagos_subidos')
                ->whereIn('ps_estado', ['PENDIENTE', 'PENDIENTE_VALIDACION'])
                ->count();
            $ultimaCarga = DB::table('pro_estados_cuenta')->max('created_at');

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas exitosamente',
                'data' => [
                    'saldo_total_gtq' => $totalGTQ,
                    'saldos'          => $saldos,
                    'pendientes'      => $pendientes,
                    'ultima_carga'    => $ultimaCarga,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las estadísticas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Bandeja de validación
     * GET /admin/pagos/pendientes
     * =========================== */
    public function pendientes(Request $request)
    {
        try {
            $q      = trim((string) $request->query('q', ''));
            $estado = (string) $request->query('estado', '');

            $rows = DB::table('pro_pagos_subidos as ps')
                ->join('pro_ventas as v', 'v.ven_id', '=', 'ps.ps_venta_id')
                ->join('pro_pagos as pg', 'pg.pago_venta_id', '=', 'v.ven_id')
                // tu esquema usa users.user_id en varios FKs
                ->leftJoin('users as u', 'u.user_id', '=', 'ps.ps_cliente_user_id')
                ->leftJoin('pro_clientes as c', 'c.cliente_user_id', '=', 'ps.ps_cliente_user_id')
                ->select([
                    'ps.ps_id',
                    'ps.ps_venta_id',
                    'ps.ps_estado',
                    'ps.ps_referencia',
                    'ps.ps_concepto',
                    'ps.ps_imagen_path',
                    'ps.ps_monto_comprobante',
                    'ps.ps_monto_total_cuotas_front',
                    'ps.ps_cuotas_json',
                    'ps.created_at',

                    'v.ven_id',
                    'v.ven_fecha',
                    'v.ven_total_vendido',
                    'v.ven_observaciones',

                    'pg.pago_id',
                    'pg.pago_monto_total',
                    'pg.pago_monto_pagado',
                    'pg.pago_monto_pendiente',
                    'pg.pago_estado',

                    DB::raw("
                    COALESCE(
                        NULLIF(
                            TRIM(CONCAT_WS(' ',
                                c.cliente_nombre1,
                                c.cliente_nombre2,
                                c.cliente_apellido1,
                                c.cliente_apellido2
                            )),
                            ''
                        ),
                        u.email,
                        CONCAT('Usuario ', ps.ps_cliente_user_id),
                        'Cliente'
                    ) as cliente
                "),
                ])
                ->when($estado !== '', function ($qq) use ($estado) {
                    if ($estado === 'PENDIENTE') {
                        $qq->whereIn('ps.ps_estado', ['PENDIENTE', 'PENDIENTE_VALIDACION']);
                    } else {
                        $qq->where('ps.ps_estado', $estado);
                    }
                })
                ->when($estado === '', fn($qq) => $qq->whereIn('ps.ps_estado', ['PENDIENTE', 'PENDIENTE_VALIDACION']))
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('ps.ps_referencia', 'like', "%{$q}%")
                            ->orWhere('ps.ps_concepto', 'like', "%{$q}%")
                            ->orWhere('v.ven_observaciones', 'like', "%{$q}%")
                            ->orWhere('v.ven_id', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('ps.created_at')
                ->limit(300)
                ->get();

            // Resumen de items por venta (marca/modelo/producto ...)
            $labelsAgg = DB::table('pro_detalle_ventas as d')
                ->join('pro_productos as p', 'p.producto_id', '=', 'd.det_producto_id')
                ->leftJoin('pro_marcas as ma', 'ma.marca_id', '=', 'p.producto_marca_id')
                ->leftJoin('pro_modelo as mo', 'mo.modelo_id', '=', 'p.producto_modelo_id')
                ->leftJoin('pro_calibres as ca', 'ca.calibre_id', '=', 'p.producto_calibre_id')
                ->whereIn('d.det_ven_id', $rows->pluck('ven_id')->all())
                ->select([
                    'd.det_ven_id',
                    DB::raw("TRIM(CONCAT_WS(' ', ma.marca_descripcion, mo.modelo_descripcion, p.producto_nombre, IFNULL(CONCAT('(', ca.calibre_nombre, ')'), ''))) as label"),
                    DB::raw('SUM(d.det_cantidad) as qty'),
                    DB::raw('MAX(d.det_id) as ord'),
                ])
                ->groupBy('d.det_ven_id', 'label');

            $conceptoSub = DB::query()->fromSub($labelsAgg, 'x')
                ->select([
                    'x.det_ven_id',
                    DB::raw("GROUP_CONCAT(CONCAT(x.qty,' ',x.label) ORDER BY x.ord SEPARATOR ', ') as concepto_resumen"),
                    DB::raw('COUNT(*) as items_count'),
                ])
                ->groupBy('x.det_ven_id')
                ->get()
                ->keyBy('det_ven_id');

            // (Opcional) Agregados de cuotas por venta para “pago n de X”
            $cuotasAgg = DB::table('pro_cuotas')
                ->whereIn('cuota_control_id', $rows->pluck('pago_id')->all())
                ->select([
                    'cuota_control_id',
                    DB::raw('COUNT(*) as cuotas_total'),
                    DB::raw("SUM(CASE WHEN cuota_estado='PENDIENTE' THEN 1 ELSE 0 END) as cuotas_pendientes"),
                    DB::raw('SUM(cuota_monto) as monto_cuotas_total'),
                    DB::raw("SUM(CASE WHEN cuota_estado='PENDIENTE' THEN cuota_monto ELSE 0 END) as monto_cuotas_pendiente"),
                ])
                ->groupBy('cuota_control_id')
                ->get()
                ->keyBy('cuota_control_id');

            $data = $rows->map(function ($r) use ($conceptoSub, $cuotasAgg) {
                $c = $conceptoSub[$r->ven_id] ?? null;

                // Debía para ESTE envío (lo que el cliente seleccionó)
                $debiaEnvio = (float) ($r->ps_monto_total_cuotas_front ?? 0);

                // Pendiente global de la venta (contexto)
                $pendienteVenta = (float) ($r->pago_monto_pendiente
                    ?? max(($r->pago_monto_total ?? 0) - ($r->pago_monto_pagado ?? 0), 0));

                // Qué mostrar en la columna "Debía" de la bandeja:
                $debiaMostrado = $debiaEnvio > 0 ? $debiaEnvio : $pendienteVenta;

                $depositado = (float) ($r->ps_monto_comprobante ?? 0);
                $dif        = $depositado - $debiaMostrado;

                $imagenUrl  = $r->ps_imagen_path
                    ? Storage::disk('public')->url($r->ps_imagen_path)
                    : null;

                // Cuotas seleccionadas en este envío (desde JSON guardado)
                $cuotasSel = 0;
                if (!empty($r->ps_cuotas_json)) {
                    $arr = json_decode($r->ps_cuotas_json, true);
                    $cuotasSel = is_array($arr) ? count($arr) : 0;
                }

                // Agregados de cuotas de la venta (si tienes tabla de cuotas)
                $cuAgg = $cuotasAgg[$r->pago_id] ?? null;

                return [
                    'ps_id'           => (int) $r->ps_id,
                    'venta_id'        => (int) $r->ven_id,
                    'fecha'           => $r->ven_fecha,
                    'cliente'         => $r->cliente,

                    'concepto'        => $c->concepto_resumen ?? '—',
                    'items_count'     => (int) ($c->items_count ?? 0),

                    // Lo que verás en la tabla:
                    'debia'           => round($debiaMostrado, 2),
                    'depositado'      => round($depositado, 2),
                    'diferencia'      => round($dif, 2),

                    // Contexto adicional (por si quieres mostrarlo en tooltip o columnas nuevas)
                    'debia_envio'         => round($debiaEnvio, 2),
                    'pendiente_venta'     => round($pendienteVenta, 2),
                    'venta_total'         => round((float) ($r->ven_total_vendido ?? 0), 2),

                    'estado'          => $r->ps_estado,
                    'referencia'      => $r->ps_referencia,
                    'imagen'          => $imagenUrl,

                    // Cuotas
                    'cuotas_seleccionadas'   => $cuotasSel,
                    'cuotas_total_venta'     => $cuAgg->cuotas_total ?? null,
                    'cuotas_pendientes'      => $cuAgg->cuotas_pendientes ?? null,
                    'monto_cuotas_pendiente' => isset($cuAgg) ? round((float) $cuAgg->monto_cuotas_pendiente, 2) : null,

                    'observaciones_venta' => $r->ven_observaciones,
                    'created_at'       => $r->created_at,
                ];
            })->values();

            return response()->json([
                'codigo'  => 1,
                'mensaje' => 'Pendientes obtenidos exitosamente',
                'data'    => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'codigo'  => 0,
                'mensaje' => 'Error al obtener los pendientes',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Aprobar pago
     * POST /admin/pagos/aprobar
     * =========================== */
    public function aprobar(Request $request)
    {
        try {
            $data = $request->validate([
                'ps_id'        => ['required', 'integer', 'min:1'],
                'observaciones' => ['nullable', 'string', 'max:255'],
                'metodo_id'    => ['nullable', 'integer', 'min:1'],
            ]);

            $metodoEfectivoId = (int) ($data['metodo_id'] ?? 1);

            $ps = DB::table('pro_pagos_subidos')->where('ps_id', $data['ps_id'])->first();
            if (!$ps) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Registro no encontrado'
                ], 404);
            }

            if (!in_array($ps->ps_estado, ['PENDIENTE', 'PENDIENTE_VALIDACION'])) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'El registro no está pendiente'
                ], 422);
            }

            $venta = DB::table('pro_ventas as v')
                ->join('pro_pagos as pg', 'pg.pago_venta_id', '=', 'v.ven_id')
                ->select(
                    'v.ven_id',
                    'v.ven_cliente',
                    'pg.pago_id',
                    'pg.pago_estado',
                    'pg.pago_monto_total',
                    'pg.pago_monto_pagado',
                    'pg.pago_monto_pendiente'
                )
                ->where('v.ven_id', $ps->ps_venta_id)
                ->first();

            if (!$venta) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Venta asociada no encontrada'
                ], 404);
            }

            $monto = (float) $ps->ps_monto_comprobante;
            $fecha = $ps->ps_fecha_comprobante ?: now();

            DB::beginTransaction();

            // 1) Detalle de pago
            $detId = DB::table('pro_detalle_pagos')->insertGetId([
                'det_pago_pago_id'             => $venta->pago_id,
                'det_pago_cuota_id'            => null,
                'det_pago_fecha'               => $fecha,
                'det_pago_monto'               => $monto,
                'det_pago_metodo_pago'         => $metodoEfectivoId,
                'det_pago_banco_id'            => $ps->ps_banco_id ?? null,
                'det_pago_numero_autorizacion' => $ps->ps_referencia ?? null,
                'det_pago_imagen_boucher'      => $ps->ps_imagen_path ?? null,
                'det_pago_tipo_pago'           => 'PAGO_UNICO',
                'det_pago_estado'              => 'VALIDO',
                'det_pago_observaciones'       => $data['observaciones'] ?? $ps->ps_concepto,
                'det_pago_usuario_registro'    => auth()->id(),
                'created_at'                   => now(),
                'updated_at'                   => now(),
            ]);

            // 2) Master de pagos
            $nuevoPagado    = (float) $venta->pago_monto_pagado + $monto;
            $nuevoPendiente = max((float) $venta->pago_monto_total - $nuevoPagado, 0);
            $nuevoEstado    = $nuevoPendiente <= 0 ? 'COMPLETADO' : 'PARCIAL';

            DB::table('pro_pagos')
                ->where('pago_id', $venta->pago_id)
                ->update([
                    'pago_monto_pagado'     => $nuevoPagado,
                    'pago_monto_pendiente'  => $nuevoPendiente,
                    'pago_estado'           => $nuevoEstado,
                    'pago_fecha_completado' => $nuevoPendiente <= 0 ? now() : null,
                    'updated_at'            => now(),
                ]);

            // 3) Caja (historial)
            DB::table('cja_historial')->insert([
                'cja_tipo' => 'VENTA',
                'cja_id_venta'      => $venta->ven_id,
                'cja_usuario'       => auth()->id(),
                'cja_monto'         => $monto,
                'cja_fecha'         => now(),
                'cja_metodo_pago'   => $metodoEfectivoId,
                'cja_no_referencia' => $ps->ps_referencia ?? null,
                'cja_situacion'     => 'ACTIVO',
                'cja_observaciones' => 'Aprobación ps#' . $ps->ps_id,
                'created_at'        => now(),
            ]);

            // 4) Saldos
            CajaSaldo::ensureRow($metodoEfectivoId, 'GTQ')->addAmount($monto);

            // 5) Cambiar estado del PS
            DB::table('pro_pagos_subidos')
                ->where('ps_id', $ps->ps_id)
                ->update([
                    'ps_estado'    => 'APROBADO',
                    'ps_notas_revision' => $data['observaciones'] ?? null,
                    'ps_revisado_por' => auth()->id(),
                    'ps_revisado_en' => now(),
                    'updated_at'   => now(),
                ]);


            if (!empty($ps->ps_cuotas_aprobadas_json)) {
                $cuotasIds = json_decode($ps->ps_cuotas_aprobadas_json, true);
                if (is_array($cuotasIds) && count($cuotasIds) > 0) {
                    DB::table('pro_cuotas')
                        ->whereIn('cuota_id', $cuotasIds)
                        ->update([
                            'cuota_estado' => 'PAGADA',
                            'cuota_fecha_pago' => now(),
                            'updated_at' => now()
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Pago aprobado exitosamente',
                'data' => [
                    'det_pago_id' => $detId
                ]
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Datos de validación inválidos',
                'detalle' => $ve->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al aprobar el pago',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Rechazar pago
     * POST /admin/pagos/rechazar
     * =========================== */
    public function rechazar(Request $request)
    {
        try {
            $data = $request->validate([
                'ps_id'  => ['required', 'integer', 'min:1'],
                'motivo' => ['required', 'string', 'min:5', 'max:255'],
            ]);

            $ps = DB::table('pro_pagos_subidos')->where('ps_id', $data['ps_id'])->first();
            if (!$ps) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Registro no encontrado'
                ], 404);
            }

            if (!in_array($ps->ps_estado, ['PENDIENTE', 'PENDIENTE_VALIDACION'])) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'El registro no está pendiente'
                ], 422);
            }

            DB::table('pro_pagos_subidos')
                ->where('ps_id', $data['ps_id'])
                ->update([
                    'ps_estado'    => 'RECHAZADO',
                    'ps_notas_revision' => $data['motivo'],
                    'ps_revisado_por' => auth()->id(),
                    'ps_revisado_en' => now(),
                    'updated_at'   => now(),
                ]);

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Pago rechazado exitosamente'
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Datos de validación inválidos',
                'detalle' => $ve->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al rechazar el pago',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Movimientos de caja
     * GET /admin/pagos/movimientos
     * =========================== */
    public function movimientos(Request $request)
    {
        try {
            $from = $request->query('from') ?: Carbon::now()->startOfMonth()->toDateString();
            $to   = $request->query('to')   ?: Carbon::now()->endOfMonth()->toDateString();
            $metodoId = $request->query('metodo_id');

            $q = DB::table('cja_historial as h')
                ->leftJoin('pro_metodos_pago as m', 'm.metpago_id', '=', 'h.cja_metodo_pago')
                ->select(
                    'h.cja_id',
                    'h.cja_fecha',
                    'h.cja_tipo',
                    'h.cja_no_referencia',
                    'm.metpago_descripcion as metodo',
                    'h.cja_monto',
                    'h.cja_situacion'
                )
                ->whereDate('h.cja_fecha', '>=', $from)
                ->whereDate('h.cja_fecha', '<=', $to)
                ->when($metodoId, fn($qq) => $qq->where('h.cja_metodo_pago', $metodoId))
                ->orderBy('h.cja_fecha', 'desc');

            $rows = $q->get();

            $total = 0.0;
            foreach ($rows as $r) {
                if ($r->cja_situacion === 'ANULADO') continue;
                $total += in_array($r->cja_tipo, ['VENTA', 'DEPOSITO', 'AJUSTE_POS'])
                    ? (float) $r->cja_monto
                    : -(float) $r->cja_monto;
            }

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Movimientos obtenidos exitosamente',
                'data' => [
                    'movimientos' => $rows,
                    'total' => round($total, 2),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los movimientos',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Registrar egreso de caja
     * POST /admin/pagos/egresos
     * =========================== */
    public function registrarEgreso(Request $request)
    {
        try {
            $data = $request->validate([
                'fecha'      => ['nullable', 'date'],
                'metodo_id'  => ['required', 'integer', 'min:1'],
                'monto'      => ['required', 'numeric', 'gt:0'],
                'motivo'     => ['required', 'string', 'max:200'],
                'referencia' => ['nullable', 'string', 'max:100'],
                'archivo'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);

            $path = null;

            if ($request->hasFile('archivo')) {
                $path = $request->file('archivo')->store('egresos', 'public');
            }

            DB::beginTransaction();

            DB::table('cja_historial')->insert([
                'cja_tipo'          => 'EGRESO',
                'cja_id_venta'      => null,
                'cja_id_import'     => null,
                'cja_usuario'       => auth()->id(),
                'cja_monto'         => $data['monto'],
                'cja_fecha'         => $data['fecha'] ? Carbon::parse($data['fecha']) : now(),
                'cja_metodo_pago'   => $data['metodo_id'],
                'cja_no_referencia' => $data['referencia'] ?? null,
                'cja_situacion'     => 'ACTIVO',
                'cja_observaciones' => $data['motivo'],
                'created_at'        => now(),
            ]);

            CajaSaldo::ensureRow($data['metodo_id'], 'GTQ')->subtractAmount($data['monto']);

            DB::commit();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Egreso registrado exitosamente',
                'data' => [
                    'archivo' => $path
                ]
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Datos de validación inválidos',
                'detalle' => $ve->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($path) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (\Exception $__) {
                }
            }
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al registrar el egreso',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Upload/preview estado de cuenta
     * POST /admin/pagos/movs/upload
     * =========================== */
    public function estadoCuentaPreview(Request $request)
    {
        try {
            $request->validate([
                'archivo'  => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
                'banco_id' => ['nullable', 'integer'],
            ]);

            $file = $request->file('archivo');
            $path = $file->store('estados_cuenta/tmp', 'public');

            [$headers, $rows] = $this->parseSheet(storage_path('app/public/' . $path));

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Vista previa generada exitosamente',
                'data' => [
                    'path'    => $path,
                    'headers' => $headers,
                    'rows'    => array_slice($rows, 0, 50),
                ]
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Datos de validación inválidos',
                'detalle' => $ve->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al generar la vista previa',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Procesar estado de cuenta (guardar control)
     * POST /admin/pagos/movs/procesar
     * =========================== */
    public function estadoCuentaProcesar(Request $request)
    {
        try {
            $data = $request->validate([
                'archivo_path' => ['required', 'string'],
                'banco_id'     => ['nullable', 'integer'],
                'fecha_inicio' => ['nullable', 'date'],
                'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            ]);

            $full = storage_path('app/public/' . $data['archivo_path']);
            if (!file_exists($full)) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Archivo no encontrado'
                ], 404);
            }

            [$headers, $rows] = $this->parseSheet($full);

            $ecId = DB::table('pro_estados_cuenta')->insertGetId([
                'ec_banco_id'  => $data['banco_id'] ?? null,
                'ec_archivo'   => $data['archivo_path'],
                'ec_headers'   => json_encode($headers, JSON_UNESCAPED_UNICODE),
                'ec_rows'      => json_encode($rows, JSON_UNESCAPED_UNICODE),
                'ec_fecha_ini' => $data['fecha_inicio'] ?? null,
                'ec_fecha_fin' => $data['fecha_fin'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Estado de cuenta procesado exitosamente',
                'data' => [
                    'ec_id' => $ecId,
                    'rows_count' => count($rows)
                ]
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Datos de validación inválidos',
                'detalle' => $ve->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error al procesar el estado de cuenta',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /* ===========================
     * Utilidades privadas
     * =========================== */

    /**
     * Lee CSV/XLSX y devuelve [headers, rows normalizados].
     * Estandariza: fecha, descripcion, referencia, monto
     */
    private function parseSheet(string $fullPath): array
    {
        setlocale(LC_ALL, 'es_ES.UTF-8', 'es_GT.UTF-8', 'Spanish_Guatemala.1252');
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // CSV/TXT
        if (in_array($ext, ['csv', 'txt'])) {
            $fh = fopen($fullPath, 'r');
            if ($fh === false) {
                throw new \RuntimeException('No se pudo abrir el archivo CSV/TXT.');
            }

            stream_filter_append($fh, 'convert.iconv.ISO-8859-1/UTF-8');
            $headers = fgetcsv($fh) ?: [];
            $rows = [];
            while (($r = fgetcsv($fh)) !== false) {
                $rows[] = $this->normalizeRow($headers, $r);
            }
            fclose($fh);
            return [$headers, $rows];
        }

        // Excel
        $reader = IOFactory::createReaderForFile($fullPath);
        $spread = $reader->load($fullPath);
        $sheet  = $spread->getSheet(0);
        $rowsRaw = $sheet->toArray(null, true, true, true);

        $first   = array_shift($rowsRaw) ?: [];
        $headers = array_values(array_map(fn($v) => is_null($v) ? '' : trim((string) $v), $first));

        $rows = [];
        foreach ($rowsRaw as $row) {
            $vals = array_values($row);
            $rows[] = $this->normalizeRow($headers, $vals);
        }
        return [$headers, $rows];
    }

    public function conciliarAutomatico(Request $request)
    {
        try {
            $ecId = $request->validate(['ec_id' => 'required|integer'])['ec_id'];

            $ec = DB::table('pro_estados_cuenta')->where('ec_id', $ecId)->first();
            if (!$ec) {
                return response()->json(['codigo' => 0, 'mensaje' => 'Estado de cuenta no encontrado'], 404);
            }

            $rows = json_decode($ec->ec_rows, true);

            $pendientes = DB::table('pro_pagos_subidos')
                ->whereIn('ps_estado', ['PENDIENTE', 'PENDIENTE_VALIDACION'])
                ->get();

            $matches = [];
            $noMatch = [];

            foreach ($pendientes as $ps) {
                foreach ($pendientes as $ps) {
                    $encontrado = false;
                    foreach ($rows as $row) {
                        \Log::info('Comparando', [
                            'ps_referencia' => $ps->ps_referencia,
                            'banco_ref' => $row['referencia'],
                            'ps_monto' => $ps->ps_monto_comprobante,
                            'banco_monto' => $row['monto'],
                        ]);

                        $refMatch = !empty($ps->ps_referencia)
                            && !empty($row['referencia'])
                            && stripos($row['referencia'], $ps->ps_referencia) !== false;

                        $montoMatch = abs($row['monto'] - $ps->ps_monto_comprobante) <= 1.00;

                        if ($refMatch && $montoMatch) {
                            \Log::info('MATCH ENCONTRADO!');
                            $matches[] = [
                                'ps_id' => $ps->ps_id,
                                'venta_id' => $ps->ps_venta_id,
                                'banco_monto' => $row['monto'],
                                'banco_fecha' => $row['fecha'],
                                'banco_ref' => $row['referencia'],
                                'confianza' => 'ALTA'
                            ];
                            $encontrado = true;
                            break;
                        }
                    }
                }

                if (!$encontrado) {
                    $noMatch[] = [
                        'ps_id' => $ps->ps_id,
                        'venta_id' => $ps->ps_venta_id,
                        'ps_referencia' => $ps->ps_referencia,
                        'ps_monto' => $ps->ps_monto_comprobante
                    ];
                }
            }

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Conciliación completada',
                'data' => [
                    'matches' => $matches,
                    'no_match' => $noMatch,
                    'total_procesados' => count($pendientes)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error en conciliación',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    private function normalizeRow(array $headers, array $values): array
    {
        $map = [];
        foreach ($headers as $i => $h) {
            $key = strtolower(trim((string) $h));
            // Eliminar caracteres especiales para mejor matching
            $key = preg_replace('/[^a-z0-9]/', '', $key);
            $map[$key] = $i;
        }

        $get = function (array $names, $default = null) use ($map, $values) {
            foreach ($names as $name) {
                // Limpiar el nombre también
                $cleanName = preg_replace('/[^a-z0-9]/', '', strtolower($name));
                if (isset($map[$cleanName])) {
                    return $values[$map[$cleanName]] ?? $default;
                }
            }

            // Fallback: buscar si contiene
            foreach ($map as $k => $idx) {
                foreach ($names as $name) {
                    $cleanName = preg_replace('/[^a-z0-9]/', '', strtolower($name));
                    if (str_contains($k, $cleanName)) {
                        return $values[$idx] ?? $default;
                    }
                }
            }
            return $default;
        };

        $rawFecha = $get(['fecha', 'date'], null);
        $fecha = null;
        if ($rawFecha) {
            try {
                $fecha = Carbon::parse($rawFecha)->format('Y-m-d');
            } catch (\Throwable $e) {
                $fecha = null;
            }
        }

        $desc = (string) ($get(['descripcion', 'description', 'detalle', 'concepto'], '') ?? '');
        $ref = (string) ($get(['referencia', 'ref', 'autorizacion', 'aut', 'secuencial'], '') ?? '');

        $montoRaw = $get(['credito', 'credit', 'abono'], 0);
        if (!$montoRaw || $montoRaw == 0) {
            $montoRaw = $get(['debito', 'debit', 'cargo', 'monto', 'importe', 'valor', 'amount'], 0);
        }

        $val = trim((string) $montoRaw);
        $val = str_ireplace(['Q', ' '], '', $val);
        if (strpos($val, ',') !== false && strpos($val, '.') === false) {
            $val = str_replace(',', '.', $val);
        } else {
            $val = str_replace(',', '', $val);
        }
        $monto = (float) $val;

        return [
            'fecha'       => $fecha,
            'descripcion' => trim($desc),
            'referencia'  => trim($ref),
            'monto'       => round($monto, 2),
        ];
    }
}
