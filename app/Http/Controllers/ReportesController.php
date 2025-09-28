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
     * Dashboard principal de reportes con KPIs
     */
    public function getDashboard(Request $request): JsonResponse
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth());
            $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth());
            
            // KPIs principales
            $kpis = $this->getKPIs($fechaInicio, $fechaFin);
            
            // Gráficos
            $ventasPorDia = $this->getVentasPorDia($fechaInicio, $fechaFin);
            $productosMasVendidos = $this->getProductosMasVendidos($fechaInicio, $fechaFin);
            $ventasPorVendedor = $this->getVentasPorVendedor($fechaInicio, $fechaFin);
            $metodospagoStats = $this->getEstadisticasMetodosPago($fechaInicio, $fechaFin);

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
                ]
            ]);

        } catch (\Exception $e) {
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
                'cliente:cliente_id,cliente_nombre1,cliente_apellido1',
                'pagos.metodo:metpago_id,metpago_descripcion'
            ]);

            // Aplicar filtros
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('ven_fecha', '>=', $request->fecha_inicio);
            }
            
            if ($request->filled('fecha_fin')) {
                $query->whereDate('ven_fecha', '<=', $request->fecha_fin);
            }

            if ($request->filled('vendedor_id')) {
                $query->where('ven_user', $request->vendedor_id);
            }

            if ($request->filled('cliente_id')) {
                $query->where('ven_cliente', $request->cliente_id);
            }

            if ($request->filled('estado')) {
                $query->where('ven_situacion', $request->estado);
            }

            // Paginación
            $ventas = $query->orderBy('ven_fecha', 'desc')
                          ->paginate($request->get('per_page', 25));

            return response()->json([
                'success' => true,
                'data' => $ventas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reporte de ventas: ' . $e->getMessage()
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
    // MÉTODOS PRIVADOS AUXILIARES
    // ================================

    private function getKPIs($fechaInicio, $fechaFin)
    {
        $ventasQuery = ProVenta::whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
                              ->where('ven_situacion', 1);

        $totalVentas = $ventasQuery->count();
        $montoTotal = $ventasQuery->sum('ven_total_vendido');
        $promedioVenta = $totalVentas > 0 ? $montoTotal / $totalVentas : 0;

        $productosVendidos = ProDetalleVenta::whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])->where('ven_situacion', 1);
        })->where('det_situacion', 'ACTIVO')->sum('det_cantidad');

        $comisionesPendientes = ProPorcentajeVendedor::whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('ven_fecha', [$fechaInicio, $fechaFin]);
        })->where('porc_vend_estado', 'PENDIENTE')->sum('porc_vend_cantidad_ganancia');

        return [
            'total_ventas' => $totalVentas,
            'monto_total' => $montoTotal,
            'promedio_venta' => $promedioVenta,
            'productos_vendidos' => $productosVendidos,
            'comisiones_pendientes' => $comisionesPendientes
        ];
    }

    private function getVentasPorDia($fechaInicio, $fechaFin)
    {
        return DB::table('pro_ventas')
            ->selectRaw('DATE(ven_fecha) as fecha, COUNT(*) as total_ventas, SUM(ven_total_vendido) as monto_total')
            ->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
            ->where('ven_situacion', 1)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    private function getProductosMasVendidos($fechaInicio, $fechaFin, $limit = 10)
    {
        return DB::table('pro_detalle_ventas as dv')
            ->join('pro_ventas as v', 'dv.det_ven_id', '=', 'v.ven_id')
            ->join('pro_productos as p', 'dv.det_producto_id', '=', 'p.producto_id')
            ->selectRaw('p.producto_nombre, SUM(dv.det_cantidad) as total_vendido')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->where('dv.det_situacion', 'ACTIVO')
            ->groupBy('p.producto_id', 'p.producto_nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getVentasPorVendedor($fechaInicio, $fechaFin)
    {
        return DB::table('pro_ventas as v')
            ->join('users as u', 'v.ven_user', '=', 'u.user_id')
            ->selectRaw('CONCAT(u.user_primer_nombre, " ", u.user_primer_apellido) as vendedor, COUNT(*) as total_ventas, SUM(v.ven_total_vendido) as monto_total')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->groupBy('u.user_id', 'vendedor')
            ->orderBy('monto_total', 'desc')
            ->get();
    }

    private function getEstadisticasMetodosPago($fechaInicio, $fechaFin)
    {
        return DB::table('pro_detalle_pagos as dp')
            ->join('pro_pagos as p', 'dp.det_pago_pago_id', '=', 'p.pago_id')
            ->join('pro_ventas as v', 'p.pago_venta_id', '=', 'v.ven_id')
            ->join('pro_metodos_pago as mp', 'dp.det_pago_metodo_pago', '=', 'mp.metpago_id')
            ->selectRaw('mp.metpago_descripcion as metodo, COUNT(*) as total_transacciones, SUM(dp.det_pago_monto) as monto_total')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->where('dp.det_pago_estado', 'VALIDO')
            ->groupBy('mp.metpago_id', 'mp.metpago_descripcion')
            ->orderBy('monto_total', 'desc')
            ->get();
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
}