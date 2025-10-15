<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Marcas;
use App\Models\ProPago;
use App\Models\Producto;
use App\Models\ProVenta;
use App\Models\Categoria;
use App\Models\ProCliente;
use Illuminate\Http\Request;
use App\Models\ProMetodoPago;
use App\Models\ProDetallePago;
use App\Models\ProDetalleVenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProPorcentajeVendedor;

class ReportesController extends Controller
{
    /**
     * Vista principal de reportes
     */
    public function index()
    {
        return view('reportes.index');
    }


/**
 * Dashboard principal de reportes con KPIs - CORREGIDO
 */
public function getDashboard(Request $request): JsonResponse
{
    try {
        // Convertir fechas correctamente
        $fechaInicio = $request->filled('fecha_inicio') 
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : Carbon::now()->startOfMonth();
            
        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : Carbon::now()->endOfDay();
        
        \Log::info('Dashboard fechas:', [
            'inicio' => $fechaInicio->toDateTimeString(),
            'fin' => $fechaFin->toDateTimeString()
        ]);
        
        // KPIs principales
        $kpis = $this->getKPIs($fechaInicio, $fechaFin);
        
        // Gráficos
        $ventasPorDia = $this->getVentasPorDia($fechaInicio, $fechaFin);
        $productosMasVendidos = $this->getProductosMasVendidos($fechaInicio, $fechaFin);
        $ventasPorVendedor = $this->getVentasPorVendedor($fechaInicio, $fechaFin);
        $metodospagoStats = $this->getEstadisticasMetodosPago($fechaInicio, $fechaFin);

        \Log::info('Dashboard datos:', [
            'kpis' => $kpis,
            'ventas_por_dia_count' => count($ventasPorDia),
            'productos_count' => count($productosMasVendidos),
            'vendedores_count' => count($ventasPorVendedor),
            'metodos_pago_count' => count($metodospagoStats)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'kpis' => $kpis,
                'graficos' => [
                    'ventas_por_dia' => $ventasPorDia,
                    'productos_mas_vendidos' => $productosMasVendidos,
                    'ventas_por_vendedor' => $ventasPorVendedor,
                    'metodos_pago' => $metodospagoStats
                ]
            ],
            'periodo' => [
                'inicio' => $fechaInicio->format('Y-m-d'),
                'fin' => $fechaFin->format('Y-m-d')
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error en getDashboard:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar dashboard: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Reporte de ventas detallado
     */
   public function getReporteVentas(Request $request): JsonResponse
{
    try {
        $query = ProVenta::with([
            'vendedor:user_id,user_primer_nombre,user_primer_apellido',
            'detalleVentas.producto:producto_id,producto_nombre,pro_codigo_sku',
            'cliente:cliente_id,cliente_nombre1,cliente_apellido1,cliente_dpi',
            'pagos.detallesPago.metodoPago:metpago_id,metpago_descripcion'
        ]);

        // Filtro por fecha
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('ven_fecha', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('ven_fecha', '<=', $request->fecha_fin);
        }

        // Filtro por vendedor
        if ($request->filled('vendedor_id')) {
            $query->where('ven_user', $request->vendedor_id);
        }

        // Filtro por cliente - CORREGIDO: búsqueda por nombre o DPI
        if ($request->filled('cliente_buscar')) {
            $busqueda = $request->cliente_buscar;
            $query->whereHas('cliente', function($q) use ($busqueda) {
                $q->where('cliente_nombre1', 'LIKE', "%{$busqueda}%")
                  ->orWhere('cliente_apellido1', 'LIKE', "%{$busqueda}%")
                  ->orWhere('cliente_dpi', 'LIKE', "%{$busqueda}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('ven_situacion', $request->estado);
        }

        // Calcular estado de pago para cada venta
        $ventas = $query->orderBy('ven_fecha', 'desc')
                       ->paginate($request->get('per_page', 25));

        // Agregar información calculada a cada venta
        $ventas->getCollection()->transform(function ($venta) {
            // Calcular estado de pago
            $totalPagado = $venta->pagos->sum('pago_monto_abonado');
            $totalVenta = $venta->ven_total_vendido;
            
            if ($totalPagado == 0) {
                $venta->estado_pago = 'PENDIENTE';
            } elseif ($totalPagado >= $totalVenta) {
                $venta->estado_pago = 'COMPLETADO';
            } else {
                $venta->estado_pago = 'PARCIAL';
            }
            
            $venta->total_pagado = $totalPagado;
            $venta->saldo_pendiente = $totalVenta - $totalPagado;
            
            // Información del cliente
            $venta->cliente_nombre_completo = $venta->cliente 
                ? trim($venta->cliente->cliente_nombre1 . ' ' . $venta->cliente->cliente_apellido1)
                : 'Cliente General';
            
            // Información del vendedor
            $venta->vendedor_nombre_completo = $venta->vendedor
                ? trim($venta->vendedor->user_primer_nombre . ' ' . $venta->vendedor->user_primer_apellido)
                : 'Sin vendedor';
            
            // Resumen de productos
            $venta->total_productos = $venta->detalleVentas->sum('det_cantidad');
            $venta->cantidad_items = $venta->detalleVentas->count();
            
            return $venta;
        });

        // Resumen general
        $resumen = [
            'total_ventas' => $ventas->total(),
            'suma_ventas' => $query->sum('ven_total_vendido'),
            'promedio_venta' => $ventas->total() > 0 ? $query->sum('ven_total_vendido') / $ventas->total() : 0
        ];

        return response()->json([
            'success' => true,
            'data' => $ventas,
            'resumen' => $resumen
        ]);

    } catch (\Exception $e) {
        \Log::error('Error en getReporteVentas: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar reporte de ventas: ' . $e->getMessage()
        ], 500);
    }
}


 //Buscar clientes para autocomplete

public function buscarClientes(Request $request): JsonResponse
{
    try {
        $termino = $request->get('q', '');
        
        // 🔍 LOG para debug
        \Log::info('Búsqueda de clientes', [
            'termino' => $termino,
            'request_all' => $request->all()
        ]);
        
        // ✅ Construir query base
        $query = ProCliente::select(
                'cliente_id', 
                'cliente_nombre1', 
                'cliente_nombre2',
                'cliente_apellido1', 
                'cliente_apellido2',
                'cliente_dpi'
            )
            ->where('cliente_situacion', 1);
        
        // Si hay término de búsqueda, filtrar
        if (!empty($termino) && strlen($termino) >= 1) {
            $query->where(function($q) use ($termino) {
                $q->where('cliente_nombre1', 'LIKE', "%{$termino}%")
                  ->orWhere('cliente_nombre2', 'LIKE', "%{$termino}%")
                  ->orWhere('cliente_apellido1', 'LIKE', "%{$termino}%")
                  ->orWhere('cliente_apellido2', 'LIKE', "%{$termino}%")
                  ->orWhere('cliente_dpi', 'LIKE', "%{$termino}%");
            });
        }
        
        // Obtener resultados
        $clientes = $query->limit(10)
            ->orderBy('cliente_nombre1', 'asc')
            ->get()
            ->map(function($cliente) {
                $nombreCompleto = trim(
                    implode(' ', array_filter([
                        $cliente->cliente_nombre1,
                        $cliente->cliente_nombre2,
                        $cliente->cliente_apellido1,
                        $cliente->cliente_apellido2
                    ]))
                );
                
                return [
                    'id' => $cliente->cliente_id,
                    'text' => $nombreCompleto . 
                             ($cliente->cliente_dpi ? " (DPI: {$cliente->cliente_dpi})" : '')
                ];
            });

        // 🔍 LOG de resultados
        \Log::info('Clientes encontrados', [
            'cantidad' => $clientes->count(),
            'resultados' => $clientes->toArray()
        ]);

        return response()->json([
            'success' => true,
            'results' => $clientes,
            'total' => $clientes->count()
        ]);

    } catch (\Exception $e) {
        \Log::error('Error buscando clientes', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error buscando clientes: ' . $e->getMessage(),
            'results' => []
        ], 500);
    }
}

    /**
     * Reporte de productos más vendidos
     */
    public function getReporteProductos(Request $request): JsonResponse
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
            $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());

            $query = DB::table('pro_detalle_ventas as dv')
                ->join('pro_ventas as v', 'dv.det_ven_id', '=', 'v.ven_id')
                ->join('pro_productos as p', 'dv.det_producto_id', '=', 'p.producto_id')
                ->leftJoin('pro_categorias as c', 'p.producto_categoria_id', '=', 'c.categoria_id')
                ->leftJoin('pro_marcas as m', 'p.producto_marca_id', '=', 'm.marca_id')
                ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
                ->where('v.ven_situacion', 1)
                ->where('dv.det_situacion', 'ACTIVO')
                ->select([
                    'p.producto_id',
                    'p.producto_nombre',
                    'p.pro_codigo_sku',
                    'c.categoria_nombre',
                    'm.marca_descripcion as marca_nombre',
                    DB::raw('SUM(dv.det_cantidad) as total_vendido'),
                    DB::raw('AVG(dv.det_precio) as precio_promedio'),
                    DB::raw('SUM(dv.det_cantidad * dv.det_precio) as total_ingresos'),
                    DB::raw('COUNT(DISTINCT v.ven_id) as total_transacciones')
                ])
                ->groupBy('p.producto_id', 'p.producto_nombre', 'p.pro_codigo_sku', 'c.categoria_nombre', 'm.marca_descripcion');

            // Filtros adicionales
            if ($request->filled('categoria_id')) {
                $query->where('p.producto_categoria_id', $request->categoria_id);
            }

            if ($request->filled('marca_id')) {
                $query->where('p.producto_marca_id', $request->marca_id);
            }

            $productos = $query->orderBy('total_vendido', 'desc')
                              ->limit($request->get('limit', 50))
                              ->get();

            return response()->json([
                'success' => true,
                'data' => $productos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reporte de productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de comisiones de vendedores
     */
    public function getReporteComisiones(Request $request): JsonResponse
    {
        try {
            $query = ProPorcentajeVendedor::with([
                'vendedor:user_id,user_primer_nombre,user_primer_apellido',
                'venta:ven_id,ven_fecha,ven_total_vendido'
            ]);

            // Aplicar filtros por fecha
            if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
                $query->whereHas('venta', function($q) use ($request) {
                    if ($request->filled('fecha_inicio')) {
                        $q->whereDate('ven_fecha', '>=', $request->fecha_inicio);
                    }
                    if ($request->filled('fecha_fin')) {
                        $q->whereDate('ven_fecha', '<=', $request->fecha_fin);
                    }
                });
            }

            if ($request->filled('vendedor_id')) {
                $query->where('porc_vend_user_id', $request->vendedor_id);
            }

            if ($request->filled('estado')) {
                $query->where('porc_vend_estado', $request->estado);
            }

            $comisiones = $query->orderBy('porc_vend_fecha_asignacion', 'desc')
                               ->paginate($request->get('per_page', 25));

            // Resumen de comisiones
            $resumen = $this->getResumenComisiones($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'comisiones' => $comisiones,
                    'resumen' => $resumen
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reporte de comisiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de pagos y cuotas
     */
    public function getReportePagos(Request $request): JsonResponse
    {
        try {
            $query = ProPago::with([
                'venta:ven_id,ven_fecha,ven_total_vendido',
                'venta.vendedor:user_id,user_primer_nombre,user_primer_apellido',
                'venta.cliente:cliente_id,cliente_nombre1,cliente_apellido1',
                'detallesPago.metodoPago:metpago_id,metpago_descripcion'
            ]);

            // Filtros
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('pago_fecha_inicio', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin')) {
                $query->whereDate('pago_fecha_inicio', '<=', $request->fecha_fin);
            }

            if ($request->filled('estado')) {
                $query->where('pago_estado', $request->estado);
            }

            if ($request->filled('tipo_pago')) {
                $query->where('pago_tipo_pago', $request->tipo_pago);
            }

            $pagos = $query->orderBy('pago_fecha_inicio', 'desc')
                          ->paginate($request->get('per_page', 25));

            return response()->json([
                'success' => true,
                'data' => $pagos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reporte de pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar reporte a PDF
     */
public function exportarPDF(Request $request)
{
    try {
        $tipoReporte = $request->get('tipo_reporte');
        
        if (!$tipoReporte) {
            throw new \Exception('Tipo de reporte no especificado');
        }
        
        // Validar tipo de reporte
        $tiposValidos = ['ventas', 'productos', 'comisiones', 'pagos'];
        if (!in_array($tipoReporte, $tiposValidos)) {
            throw new \Exception('Tipo de reporte no válido: ' . $tipoReporte);
        }
        
        $data = $this->getDataForExport($request);
        
        // Verificar que tenemos datos
        if (empty($data['data']) && !isset($data['error'])) {
            throw new \Exception('No se encontraron datos para el reporte');
        }
        
        // Verificar que existe la vista
        $vistaPath = "reportes.pdf.{$tipoReporte}";
        if (!view()->exists($vistaPath)) {
            throw new \Exception("Vista no encontrada: {$vistaPath}");
        }
        
        $pdf = Pdf::loadView($vistaPath, $data);
        
        // Configurar el PDF
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('defaultFont', 'sans-serif');
        
        $filename = "reporte_{$tipoReporte}_" . date('Y-m-d_H-i-s') . ".pdf";
        
        return $pdf->download($filename);

    } catch (\Exception $e) {
        \Log::error('Error generando PDF: ' . $e->getMessage(), [
            'request' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al generar PDF: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Exportar reporte a Excel
     */
    public function exportarExcel(Request $request)
    {
        try {
            $tipoReporte = $request->get('tipo_reporte');
            
            return Excel::download(
                new \App\Exports\ReportesExport($request->all()),
                "reporte_{$tipoReporte}_" . date('Y-m-d') . ".xlsx"
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para filtros
     */
    public function getFiltros(): JsonResponse
    {
        try {
            $vendedores = User::select('user_id', 'user_primer_nombre', 'user_primer_apellido')
                             ->where('user_situacion', 1)
                             ->orderBy('user_primer_nombre')
                             ->get();

            $categorias = Categoria::select('categoria_id', 'categoria_nombre')
                                  ->where('categoria_situacion', 1)
                                  ->orderBy('categoria_nombre')
                                  ->get();

            $marcas = Marcas::select('marca_id', 'marca_descripcion')
                           ->where('marca_situacion', 1)
                           ->orderBy('marca_descripcion')
                           ->get();

            $metodosPago = ProMetodoPago::select('metpago_id', 'metpago_descripcion')
                                      ->where('metpago_situacion', 1)
                                      ->orderBy('metpago_descripcion')
                                      ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'vendedores' => $vendedores,
                    'categorias' => $categorias,
                    'marcas' => $marcas,
                    'metodos_pago' => $metodosPago
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar filtros: ' . $e->getMessage()
            ], 500);
        }
    }

   // ================================
// MÉTODOS PRIVADOS CORREGIDOS
// ================================

private function getKPIs($fechaInicio, $fechaFin)
{
    try {
        $ventasQuery = ProVenta::whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
                              ->where('ven_situacion', 1);

        $totalVentas = $ventasQuery->count();
        $montoTotal = $ventasQuery->sum('ven_total_vendido') ?? 0;
        $promedioVenta = $totalVentas > 0 ? round($montoTotal / $totalVentas, 2) : 0;

        $productosVendidos = ProDetalleVenta::whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
              ->where('ven_situacion', 1);
        })->where('det_situacion', 'ACTIVO')
          ->sum('det_cantidad') ?? 0;

        $comisionesPendientes = ProPorcentajeVendedor::whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('ven_fecha', [$fechaInicio, $fechaFin]);
        })->where('porc_vend_estado', 'PENDIENTE')
          ->sum('porc_vend_cantidad_ganancia') ?? 0;

        return [
            'total_ventas' => (int) $totalVentas,
            'monto_total' => (float) $montoTotal,
            'promedio_venta' => (float) $promedioVenta,
            'productos_vendidos' => (int) $productosVendidos,
            'comisiones_pendientes' => (float) $comisionesPendientes
        ];
    } catch (\Exception $e) {
        \Log::error('Error en getKPIs: ' . $e->getMessage());
        return [
            'total_ventas' => 0,
            'monto_total' => 0,
            'promedio_venta' => 0,
            'productos_vendidos' => 0,
            'comisiones_pendientes' => 0
        ];
    }
}

private function getVentasPorDia($fechaInicio, $fechaFin)
{
    try {
        $ventas = DB::table('pro_ventas')
            ->selectRaw('DATE(ven_fecha) as fecha, COUNT(*) as total_ventas, COALESCE(SUM(ven_total_vendido), 0) as monto_total')
            ->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
            ->where('ven_situacion', 1)
            ->groupBy(DB::raw('DATE(ven_fecha)'))
            ->orderBy('fecha')
            ->get();

        \Log::info('Ventas por día query:', [
            'count' => $ventas->count(),
            'first' => $ventas->first()
        ]);

        return $ventas->map(function($item) {
            return [
                'fecha' => $item->fecha,
                'total_ventas' => (int) $item->total_ventas,
                'monto_total' => (float) $item->monto_total
            ];
        });
    } catch (\Exception $e) {
        \Log::error('Error en getVentasPorDia: ' . $e->getMessage());
        return collect([]);
    }
}

private function getProductosMasVendidos($fechaInicio, $fechaFin, $limit = 10)
{
    try {
        $productos = DB::table('pro_detalle_ventas as dv')
            ->join('pro_ventas as v', 'dv.det_ven_id', '=', 'v.ven_id')
            ->join('pro_productos as p', 'dv.det_producto_id', '=', 'p.producto_id')
            ->selectRaw('
                p.producto_id,
                p.producto_nombre, 
                SUM(dv.det_cantidad) as total_vendido,
                COALESCE(SUM(dv.det_cantidad * dv.det_precio), 0) as total_ingresos
            ')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->where('dv.det_situacion', 'ACTIVO')
            ->groupBy('p.producto_id', 'p.producto_nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit($limit)
            ->get();

        \Log::info('Productos más vendidos:', [
            'count' => $productos->count(),
            'first' => $productos->first()
        ]);

        return $productos->map(function($item) {
            return [
                'producto_id' => $item->producto_id,
                'producto_nombre' => $item->producto_nombre,
                'total_vendido' => (int) $item->total_vendido,
                'total_ingresos' => (float) $item->total_ingresos
            ];
        });
    } catch (\Exception $e) {
        \Log::error('Error en getProductosMasVendidos: ' . $e->getMessage());
        return collect([]);
    }
}

private function getVentasPorVendedor($fechaInicio, $fechaFin)
{
    try {
        $ventas = DB::table('pro_ventas as v')
            ->join('users as u', 'v.ven_user', '=', 'u.user_id')
            ->selectRaw('
                u.user_id,
                CONCAT(u.user_primer_nombre, " ", u.user_primer_apellido) as vendedor, 
                COUNT(*) as total_ventas, 
                COALESCE(SUM(v.ven_total_vendido), 0) as monto_total
            ')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->groupBy('u.user_id', 'u.user_primer_nombre', 'u.user_primer_apellido')
            ->orderBy('monto_total', 'desc')
            ->get();

        \Log::info('Ventas por vendedor:', [
            'count' => $ventas->count(),
            'first' => $ventas->first()
        ]);

        return $ventas->map(function($item) {
            return [
                'vendedor_id' => $item->user_id,
                'vendedor' => $item->vendedor,
                'total_ventas' => (int) $item->total_ventas,
                'monto_total' => (float) $item->monto_total
            ];
        });
    } catch (\Exception $e) {
        \Log::error('Error en getVentasPorVendedor: ' . $e->getMessage());
        return collect([]);
    }
}

private function getEstadisticasMetodosPago($fechaInicio, $fechaFin)
{
    try {
        $stats = DB::table('pro_detalle_pagos as dp')
            ->join('pro_pagos as p', 'dp.det_pago_pago_id', '=', 'p.pago_id')
            ->join('pro_ventas as v', 'p.pago_venta_id', '=', 'v.ven_id')
            ->join('pro_metodos_pago as mp', 'dp.det_pago_metodo_pago', '=', 'mp.metpago_id')
            ->selectRaw('
                mp.metpago_id,
                mp.metpago_descripcion as metodo, 
                COUNT(*) as total_transacciones, 
                COALESCE(SUM(dp.det_pago_monto), 0) as monto_total
            ')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->where('dp.det_pago_estado', 'VALIDO')
            ->groupBy('mp.metpago_id', 'mp.metpago_descripcion')
            ->orderBy('monto_total', 'desc')
            ->get();

        \Log::info('Métodos de pago stats:', [
            'count' => $stats->count(),
            'first' => $stats->first()
        ]);

        return $stats->map(function($item) {
            return [
                'metodo_id' => $item->metpago_id,
                'metodo' => $item->metodo,
                'total_transacciones' => (int) $item->total_transacciones,
                'monto_total' => (float) $item->monto_total
            ];
        });
    } catch (\Exception $e) {
        \Log::error('Error en getEstadisticasMetodosPago: ' . $e->getMessage());
        return collect([]);
    }
}
    private function getResumenComisiones($request)
    {
        $query = ProPorcentajeVendedor::query();

        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $query->whereHas('venta', function($q) use ($request) {
                if ($request->filled('fecha_inicio')) {
                    $q->whereDate('ven_fecha', '>=', $request->fecha_inicio);
                }
                if ($request->filled('fecha_fin')) {
                    $q->whereDate('ven_fecha', '<=', $request->fecha_fin);
                }
            });
        }

        if ($request->filled('vendedor_id')) {
            $query->where('porc_vend_user_id', $request->vendedor_id);
        }

        return [
            'total_comisiones' => $query->sum('porc_vend_cantidad_ganancia'),
            'pendientes' => $query->where('porc_vend_estado', 'PENDIENTE')->sum('porc_vend_cantidad_ganancia'),
            'pagadas' => $query->where('porc_vend_estado', 'PAGADO')->sum('porc_vend_cantidad_ganancia'),
            'canceladas' => $query->where('porc_vend_estado', 'CANCELADO')->sum('porc_vend_cantidad_ganancia')
        ];
    }

private function getDataForExport($request)
{
    $tipo = $request->get('tipo_reporte');
    
    try {
        switch ($tipo) {
            case 'ventas':
                $response = $this->getReporteVentas($request);
                $responseData = $response->getData(true);
                
                // Verificar que existe la estructura esperada
                if (!isset($responseData['success']) || !$responseData['success']) {
                    throw new \Exception('Error obteniendo datos de ventas');
                }
                
                return [
                    'data' => $responseData['data'] ?? [],
                    'tipo' => 'ventas',
                    'titulo' => 'Reporte de Ventas',
                    'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                    'filtros' => $request->all()
                ];
                
            case 'productos':
                $response = $this->getReporteProductos($request);
                $responseData = $response->getData(true);
                
                if (!isset($responseData['success']) || !$responseData['success']) {
                    throw new \Exception('Error obteniendo datos de productos');
                }
                
                return [
                    'data' => $responseData['data'] ?? [],
                    'tipo' => 'productos',
                    'titulo' => 'Productos Más Vendidos',
                    'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                    'filtros' => $request->all()
                ];
                
            case 'comisiones':
                $response = $this->getReporteComisiones($request);
                $responseData = $response->getData(true);
                
                if (!isset($responseData['success']) || !$responseData['success']) {
                    throw new \Exception('Error obteniendo datos de comisiones');
                }
                
                // Para comisiones, los datos están en una estructura diferente
                return [
                    'data' => $responseData['data']['comisiones'] ?? [],
                    'resumen' => $responseData['data']['resumen'] ?? [],
                    'tipo' => 'comisiones',
                    'titulo' => 'Reporte de Comisiones',
                    'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                    'filtros' => $request->all()
                ];
                
            case 'pagos':
                $response = $this->getReportePagos($request);
                $responseData = $response->getData(true);
                
                if (!isset($responseData['success']) || !$responseData['success']) {
                    throw new \Exception('Error obteniendo datos de pagos');
                }
                
                return [
                    'data' => $responseData['data'] ?? [],
                    'tipo' => 'pagos',
                    'titulo' => 'Estado de Pagos',
                    'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                    'filtros' => $request->all()
                ];
                
            default:
                throw new \Exception('Tipo de reporte no válido: ' . $tipo);
        }
    } catch (\Exception $e) {
        \Log::error('Error en getDataForExport: ' . $e->getMessage());
        return [
            'data' => [],
            'error' => $e->getMessage(),
            'tipo' => $tipo ?? 'desconocido',
            'titulo' => 'Error en Reporte',
            'fecha_generacion' => now()->format('d/m/Y H:i:s')
        ];
    }
}



/**
 * Reporte DIGECAM - Ventas de Armas de Fuego
 */
public function getReporteDigecamArmas(Request $request): JsonResponse
{
    try {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        
        $ventas = DB::table('pro_detalle_ventas as dv')
            ->join('pro_ventas as v', 'dv.det_ven_id', '=', 'v.ven_id')
            ->join('pro_productos as p', 'dv.det_producto_id', '=', 'p.producto_id')
            ->leftJoin('pro_marcas as m', 'p.producto_marca_id', '=', 'm.marca_id')
            ->leftJoin('pro_clientes as c', 'v.ven_cliente', '=', 'c.cliente_id')
            ->select([
                'p.pro_tenencia_anterior',
                'p.pro_tenencia_nueva',
                'p.producto_nombre as tipo',
                'p.pro_numero_serie as serie',
                'm.marca_descripcion as marca',
                'p.producto_modelo as modelo',
                'p.producto_calibre as calibre',
                DB::raw('CONCAT(c.cliente_nombre1, " ", c.cliente_apellido1) as comprador'),
                'v.ven_id as autorizacion',
                'v.ven_fecha as fecha',
                'dv.det_factura as factura'
            ])
            ->whereYear('v.ven_fecha', $anio)
            ->whereMonth('v.ven_fecha', $mes)
            ->where('v.ven_situacion', 1)
            ->where('dv.det_situacion', 'ACTIVO')
            ->whereIn('p.producto_categoria_id', function($query) {
                $query->select('categoria_id')
                      ->from('pro_categorias')
                      ->where('categoria_nombre', 'LIKE', '%ARMA%')
                      ->orWhere('categoria_nombre', 'LIKE', '%CARABINA%')
                      ->orWhere('categoria_nombre', 'LIKE', '%PISTOLA%');
            })
            ->orderBy('v.ven_fecha')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ventas,
            'mes' => $mes,
            'anio' => $anio,
            'mes_nombre' => $this->getNombreMes($mes)
        ]);

    } catch (\Exception $e) {
        \Log::error('Error en getReporteDigecamArmas: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar reporte de armas: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Reporte DIGECAM - Ventas de Municiones
 */
public function getReporteDigecamMuniciones(Request $request): JsonResponse
{
    try {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = now()->startOfMonth()->format('Y-m-d');
            $fechaFin = now()->endOfMonth()->format('Y-m-d');
        }

        $ventas = DB::table('pro_detalle_ventas as dv')
            ->join('pro_ventas as v', 'dv.det_ven_id', '=', 'v.ven_id')
            ->join('pro_productos as p', 'dv.det_producto_id', '=', 'p.producto_id')
            ->leftJoin('pro_clientes as c', 'v.ven_cliente', '=', 'c.cliente_id')
            ->select([
                'v.ven_id as autorizacion',
                DB::raw('CASE 
                    WHEN c.cliente_licencia IS NOT NULL THEN "LICENCIA"
                    WHEN c.cliente_tenencia IS NOT NULL THEN "TENENCIA"
                    ELSE "DOCUMENTO"
                END as documento'),
                DB::raw('CONCAT(c.cliente_nombre1, " ", COALESCE(c.cliente_nombre2, ""), " ", c.cliente_apellido1, " ", COALESCE(c.cliente_apellido2, "")) as nombre'),
                'dv.det_factura as factura',
                'v.ven_fecha as fecha',
                'p.pro_numero_serie as serie_arma',
                'p.producto_nombre as clase_arma',
                'p.producto_calibre as calibre_arma',
                'p.producto_calibre as calibre_vendido',
                'dv.det_cantidad as cantidad'
            ])
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->where('dv.det_situacion', 'ACTIVO')
            ->whereIn('p.producto_categoria_id', function($query) {
                $query->select('categoria_id')
                      ->from('pro_categorias')
                      ->where('categoria_nombre', 'LIKE', '%MUNICION%')
                      ->orWhere('categoria_nombre', 'LIKE', '%CARTUCHO%')
                      ->orWhere('categoria_nombre', 'LIKE', '%BALA%');
            })
            ->orderBy('v.ven_fecha')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ventas,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);

    } catch (\Exception $e) {
        \Log::error('Error en getReporteDigecamMuniciones: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar reporte de municiones: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Exportar reportes DIGECAM a PDF
 */
public function exportarDigecamPDF(Request $request)
{
    try {
        $tipo = $request->get('tipo');
        
        if ($tipo === 'armas') {
            $response = $this->getReporteDigecamArmas($request);
            $data = $response->getData(true);
            
            $pdf = Pdf::loadView('reportes.pdf.digecam-armas', [
                'data' => $data['data'],
                'mes' => $data['mes_nombre'],
                'anio' => $data['anio'],
                'operador' => auth()->user()->user_primer_nombre . ' ' . auth()->user()->user_primer_apellido,
                'empresa' => 'PRO ARMAS',
                'fecha_generacion' => now()->format('d/m/Y H:i:s')
            ]);
            
            $filename = "reporte_armas_" . $data['mes'] . "_" . $data['anio'] . ".pdf";
            
        } else {
            $response = $this->getReporteDigecamMuniciones($request);
            $data = $response->getData(true);
            
            $pdf = Pdf::loadView('reportes.pdf.digecam-municiones', [
                'data' => $data['data'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'empresa' => 'PRO ARMAS',
                'fecha_generacion' => now()->format('d/m/Y H:i:s')
            ]);
            
            $filename = "reporte_municiones_" . date('Y-m-d') . ".pdf";
        }
        
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf->download($filename);

    } catch (\Exception $e) {
        \Log::error('Error generando PDF DIGECAM: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al generar PDF: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper: Obtener nombre del mes
 */
private function getNombreMes($mes)
{
    $meses = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
        5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
    ];
    
    return $meses[$mes] ?? 'DESCONOCIDO';
}

}