<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Marcas;
use App\Models\ProPago;
use App\Models\Producto;
use App\Models\ProVenta;
use App\Models\Categoria;
use App\Models\ProCliente;
use App\Models\ProMetodoPago;
use App\Models\ProDetallePago;
use App\Models\ProDetalleVenta;
use Illuminate\Support\Facades\DB;
use App\Models\ProPorcentajeVendedor;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ReportesExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    protected $filters;
    protected $tipoReporte;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
        $this->tipoReporte = $filters['tipo_reporte'] ?? 'ventas';
    }

    /**
     * @return array
     */
    public function array(): array
    {
        switch ($this->tipoReporte) {
            case 'ventas':
                return $this->getVentasData();
            case 'productos':
                return $this->getProductosData();
            case 'comisiones':
                return $this->getComisionesData();
            case 'pagos':
                return $this->getPagosData();
            case 'dashboard':
                return $this->getDashboardData();
            default:
                return $this->getVentasData();
        }
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        switch ($this->tipoReporte) {
            case 'ventas':
                return [
                    'ID Venta',
                    'Fecha',
                    'Cliente',
                    'Vendedor',
                    'Total Vendido',
                    'Estado',
                    'Productos',
                    'Método Pago'
                ];
            case 'productos':
                return [
                    'ID Producto',
                    'Código SKU',
                    'Nombre Producto',
                    'Categoría',
                    'Marca',
                    'Cantidad Vendida',
                    'Precio Promedio',
                    'Total Ingresos',
                    'Transacciones'
                ];
            case 'comisiones':
                return [
                    'ID Comisión',
                    'Vendedor',
                    'ID Venta',
                    'Fecha Venta',
                    'Total Venta',
                    'Porcentaje',
                    'Comisión',
                    'Estado',
                    'Fecha Asignación'
                ];
            case 'pagos':
                return [
                    'ID Pago',
                    'ID Venta',
                    'Cliente',
                    'Vendedor',
                    'Total Venta',
                    'Fecha Inicio',
                    'Tipo Pago',
                    'Estado',
                    'Métodos Pago',
                    'Monto Total Pagado'
                ];
            case 'dashboard':
                return [
                    'Métrica',
                    'Valor',
                    'Período'
                ];
            default:
                return [];
        }
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $titles = [
            'ventas' => 'Reporte de Ventas',
            'productos' => 'Productos Más Vendidos',
            'comisiones' => 'Reporte de Comisiones',
            'pagos' => 'Reporte de Pagos',
            'dashboard' => 'Dashboard - KPIs'
        ];

        return $titles[$this->tipoReporte] ?? 'Reporte';
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A1';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092'],
                ],
            ],
        ];
    }

    // ================================
    // MÉTODOS PARA OBTENER DATOS
    // ================================

    private function getVentasData()
    {
        // Usar consulta directa de BD para evitar problemas de relaciones
        $query = DB::table('pro_ventas as v')
            ->leftJoin('users as u', 'v.ven_user', '=', 'u.user_id')
            ->leftJoin('pro_clientes as c', 'v.ven_cliente', '=', 'c.cliente_id')
            ->select([
                'v.ven_id',
                'v.ven_fecha',
                'v.ven_total_vendido',
                'v.ven_situacion',
                DB::raw('CONCAT(COALESCE(c.cliente_nombre1, ""), " ", COALESCE(c.cliente_apellido1, "")) as cliente_nombre'),
                DB::raw('CONCAT(COALESCE(u.user_primer_nombre, ""), " ", COALESCE(u.user_primer_apellido, "")) as vendedor_nombre')
            ]);

        // Aplicar filtros
        if (!empty($this->filters['fecha_inicio'])) {
            $query->whereDate('v.ven_fecha', '>=', $this->filters['fecha_inicio']);
        }
        
        if (!empty($this->filters['fecha_fin'])) {
            $query->whereDate('v.ven_fecha', '<=', $this->filters['fecha_fin']);
        }

        if (!empty($this->filters['vendedor_id'])) {
            $query->where('v.ven_user', $this->filters['vendedor_id']);
        }

        if (!empty($this->filters['cliente_id'])) {
            $query->where('v.ven_cliente', $this->filters['cliente_id']);
        }

        if (!empty($this->filters['estado'])) {
            $query->where('v.ven_situacion', $this->filters['estado']);
        }

        $ventas = $query->orderBy('v.ven_fecha', 'desc')->get();

        return $ventas->map(function ($venta) {
            // Obtener productos de esta venta
            $productos = DB::table('pro_detalle_ventas as dv')
                ->join('pro_productos as p', 'dv.det_producto_id', '=', 'p.producto_id')
                ->where('dv.det_ven_id', $venta->ven_id)
                ->where('dv.det_situacion', 'ACTIVO')
                ->select('p.producto_nombre', 'dv.det_cantidad')
                ->get()
                ->map(function ($prod) {
                    return $prod->producto_nombre . ' (x' . $prod->det_cantidad . ')';
                })->join(', ');

            // Obtener métodos de pago
            $metodosPago = DB::table('pro_pagos as pg')
                ->join('pro_detalle_pagos as dp', 'pg.pago_id', '=', 'dp.det_pago_pago_id')
                ->join('pro_metodos_pago as mp', 'dp.det_pago_metodo_pago', '=', 'mp.metpago_id')
                ->where('pg.pago_venta_id', $venta->ven_id)
                ->where('dp.det_pago_estado', 'VALIDO')
                ->pluck('mp.metpago_descripcion')
                ->unique()
                ->join(', ');

            return [
                $venta->ven_id,
                $venta->ven_fecha ? Carbon::parse($venta->ven_fecha)->format('d/m/Y') : '',
                trim($venta->cliente_nombre),
                trim($venta->vendedor_nombre),
                number_format($venta->ven_total_vendido, 2),
                $venta->ven_situacion == 1 ? 'Activa' : 'Inactiva',
                $productos,
                $metodosPago
            ];
        })->toArray();
    }

    private function getProductosData()
    {
        $fechaInicio = $this->filters['fecha_inicio'] ?? Carbon::now()->startOfMonth();
        $fechaFin = $this->filters['fecha_fin'] ?? Carbon::now()->endOfMonth();

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
                'p.pro_codigo_sku',
                'p.producto_nombre',
                'c.categoria_nombre',
                'm.marca_descripcion as marca_nombre',
                DB::raw('SUM(dv.det_cantidad) as total_vendido'),
                DB::raw('AVG(dv.det_precio) as precio_promedio'),
                DB::raw('SUM(dv.det_cantidad * dv.det_precio) as total_ingresos'),
                DB::raw('COUNT(DISTINCT v.ven_id) as total_transacciones')
            ])
            ->groupBy('p.producto_id', 'p.pro_codigo_sku', 'p.producto_nombre', 'c.categoria_nombre', 'm.marca_descripcion');

        // Filtros adicionales
        if (!empty($this->filters['categoria_id'])) {
            $query->where('p.producto_categoria_id', $this->filters['categoria_id']);
        }

        if (!empty($this->filters['marca_id'])) {
            $query->where('p.producto_marca_id', $this->filters['marca_id']);
        }

        $productos = $query->orderBy('total_vendido', 'desc')
                          ->limit($this->filters['limit'] ?? 50)
                          ->get();

        return $productos->map(function ($producto) {
            return [
                $producto->producto_id,
                $producto->pro_codigo_sku,
                $producto->producto_nombre,
                $producto->categoria_nombre ?? 'Sin categoría',
                $producto->marca_nombre ?? 'Sin marca',
                $producto->total_vendido,
                number_format($producto->precio_promedio, 2),
                number_format($producto->total_ingresos, 2),
                $producto->total_transacciones
            ];
        })->toArray();
    }

    private function getComisionesData()
{
    $query = DB::table('pro_porcentaje_vendedor as pv')
        ->leftJoin('users as u', 'pv.porc_vend_user_id', '=', 'u.user_id')
        ->leftJoin('pro_ventas as v', 'pv.porc_vend_ven_id', '=', 'v.ven_id')  // Corregido aquí
        ->select([
            'pv.porc_vend_id',
            'pv.porc_vend_porcentaje',
            'pv.porc_vend_cantidad_ganancia',
            'pv.porc_vend_estado',
            'pv.porc_vend_fecha_asignacion',
            'v.ven_id',
            'v.ven_fecha',
            'v.ven_total_vendido',
            DB::raw('CONCAT(COALESCE(u.user_primer_nombre, ""), " ", COALESCE(u.user_primer_apellido, "")) as vendedor_nombre')
        ]);

    // Aplicar filtros por fecha
    if (!empty($this->filters['fecha_inicio']) || !empty($this->filters['fecha_fin'])) {
        if (!empty($this->filters['fecha_inicio'])) {
            $query->whereDate('v.ven_fecha', '>=', $this->filters['fecha_inicio']);
        }
        if (!empty($this->filters['fecha_fin'])) {
            $query->whereDate('v.ven_fecha', '<=', $this->filters['fecha_fin']);
        }
    }

    if (!empty($this->filters['vendedor_id'])) {
        $query->where('pv.porc_vend_user_id', $this->filters['vendedor_id']);
    }

    if (!empty($this->filters['estado'])) {
        $query->where('pv.porc_vend_estado', $this->filters['estado']);
    }

    $comisiones = $query->orderBy('pv.porc_vend_fecha_asignacion', 'desc')->get();

    return $comisiones->map(function ($comision) {
        return [
            $comision->porc_vend_id,
            trim($comision->vendedor_nombre),
            $comision->ven_id ?? '',
            $comision->ven_fecha ? Carbon::parse($comision->ven_fecha)->format('d/m/Y') : '',
            $comision->ven_total_vendido ? number_format($comision->ven_total_vendido, 2) : '',
            $comision->porc_vend_porcentaje . '%',
            number_format($comision->porc_vend_cantidad_ganancia, 2),
            $comision->porc_vend_estado,
            $comision->porc_vend_fecha_asignacion ? Carbon::parse($comision->porc_vend_fecha_asignacion)->format('d/m/Y H:i') : ''
        ];
    })->toArray();
}

    private function getPagosData()
    {
        $query = DB::table('pro_pagos as pg')
            ->leftJoin('pro_ventas as v', 'pg.pago_venta_id', '=', 'v.ven_id')
            ->leftJoin('users as u', 'v.ven_user', '=', 'u.user_id')
            ->leftJoin('pro_clientes as c', 'v.ven_cliente', '=', 'c.cliente_id')
            ->select([
                'pg.pago_id',
                'pg.pago_fecha_inicio',
                'pg.pago_tipo_pago',
                'pg.pago_estado',
                'v.ven_id',
                'v.ven_total_vendido',
                DB::raw('CONCAT(COALESCE(c.cliente_nombre1, ""), " ", COALESCE(c.cliente_apellido1, "")) as cliente_nombre'),
                DB::raw('CONCAT(COALESCE(u.user_primer_nombre, ""), " ", COALESCE(u.user_primer_apellido, "")) as vendedor_nombre')
            ]);

        // Filtros
        if (!empty($this->filters['fecha_inicio'])) {
            $query->whereDate('pg.pago_fecha_inicio', '>=', $this->filters['fecha_inicio']);
        }

        if (!empty($this->filters['fecha_fin'])) {
            $query->whereDate('pg.pago_fecha_inicio', '<=', $this->filters['fecha_fin']);
        }

        if (!empty($this->filters['estado'])) {
            $query->where('pg.pago_estado', $this->filters['estado']);
        }

        if (!empty($this->filters['tipo_pago'])) {
            $query->where('pg.pago_tipo_pago', $this->filters['tipo_pago']);
        }

        $pagos = $query->orderBy('pg.pago_fecha_inicio', 'desc')->get();

        return $pagos->map(function ($pago) {
            // Obtener métodos de pago y monto total
            $detallesPago = DB::table('pro_detalle_pagos as dp')
                ->leftJoin('pro_metodos_pago as mp', 'dp.det_pago_metodo_pago', '=', 'mp.metpago_id')
                ->where('dp.det_pago_pago_id', $pago->pago_id)
                ->where('dp.det_pago_estado', 'VALIDO')
                ->select('mp.metpago_descripcion', 'dp.det_pago_monto')
                ->get();

            $metodosPago = $detallesPago->pluck('metpago_descripcion')->filter()->unique()->join(', ');
            $montoTotalPagado = $detallesPago->sum('det_pago_monto');

            return [
                $pago->pago_id,
                $pago->ven_id ?? '',
                trim($pago->cliente_nombre),
                trim($pago->vendedor_nombre),
                $pago->ven_total_vendido ? number_format($pago->ven_total_vendido, 2) : '',
                $pago->pago_fecha_inicio ? Carbon::parse($pago->pago_fecha_inicio)->format('d/m/Y') : '',
                $pago->pago_tipo_pago,
                $pago->pago_estado,
                $metodosPago,
                number_format($montoTotalPagado, 2)
            ];
        })->toArray();
    }

    private function getDashboardData()
    {
        $fechaInicio = $this->filters['fecha_inicio'] ?? Carbon::now()->startOfMonth();
        $fechaFin = $this->filters['fecha_fin'] ?? Carbon::now()->endOfMonth();
        $periodo = Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y');

        // KPIs principales usando consultas directas
        $ventasStats = DB::table('pro_ventas')
            ->whereBetween('ven_fecha', [$fechaInicio, $fechaFin])
            ->where('ven_situacion', 1)
            ->selectRaw('COUNT(*) as total_ventas, SUM(ven_total_vendido) as monto_total')
            ->first();

        $totalVentas = $ventasStats->total_ventas ?? 0;
        $montoTotal = $ventasStats->monto_total ?? 0;
        $promedioVenta = $totalVentas > 0 ? $montoTotal / $totalVentas : 0;

        $productosVendidos = DB::table('pro_detalle_ventas as dv')
            ->join('pro_ventas as v', 'dv.det_ven_id', '=', 'v.ven_id')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('v.ven_situacion', 1)
            ->where('dv.det_situacion', 'ACTIVO')
            ->sum('dv.det_cantidad');

        $comisionesPendientes = DB::table('pro_porcentaje_vendedor as pv')
            ->join('pro_ventas as v', 'pv.porc_vend_venta_id', '=', 'v.ven_id')
            ->whereBetween('v.ven_fecha', [$fechaInicio, $fechaFin])
            ->where('pv.porc_vend_estado', 'PENDIENTE')
            ->sum('pv.porc_vend_cantidad_ganancia');

        return [
            ['Total de Ventas', $totalVentas, $periodo],
            ['Monto Total Vendido', number_format($montoTotal, 2), $periodo],
            ['Promedio por Venta', number_format($promedioVenta, 2), $periodo],
            ['Productos Vendidos', $productosVendidos, $periodo],
            ['Comisiones Pendientes', number_format($comisionesPendientes ?? 0, 2), $periodo]
        ];
    }
}