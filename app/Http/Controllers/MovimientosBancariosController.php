<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MovimientosBancariosController extends Controller
{
    /**
     * Mostrar la vista principal del módulo
     */
    public function index()
    {
        return view('admin.movimientos.index');
    }

    /**
     * Validar pagos automáticamente comparando con movimientos bancarios
     */
    public function validarPagos(Request $request)
    {
        $request->validate([
            'movimientos' => 'required|array',
            'banco_id' => 'required|integer',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $movimientos = $request->movimientos;
            $bancoId = $request->banco_id;
            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;

            // Obtener pagos pendientes de validación en el período
            $pagosPendientes = DB::table('pagos as p')
                ->join('cuotas as c', 'p.cuota_id', '=', 'c.id')
                ->join('ventas as v', 'c.venta_id', '=', 'v.id')
                ->where('p.estado', 'PENDIENTE')
                ->where('p.banco_id', $bancoId)
                ->whereDate('p.created_at', '>=', $fechaInicio)
                ->whereDate('p.created_at', '<=', $fechaFin)
                ->select([
                    'p.id as pago_id',
                    'p.monto',
                    'p.referencia',
                    'p.fecha_pago',
                    'p.concepto',
                    'c.id as cuota_id',
                    'v.cliente_id'
                ])
                ->get();

            $validados = 0;
            $pendientes = 0;
            $procesados = count($movimientos);

            // Insertar movimientos bancarios
            foreach ($movimientos as $mov) {
                $movimientoId = DB::table('movimientos_bancarios')->insertGetId([
                    'banco_id' => $bancoId,
                    'fecha' => $this->parseDate($mov['fecha']),
                    'descripcion' => $mov['descripcion'],
                    'referencia' => $mov['referencia'],
                    'monto' => $mov['monto'],
                    'tipo' => 'CREDITO',
                    'estado' => 'PROCESADO',
                    'periodo_inicio' => $fechaInicio,
                    'periodo_fin' => $fechaFin,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Buscar coincidencias con pagos pendientes
                $coincidencias = $this->buscarCoincidencias($mov, $pagosPendientes);

                foreach ($coincidencias as $pago) {
                    // Validar el pago
                    DB::table('pagos')
                        ->where('id', $pago->pago_id)
                        ->update([
                            'estado' => 'VALIDADO',
                            'movimiento_bancario_id' => $movimientoId,
                            'fecha_validacion' => now(),
                            'validado_por' => auth()->id(),
                            'updated_at' => now()
                        ]);

                    // Actualizar estado de la cuota
                    $this->actualizarEstadoCuota($pago->cuota_id);

                    // Registrar log de validación
                    DB::table('validaciones_pagos')->insert([
                        'pago_id' => $pago->pago_id,
                        'movimiento_bancario_id' => $movimientoId,
                        'tipo_coincidencia' => 'AUTOMATICA',
                        'porcentaje_coincidencia' => $this->calcularPorcentajeCoincidencia($mov, $pago),
                        'validado_por' => auth()->id(),
                        'created_at' => now()
                    ]);

                    $validados++;
                }
            }

            // Contar pendientes restantes
            $pendientes = DB::table('pagos')
                ->where('estado', 'PENDIENTE')
                ->where('banco_id', $bancoId)
                ->whereDate('created_at', '>=', $fechaInicio)
                ->whereDate('created_at', '<=', $fechaFin)
                ->count();

            // Registrar la carga
            DB::table('cargas_movimientos')->insert([
                'banco_id' => $bancoId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'total_registros' => $procesados,
                'validados' => $validados,
                'pendientes' => $pendientes,
                'archivo_nombre' => "movimientos_{$bancoId}_" . date('Y-m-d_H-i-s'),
                'procesado_por' => auth()->id(),
                'created_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Movimientos procesados exitosamente',
                'data' => [
                    'validados' => $validados,
                    'pendientes' => $pendientes,
                    'procesados' => $procesados,
                    'efectividad' => $procesados > 0 ? round(($validados / $procesados) * 100, 2) : 0
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error validando pagos: ' . $e->getMessage());

            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error procesando movimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar coincidencias entre movimiento bancario y pagos pendientes
     */
    private function buscarCoincidencias($movimiento, $pagosPendientes)
    {
        $coincidencias = [];
        $tolerancia = 0.01; // Tolerancia de Q0.01

        foreach ($pagosPendientes as $pago) {
            $score = 0;

            // 1. Coincidencia exacta de monto (peso: 40%)
            if (abs($movimiento['monto'] - $pago->monto) <= $tolerancia) {
                $score += 40;
            }

            // 2. Coincidencia de referencia (peso: 35%)
            if ($this->compararReferencias($movimiento['referencia'], $pago->referencia)) {
                $score += 35;
            }

            // 3. Coincidencia de fecha (peso: 15%)
            if ($this->compararFechas($movimiento['fecha'], $pago->fecha_pago)) {
                $score += 15;
            }

            // 4. Coincidencia de concepto (peso: 10%)
            if ($this->compararConceptos($movimiento['descripcion'], $pago->concepto)) {
                $score += 10;
            }

            // Si el score es >= 75%, consideramos que es una coincidencia válida
            if ($score >= 75) {
                $coincidencias[] = (object) array_merge((array) $pago, ['score' => $score]);
            }
        }

        // Ordenar por score descendente
        usort($coincidencias, function($a, $b) {
            return $b->score <=> $a->score;
        });

        return $coincidencias;
    }

    /**
     * Comparar referencias con diferentes formatos
     */
    private function compararReferencias($refMovimiento, $refPago)
    {
        if (empty($refMovimiento) || empty($refPago)) {
            return false;
        }

        // Limpiar referencias (solo números)
        $ref1 = preg_replace('/[^0-9]/', '', $refMovimiento);
        $ref2 = preg_replace('/[^0-9]/', '', $refPago);

        // Coincidencia exacta
        if ($ref1 === $ref2) {
            return true;
        }

        // Si una referencia está contenida en la otra (mínimo 6 dígitos)
        if (strlen($ref1) >= 6 && strlen($ref2) >= 6) {
            return strpos($ref1, $ref2) !== false || strpos($ref2, $ref1) !== false;
        }

        return false;
    }

    /**
     * Comparar fechas con tolerancia de ±3 días
     */
    private function compararFechas($fechaMovimiento, $fechaPago)
    {
        try {
            $fecha1 = Carbon::parse($this->parseDate($fechaMovimiento));
            $fecha2 = Carbon::parse($fechaPago);

            return abs($fecha1->diffInDays($fecha2)) <= 3;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Comparar conceptos usando similitud de texto
     */
    private function compararConceptos($descripcionMovimiento, $conceptoPago)
    {
        if (empty($descripcionMovimiento) || empty($conceptoPago)) {
            return false;
        }

        $desc1 = strtolower(trim($descripcionMovimiento));
        $desc2 = strtolower(trim($conceptoPago));

        // Buscar palabras clave comunes
        $palabrasClave = ['pago', 'servicio', 'factura', 'cuota', 'abono'];
        
        foreach ($palabrasClave as $palabra) {
            if (strpos($desc1, $palabra) !== false && strpos($desc2, $palabra) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcular porcentaje de coincidencia
     */
    private function calcularPorcentajeCoincidencia($movimiento, $pago)
    {
        $score = 0;

        if (abs($movimiento['monto'] - $pago->monto) <= 0.01) $score += 40;
        if ($this->compararReferencias($movimiento['referencia'], $pago->referencia)) $score += 35;
        if ($this->compararFechas($movimiento['fecha'], $pago->fecha_pago)) $score += 15;
        if ($this->compararConceptos($movimiento['descripcion'], $pago->concepto)) $score += 10;

        return $score;
    }

    /**
     * Actualizar estado de cuota basado en pagos
     */
    // private function actualizarEstadoCuota($cuotaId)
    // {
    //     $cuota = DB::table('cuotas')->where('id', $cuotaId)->first();
    //     $totalPagado = DB::table('pagos')
    //         ->where('cuota_id', $cuotaId)
    //         ->where('estado', 'VALIDADO')
    //         ->sum('monto');

    //     $estado = 'PENDIENTE';
    //     if ($totalPagado >= $cuota->monto) {
    //         $estado = 'PAGADA';
    /**
     * Actualizar estado de cuota basado en pagos
     */
    private function actualizarEstadoCuota($cuotaId)
    {
        $cuota = DB::table('cuotas')->where('id', $cuotaId)->first();
        $totalPagado = DB::table('pagos')
            ->where('cuota_id', $cuotaId)
            ->where('estado', 'VALIDADO')
            ->sum('monto');

        $estado = 'PENDIENTE';
        if ($totalPagado >= $cuota->monto) {
            $estado = 'PAGADA';
        } elseif ($totalPagado > 0) {
            $estado = 'PARCIAL';
        }

        DB::table('cuotas')
            ->where('id', $cuotaId)
            ->update([
                'estado' => $estado,
                'updated_at' => now()
            ]);

        // Actualizar estado de la venta
        $this->actualizarEstadoVenta($cuota->venta_id);
    }

    /**
     * Actualizar estado de venta basado en cuotas
     */
    private function actualizarEstadoVenta($ventaId)
    {
        $totalCuotas = DB::table('cuotas')->where('venta_id', $ventaId)->count();
        $cuotasPagadas = DB::table('cuotas')->where('venta_id', $ventaId)->where('estado', 'PAGADA')->count();
        $cuotasParciales = DB::table('cuotas')->where('venta_id', $ventaId)->where('estado', 'PARCIAL')->count();

        $estado = 'PENDIENTE';
        if ($cuotasPagadas == $totalCuotas) {
            $estado = 'COMPLETADO';
        } elseif ($cuotasPagadas > 0 || $cuotasParciales > 0) {
            $estado = 'PARCIAL';
        }

        DB::table('ventas')
            ->where('id', $ventaId)
            ->update([
                'estado_pago' => $estado,
                'updated_at' => now()
            ]);
    }

    /**
     * Parsear diferentes formatos de fecha guatemaltecos
     */
    private function parseDate($dateString)
    {
        $dateString = trim($dateString);
        
        // Formato DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateString, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }
        
        // Formato DD/MM/YY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2})$/', $dateString, $matches)) {
            $year = (int)$matches[3];
            $year += ($year < 50) ? 2000 : 1900; // Asumir 2000s si < 50, sino 1900s
            return sprintf('%04d-%02d-%02d', $year, $matches[2], $matches[1]);
        }
        
        // Intentar parsear con Carbon como fallback
        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return date('Y-m-d');
        }
    }

    /**
     * Obtener estadísticas del sistema
     */
    public function estadisticas()
    {
        try {
            $stats = [
                'validados' => DB::table('pagos')->where('estado', 'VALIDADO')->count(),
                'pendientes' => DB::table('pagos')->where('estado', 'PENDIENTE')->count(),
                'total' => DB::table('movimientos_bancarios')->count(),
                'ultimaCarga' => DB::table('cargas_movimientos')
                    ->orderBy('created_at', 'desc')
                    ->value('created_at')
            ];

            if ($stats['ultimaCarga']) {
                $stats['ultimaCarga'] = Carbon::parse($stats['ultimaCarga'])->format('d/m/Y H:i');
            }

            return response()->json([
                'codigo' => 1,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error obteniendo estadísticas'
            ]);
        }
    }

    /**
     * Obtener historial de cargas
     */
    public function historial()
    {
        try {
            $historial = DB::table('cargas_movimientos as cm')
                ->join('users as u', 'cm.procesado_por', '=', 'u.id')
                ->select([
                    'cm.*',
                    'u.name as procesado_por_nombre'
                ])
                ->orderBy('cm.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($carga) {
                    return [
                        'banco_id' => $carga->banco_id,
                        'fecha' => $carga->created_at,
                        'registros' => $carga->total_registros,
                        'validados' => $carga->validados,
                        'pendientes' => $carga->pendientes,
                        'procesado_por' => $carga->procesado_por_nombre
                    ];
                });

            return response()->json([
                'codigo' => 1,
                'data' => $historial
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error obteniendo historial'
            ]);
        }
    }

    /**
     * Validar manualmente un pago específico
     */
    public function validarPagoManual(Request $request)
    {
        $request->validate([
            'pago_id' => 'required|integer',
            'movimiento_id' => 'required|integer',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pagoId = $request->pago_id;
            $movimientoId = $request->movimiento_id;

            // Actualizar pago
            DB::table('pagos')
                ->where('id', $pagoId)
                ->update([
                    'estado' => 'VALIDADO',
                    'movimiento_bancario_id' => $movimientoId,
                    'fecha_validacion' => now(),
                    'validado_por' => auth()->id(),
                    'observaciones' => $request->observaciones,
                    'updated_at' => now()
                ]);

            // Registrar validación manual
            DB::table('validaciones_pagos')->insert([
                'pago_id' => $pagoId,
                'movimiento_bancario_id' => $movimientoId,
                'tipo_coincidencia' => 'MANUAL',
                'porcentaje_coincidencia' => 100,
                'observaciones' => $request->observaciones,
                'validado_por' => auth()->id(),
                'created_at' => now()
            ]);

            // Actualizar estado de cuota
            $pago = DB::table('pagos as p')
                ->join('cuotas as c', 'p.cuota_id', '=', 'c.id')
                ->where('p.id', $pagoId)
                ->select('c.id as cuota_id')
                ->first();

            if ($pago) {
                $this->actualizarEstadoCuota($pago->cuota_id);
            }

            DB::commit();

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Pago validado manualmente exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error validando pago manual: ' . $e->getMessage());

            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error validando pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener pagos pendientes para revisión manual
     */
    public function pagosPendientes(Request $request)
    {
        try {
            $query = DB::table('pagos as p')
                ->join('cuotas as c', 'p.cuota_id', '=', 'c.id')
                ->join('ventas as v', 'c.venta_id', '=', 'v.id')
                ->join('clientes as cl', 'v.cliente_id', '=', 'cl.id')
                ->where('p.estado', 'PENDIENTE')
                ->select([
                    'p.id as pago_id',
                    'p.monto',
                    'p.referencia',
                    'p.fecha_pago',
                    'p.concepto',
                    'p.banco_id',
                    'c.numero as cuota_numero',
                    'v.id as venta_id',
                    'cl.nombre as cliente_nombre',
                    'p.created_at'
                ]);

            // Filtros opcionales
            if ($request->banco_id) {
                $query->where('p.banco_id', $request->banco_id);
            }

            if ($request->fecha_desde) {
                $query->whereDate('p.created_at', '>=', $request->fecha_desde);
            }

            if ($request->fecha_hasta) {
                $query->whereDate('p.created_at', '<=', $request->fecha_hasta);
            }

            $pagos = $query->orderBy('p.created_at', 'desc')
                           ->paginate(50);

            return response()->json([
                'codigo' => 1,
                'data' => $pagos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error obteniendo pagos pendientes'
            ]);
        }
    }

    /**
     * Buscar posibles coincidencias para un pago específico
     */
    public function buscarCoincidenciasPago($pagoId)
    {
        try {
            $pago = DB::table('pagos as p')
                ->join('cuotas as c', 'p.cuota_id', '=', 'c.id')
                ->where('p.id', $pagoId)
                ->select([
                    'p.*',
                    'c.venta_id'
                ])
                ->first();

            if (!$pago) {
                return response()->json([
                    'codigo' => 0,
                    'mensaje' => 'Pago no encontrado'
                ], 404);
            }

            // Buscar movimientos bancarios sin asignar del mismo banco
            $movimientos = DB::table('movimientos_bancarios')
                ->where('banco_id', $pago->banco_id)
                ->where('estado', 'PROCESADO')
                ->whereNull('pago_asignado_id')
                ->whereBetween('fecha', [
                    Carbon::parse($pago->fecha_pago)->subDays(7)->format('Y-m-d'),
                    Carbon::parse($pago->fecha_pago)->addDays(7)->format('Y-m-d')
                ])
                ->get();

            $coincidencias = [];
            foreach ($movimientos as $mov) {
                $movArray = [
                    'monto' => $mov->monto,
                    'referencia' => $mov->referencia,
                    'fecha' => $mov->fecha,
                    'descripcion' => $mov->descripcion
                ];

                $score = $this->calcularPorcentajeCoincidencia($movArray, $pago);
                
                if ($score >= 40) { // Umbral más bajo para sugerencias manuales
                    $coincidencias[] = [
                        'movimiento' => $mov,
                        'score' => $score,
                        'recomendacion' => $score >= 75 ? 'ALTA' : ($score >= 60 ? 'MEDIA' : 'BAJA')
                    ];
                }
            }

            // Ordenar por score
            usort($coincidencias, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            return response()->json([
                'codigo' => 1,
                'data' => [
                    'pago' => $pago,
                    'coincidencias' => array_slice($coincidencias, 0, 10) // Max 10 sugerencias
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error buscando coincidencias'
            ]);
        }
    }

    /**
     * Rechazar un pago
     */
    public function rechazarPago(Request $request)
    {
        $request->validate([
            'pago_id' => 'required|integer',
            'motivo' => 'required|string|max:500'
        ]);

        try {
            DB::table('pagos')
                ->where('id', $request->pago_id)
                ->update([
                    'estado' => 'RECHAZADO',
                    'motivo_rechazo' => $request->motivo,
                    'rechazado_por' => auth()->id(),
                    'fecha_rechazo' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'codigo' => 1,
                'mensaje' => 'Pago rechazado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'codigo' => 0,
                'mensaje' => 'Error rechazando pago'
            ]);
        }
    }
}