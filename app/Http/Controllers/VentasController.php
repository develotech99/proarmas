<?php

namespace App\Http\Controllers;

use App\Models\Ventas;
use Illuminate\Http\Request;
use App\Models\MetodoPago;
use App\Models\Clientes;

use App\Models\Producto;
use App\Models\SerieProducto;
use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\StockActual;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class VentasController extends Controller
{

    // Constante para el monto de tenencia
private const MONTO_TENENCIA = 60.00;

    public function index()
    {
        // // Datos necesarios para los selects y filtros
        $categorias = DB::table('pro_categorias')->where('categoria_situacion', 1)->get();
        $clientes = DB::table('users')->where('user_rol', 2)->get();
        $metodopago = MetodoPago::orderBy('metpago_descripcion')->paginate(15);

        return view('ventas.index', compact(
            'categorias',
            'clientes',
            'metodopago'
        ));
    }


    public function buscarClientes(Request $request)
    {
        // echo json_encode($_GET);
        // exit;
        $nit = trim($request->query('nit', ''));
        $dpi = trim($request->query('dpi', ''));

        $clientes = DB::table('pro_clientes')
            ->where('cliente_situacion', 1)
            ->when($nit, function ($q) use ($nit) {
                $q->where('cliente_nit', $nit);
            })
            ->when($dpi, function ($q) use ($dpi) {
                $q->where('cliente_dpi', $dpi);
            })
            ->select(
                'cliente_id',
                'cliente_nombre1',
                'cliente_nombre2',
                'cliente_apellido1',
                'cliente_apellido2',
                'cliente_nit',
                'cliente_dpi',
                'cliente_tipo',          
                'cliente_nom_empresa'  
            )
            ->orderBy('cliente_nombre1')
            ->get();

        return response()->json($clientes);
    }


    public function getSubcategorias($categoria_id)
    {
        $subcategorias = DB::table('pro_productos as p')
            ->join('pro_subcategorias as s', 'p.producto_subcategoria_id', '=', 's.subcategoria_id')
            ->where('p.producto_categoria_id', $categoria_id)
            ->where('p.producto_situacion', 1)
            ->select('s.subcategoria_id', 's.subcategoria_nombre')
            ->distinct()
            ->orderBy('s.subcategoria_nombre')
            ->get();

        return response()->json($subcategorias);
    }


    public function getMarcas($subcategoria_id)
    {
        $marcas = DB::table('pro_productos as p')
            ->join('pro_marcas as m', 'p.producto_marca_id', '=', 'm.marca_id')
            ->where('p.producto_subcategoria_id', $subcategoria_id)
            ->where('p.producto_situacion', 1)
            ->select('m.marca_id', 'm.marca_descripcion')
            ->distinct()
            ->get();

        return response()->json($marcas);
    }

    public function getModelos($marca_id)
    {
        $modelos = DB::table('pro_productos as p')
            ->join('pro_modelo as m', 'p.producto_modelo_id', '=', 'm.modelo_id')
            ->where('p.producto_marca_id', $marca_id)  // ← Corregido
            ->where('p.producto_situacion', 1)
            ->whereNotNull('p.producto_modelo_id')     // ← Solo productos con modelo
            ->select('m.modelo_id', 'm.modelo_descripcion') // ← Verifica este campo
            ->distinct()
            ->orderBy('m.modelo_descripcion')
            ->get();

        return response()->json($modelos);
    }

    public function getCalibres($modelo_id)
    {
        $calibres = DB::table('pro_productos as p')
            ->join('pro_calibres as c', 'p.producto_calibre_id', '=', 'c.calibre_id')
            ->where('p.producto_modelo_id', $modelo_id)
            ->where('p.producto_situacion', 1)
            ->whereNotNull('p.producto_calibre_id')  // Solo productos que tengan calibre
            ->select('c.calibre_id', 'c.calibre_nombre')
            ->distinct()
            ->orderBy('c.calibre_nombre')
            ->get();

        return response()->json($calibres);
    }

    public function buscarProductos(Request $request)
    {
        $categoria_id = trim($request->query('categoria_id', ''));
        $subcategoria_id = trim($request->query('subcategoria_id', ''));
        $marca_id = trim($request->query('marca_id', ''));
        $modelo_id = trim($request->query('modelo_id', ''));
        $calibre_id = trim($request->query('calibre_id', ''));
        $busqueda = trim($request->query('busqueda', ''));

        $productos = DB::table('pro_productos')
            ->leftJoin('pro_precios', 'producto_id', '=', 'precio_producto_id')
            ->Join('pro_categorias', 'producto_categoria_id', '=', 'categoria_id')
            ->Join('pro_subcategorias', 'producto_subcategoria_id', '=', 'subcategoria_id')
            ->leftJoin('pro_marcas', 'producto_marca_id', '=', 'marca_id')
            ->leftJoin('pro_modelo', 'producto_modelo_id', '=', 'modelo_id')
            ->leftJoin('pro_calibres', 'producto_calibre_id', '=', 'calibre_id')
            ->leftJoin('pro_paises', 'producto_madein', '=', 'pais_id')
            ->leftJoin('pro_stock_actual', 'stock_producto_id', '=', 'producto_id')
            ->leftJoin('pro_productos_fotos', function ($join) {
                $join->on('producto_id', '=', 'foto_producto_id')
                    ->where('foto_principal', 1);
            })
            ->where('producto_situacion', 1)
            ->when($categoria_id, fn($q) => $q->where('categoria_id', $categoria_id))
            ->when($subcategoria_id, fn($q) => $q->where('subcategoria_id', $subcategoria_id))
            ->when($marca_id, fn($q) => $q->where('marca_id', $marca_id))
            ->when($modelo_id, fn($q) => $q->where('modelo_id', $modelo_id))
            ->when($calibre_id, fn($q) => $q->where('calibre_id', $calibre_id))
            ->when($busqueda, function ($q) use ($busqueda) {
                $q->where(function ($query) use ($busqueda) {
                    $query->where('producto_nombre', 'like', "%{$busqueda}%")
                        ->orWhere('marca_descripcion', 'like', "%{$busqueda}%")
                        ->orWhere('modelo_descripcion', 'like', "%{$busqueda}%")
                        ->orWhere('calibre_nombre', 'like', "%{$busqueda}%");
                });
            })
            ->select(
                'producto_id',
                'producto_nombre',
                'producto_descripcion',
                'producto_categoria_id',
                'categoria_nombre',
                'producto_subcategoria_id',
                'subcategoria_nombre',
                'producto_marca_id',
                'marca_descripcion',
                'producto_modelo_id',
                'modelo_descripcion',
                'producto_calibre_id',
                'calibre_nombre',
                'pais_descripcion',
                'producto_situacion',
                'producto_requiere_serie',
                'precio_venta',
                'precio_venta_empresa',
                'foto_url',
                'stock_cantidad_total',
                'stock_cantidad_reservada',
                'producto_requiere_stock'
            )
            ->orderBy('producto_nombre')
            ->get();

        // Series + LOTES (igual que series, pero para pro_lotes)
        $productos = $productos->map(function ($producto) {
            $productoArray = (array) $producto;


            // 👇 Calcular stock real
            $stockTotal = $producto->stock_cantidad_total ?? 0;
            $stockReservado = $producto->stock_cantidad_reservada ?? 0;

            // 👇 IMPORTANTE: Sobrescribir stock_cantidad_total con el stock real disponible
            $productoArray['stock_cantidad_total'] = max(0, $stockTotal - $stockReservado);



            // SERIES
            if ($producto->producto_requiere_serie == 1) {
                $seriesDisponibles = DB::table('pro_series_productos')
                    ->where('serie_producto_id', $producto->producto_id)
                    ->where('serie_estado', 'disponible')
                    ->select('serie_producto_id', 'serie_numero_serie', 'serie_situacion')
                    ->orderBy('serie_numero_serie')
                    ->get();

                $productoArray['series_disponibles'] = $seriesDisponibles;
                $productoArray['cantidad_series'] = $seriesDisponibles->count();
            } else {
                $productoArray['series_disponibles'] = [];
                $productoArray['cantidad_series'] = 0;
            }

            // LOTES (nuevo)
            $lotes = DB::table('pro_lotes')
                ->where('lote_producto_id', $producto->producto_id)
                ->select(
                    'lote_id',
                    'lote_producto_id',
                    'lote_codigo',
                    'lote_cantidad_total'
                    // agrega aquí más columnas si las tienes (lote_codigo, fecha_vencimiento, etc.)
                )
                ->orderBy('lote_id')
                ->get();

            $productoArray['lotes'] = $lotes;                           // listado de lotes
            $productoArray['cantidad_lotes'] = $lotes->count();                  // cuántos lotes
            $productoArray['lotes_cantidad_total'] = $lotes->sum('lote_cantidad_total'); // suma de cantidades

            return (object) $productoArray;
        });

        return response()->json($productos);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarCliente(Request $request)
    {
        try {
            $data = $request->validate([
                'cliente_nombre1' => ['required', 'string', 'max:50'],
                'cliente_nombre2' => ['nullable', 'string', 'max:50'],
                'cliente_apellido1' => ['required', 'string', 'max:50'],
                'cliente_apellido2' => ['nullable', 'string', 'max:50'],
                'cliente_dpi' => ['nullable', 'string', 'max:20'],
                'cliente_nit' => ['nullable', 'string', 'max:20'],
                'cliente_direccion' => ['nullable', 'string', 'max:255'],
                'cliente_telefono' => ['nullable', 'string', 'max:30'],
                'cliente_correo' => ['nullable', 'email', 'max:150'],
                'cliente_tipo' => ['required', 'integer', 'in:1,2,3'],
                'cliente_user_id' => ['nullable', 'integer'],
                'cliente_nom_empresa' => ['nullable', 'string', 'max:255'],
                'cliente_nom_vendedor' => ['nullable', 'string', 'max:255'],
                'cliente_cel_vendedor' => ['nullable', 'string', 'max:30'],
                'cliente_ubicacion' => ['nullable', 'string', 'max:255'],
                'cliente_pdf_licencia' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], //Validar PDF
            ]);
    
            // Asegurar que cliente_situacion tenga valor por defecto
            if (!isset($data['cliente_situacion'])) {
                $data['cliente_situacion'] = 1;
            }
    
            // Manejar subida de PDF si existe
            if ($request->hasFile('cliente_pdf_licencia')) {
                $file = $request->file('cliente_pdf_licencia');
                
                // Crear nombre único para el archivo
                $fileName = 'licencia_' . time() . '_' . uniqid() . '.pdf';
                
                // Guardar en storage/app/public/clientes/licencias/
                $path = $file->storeAs('clientes/licencias', $fileName, 'public');
                
                // Agregar la ruta al array de datos
                $data['cliente_pdf_licencia'] = $path;
            }
    
            $cliente = Clientes::create($data);
    
            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente guardado correctamente'
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            \Log::error('Error al guardar cliente:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'data' => $request->except('cliente_pdf_licencia') // No loguear el archivo
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }





    ///hecho por morales batz 

public function obtenerVentasPendientes(Request $request): JsonResponse
{
    try {
       
        $clienteId = $request->input('cliente_id');
        $vendedorId = $request->input('vendedor_id');
        
        $query = "
            SELECT 
                v.ven_id,
                v.ven_user,
                d.det_producto_id,
                d.det_ven_id,
                v.ven_fecha,
                
                TRIM(
                    CONCAT_WS(' ',
                        TRIM(c.cliente_nombre1),
                        TRIM(c.cliente_nombre2),
                        TRIM(c.cliente_apellido1),
                        TRIM(c.cliente_apellido2)
                    )
                ) AS cliente,
                CASE 
                    WHEN c.cliente_nom_empresa IS NULL OR c.cliente_nom_empresa = ''
                        THEN 'Cliente Individual'
                    ELSE c.cliente_nom_empresa
                END AS empresa,
                TRIM(
                    CONCAT_WS(' ',
                        TRIM(u.user_primer_nombre),
                        TRIM(u.user_segundo_nombre),
                        TRIM(u.user_primer_apellido),
                        TRIM(u.user_segundo_apellido)
                    )
                ) AS vendedor,
                GROUP_CONCAT(DISTINCT p.producto_nombre SEPARATOR ', ') AS productos,
                            
                GROUP_CONCAT(DISTINCT mov.mov_serie_id ORDER BY mov.mov_serie_id SEPARATOR ',') AS series_ids,
                GROUP_CONCAT(DISTINCT mov.mov_lote_id ORDER BY mov.mov_lote_id SEPARATOR ',') AS lotes_ids,
                
                GROUP_CONCAT(
                    DISTINCT CONCAT(mov.mov_lote_id, ' (', mov.mov_cantidad, ')')
                    ORDER BY mov.mov_lote_id SEPARATOR ', '
                ) AS lotes_display,
                
                GROUP_CONCAT(
                    DISTINCT CONCAT(mov.mov_serie_id, ' (', mov.mov_cantidad, ')')
                    ORDER BY mov.mov_serie_id SEPARATOR ', '
                ) AS series_display,
                
                v.ven_total_vendido,
                v.ven_situacion
            FROM pro_detalle_ventas d
            INNER JOIN pro_ventas v ON v.ven_id = d.det_ven_id
            INNER JOIN users u ON u.user_id = v.ven_user
            INNER JOIN pro_clientes c ON c.cliente_id = v.ven_cliente
            INNER JOIN pro_productos p ON d.det_producto_id = p.producto_id
            LEFT JOIN pro_movimientos mov ON mov.mov_producto_id = d.det_producto_id
                AND mov.mov_situacion = 3
                AND mov.mov_documento_referencia = CONCAT('VENTA-', v.ven_id)
            WHERE d.det_situacion = 'PENDIENTE'
                AND v.ven_situacion = 'PENDIENTE'
        ";
        
        // ✅ Agregar filtros dinámicamente
        $bindings = [];
        
        if ($clienteId) {
            $query .= " AND v.ven_cliente = ?";
            $bindings[] = $clienteId;
        }
        
        if ($vendedorId) {
            $query .= " AND v.ven_user = ?";
            $bindings[] = $vendedorId;
        }
        
        $query .= "
            GROUP BY 
                v.ven_id,
                v.ven_fecha,
                v.ven_user,
                d.det_producto_id,
                d.det_ven_id,
                v.ven_total_vendido,
                v.ven_situacion,
                c.cliente_nombre1,
                c.cliente_nombre2,
                c.cliente_apellido1,
                c.cliente_apellido2,
                c.cliente_nom_empresa,
                u.user_primer_nombre,
                u.user_segundo_nombre,
                u.user_primer_apellido,
                u.user_segundo_apellido
            ORDER BY v.ven_fecha DESC
        ";
        
        $ventas = DB::select($query, $bindings);

        $ventasProcesadas = array_map(function($venta) {
            return [
                'ven_id' => $venta->ven_id,
                'ven_user' => $venta->ven_user,
                'det_producto_id' => $venta->det_producto_id,
                'det_ven_id' => $venta->det_ven_id,
                'ven_fecha' => $venta->ven_fecha,
                'cliente' => $venta->cliente,
                'empresa' => $venta->empresa,
                'vendedor' => $venta->vendedor,
                'productos' => $venta->productos,
                'lotes_ids' => $venta->lotes_ids ?? '',
                'series_ids' => $venta->series_ids ?? '',
                'lotes_display' => $venta->lotes_display ?? '',
                'series_display' => $venta->series_display ?? '',
                'ven_total_vendido' => $venta->ven_total_vendido,
                'ven_situacion' => $venta->ven_situacion
            ];
        }, $ventas);

        return response()->json([
            'success' => true,
            'total' => count($ventasProcesadas),
            'data' => $ventasProcesadas,
            'filtros_aplicados' => [
                'cliente_id' => $clienteId,
                'vendedor_id' => $vendedorId
            ]
        ]);

    } catch (QueryException $e) {
        Log::error('Pendientes SQL', [
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
            'info' => $e->errorInfo
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error SQL en pendientes: ' . $e->getMessage(),
        ], 500);

    } catch (\Throwable $e) {
        Log::error('Pendientes error', ['err' => $e]);
        return response()->json([
            'success' => false,
            'message' => 'Error en pendientes: ' . $e->getMessage(),
        ], 500);
    }
}

public function autorizarVenta(Request $request): JsonResponse
{


    $venId = (int) $request->input('ven_id');
    $venUser = (int) $request->input('ven_user');
    $detProductoId = (int) $request->input('det_producto_id');
    $productoId = (int) $request->input('producto_id', $detProductoId);

    $seriesIds = collect($request->input('series_ids', []))
        ->map(fn($v) => (int) $v)->filter()->unique()->values();

    $lotesIds = collect($request->input('lotes_ids', []))
        ->map(fn($v) => (int) $v)->filter()->unique()->values();

    $qtyTotal = 0;
    $detallesProcesados = [];

    try {
        DB::transaction(function () use (
            $venId, $venUser, $productoId, $seriesIds, $lotesIds, &$qtyTotal, &$detallesProcesados
        ) {
            // Paso 1: Activar venta
            $affected = DB::table('pro_ventas')
                ->where('ven_id', $venId)
                ->where('ven_user', $venUser)
                ->update(['ven_situacion' => 'ACTIVA']);

            if ($affected === 0) {
                throw new \RuntimeException('No se pudo activar la venta (pro_ventas).');
            }

            // Paso 2: Activar detalles
            DB::table('pro_detalle_ventas')
                ->where('det_ven_id', $venId)
                ->update(['det_situacion' => 'ACTIVO']);

            $ref = 'VENTA-' . $venId;

            // Paso 3: SERIES
            if ($seriesIds->isNotEmpty()) {
                $seriesCantidades = DB::table('pro_movimientos')
                    ->select('mov_serie_id', DB::raw('SUM(mov_cantidad) as qty'))
                    ->where('mov_documento_referencia', $ref)
                    ->where('mov_situacion', 3)
                    ->whereIn('mov_serie_id', $seriesIds)
                    ->groupBy('mov_serie_id')
                    ->pluck('qty', 'mov_serie_id');

                if ($seriesCantidades->isNotEmpty()) {
                    DB::table('pro_series_productos')
                        ->whereIn('serie_id', $seriesCantidades->keys())
                        ->update(['serie_estado' => 'vendido', 'serie_situacion' => 1]);

                    DB::table('pro_movimientos')
                        ->whereIn('mov_serie_id', $seriesCantidades->keys())
                        ->where('mov_documento_referencia', $ref)
                        ->where('mov_situacion', 3)
                        ->update(['mov_situacion' => 1]);

                    $sumaSeries = $seriesCantidades->sum();
                    $qtyTotal += $sumaSeries;

                    $detallesProcesados[] = 'Series procesadas: ' . $seriesCantidades
                        ->map(fn($qty, $id) => "$id ($qty)")
                        ->implode(', ');
                }
            }

            // Paso 4: LOTES
            if ($lotesIds->isNotEmpty()) {
                $lotesCantidades = DB::table('pro_movimientos')
                    ->select('mov_lote_id', DB::raw('SUM(mov_cantidad) as qty'))
                    ->where('mov_documento_referencia', $ref)
                    ->where('mov_situacion', 3)
                    ->whereIn('mov_lote_id', $lotesIds)
                    ->groupBy('mov_lote_id')
                    ->pluck('qty', 'mov_lote_id');

                if ($lotesCantidades->isNotEmpty()) {
                    DB::table('pro_movimientos')
                        ->whereIn('mov_lote_id', $lotesCantidades->keys())
                        ->where('mov_documento_referencia', $ref)
                        ->where('mov_situacion', 3)
                        ->update(['mov_situacion' => 1]);

                    $sumaLotes = $lotesCantidades->sum();
                    $qtyTotal += $sumaLotes;

                    $detallesProcesados[] = 'Lotes procesados: ' . $lotesCantidades
                        ->map(fn($qty, $id) => "$id ($qty)")
                        ->implode(', ');
                }
            }

            // Paso 5: Actualizar stock
            if ($qtyTotal > 0) {
                DB::table('pro_stock_actual')
                    ->where('stock_producto_id', $productoId)
                    ->decrement('stock_cantidad_reservada', $qtyTotal);

                DB::table('pro_stock_actual')
                    ->where('stock_producto_id', $productoId)
                    ->decrement('stock_cantidad_disponible', $qtyTotal);

                DB::table('pro_stock_actual')
                    ->where('stock_producto_id', $productoId)
                    ->decrement('stock_cantidad_total', $qtyTotal);
            }
        }, 3);

        return response()->json([
            'codigo' => 1,
            'mensaje' => 'Venta autorizada exitosamente',
            'meta' => [
                'qty_total' => $qtyTotal,
                'detalles' => $detallesProcesados,
                'ven_id' => $venId
            ],
        ]);
    } catch (\Throwable $e) {
        report($e);
        return response()->json([
            'codigo' => 0,
            'mensaje' => 'No se pudo autorizar la venta.',
            'detalle' => $e->getMessage(),
        ], 500);
    }
}
public function actualizarLicencias(Request $request): JsonResponse
{
    $venId = (int) $request->input('ven_id');
    $licencias = $request->input('licencias', []); // Array de objetos con licencias por serie

    if (empty($licencias)) {
        return response()->json([
            'codigo' => 0,
            'mensaje' => 'No se recibieron licencias para actualizar'
        ], 400);
    }

    try {
        $procesadas = 0;
        
        DB::transaction(function () use ($venId, $licencias, &$procesadas) {
            $ref = 'VENTA-' . $venId;
            
            foreach ($licencias as $licencia) {
                $serieId = (int) $licencia['serie_id'];
                $licAnterior = $licencia['licencia_anterior'];
                $licNueva = $licencia['licencia_nueva'];
                
                // Actualizar movimientos de esta serie específica
                $affected = DB::table('pro_movimientos')
                    ->where('mov_serie_id', $serieId)
                    ->where('mov_documento_referencia', $ref)
                    ->update([
                        'mov_licencia_anterior' => $licAnterior,
                        'mov_licencia_nueva' => $licNueva
                    ]);
                
                if ($affected > 0) {
                    $procesadas++;
                }
                
            
               
            }
        });

        return response()->json([
            'codigo' => 1,
            'mensaje' => "Licencias actualizadas correctamente ({$procesadas} serie(s))",
            'meta' => [
                'series_procesadas' => $procesadas,
                'total_enviadas' => count($licencias)
            ]
        ]);
        
    } catch (\Throwable $e) {
        report($e);
        return response()->json([
            'codigo' => 0,
            'mensaje' => 'Error al actualizar licencias',
            'detalle' => $e->getMessage()
        ], 500);
    }
}


    ///////// termino morales batz

public function cancelarVenta(Request $request): JsonResponse
{
    $venId = (int) $request->input('ven_id');
    $motivoCancelacion = $request->input('motivo', 'Cancelación de venta');

    try {
        DB::transaction(function () use ($venId, $motivoCancelacion) {
            // Verificar que la venta existe y está PENDIENTE
            $venta = DB::table('pro_ventas')
                ->where('ven_id', $venId)
                ->first();

            if (!$venta) {
                throw new \RuntimeException('Venta no encontrada.');
            }

            if ($venta->ven_situacion !== 'PENDIENTE') {
                throw new \RuntimeException('Solo se pueden cancelar ventas en estado PENDIENTE.');
            }

            $ref = 'VENTA-' . $venId;

            // 1. REVERTIR MOVIMIENTOS Y STOCK SEGÚN TIPO

            // 1.1 Revertir SERIES
            $movimientosSeries = DB::table('pro_movimientos')
                ->where('mov_documento_referencia', $ref)
                ->where('mov_situacion', 3)
                ->whereNotNull('mov_serie_id')
                ->get();

            if ($movimientosSeries->isNotEmpty()) {
                $seriesIds = $movimientosSeries->pluck('mov_serie_id')->unique();
                
                // Revertir estado de series a disponible
                DB::table('pro_series_productos')
                    ->whereIn('serie_id', $seriesIds)
                    ->update([
                        'serie_estado' => 'disponible',
                        'serie_situacion' => 1, 
                        'serie_tiene_tenencia' => false,  
                        'serie_monto_tenencia' => null 
                    ]);

                // Cancelar movimientos de series
                DB::table('pro_movimientos')
                    ->whereIn('mov_serie_id', $seriesIds)
                    ->where('mov_documento_referencia', $ref)
                    ->where('mov_situacion', 3)
                    ->update(['mov_situacion' => 0]);

                // Revertir stock reservado
                foreach ($movimientosSeries->groupBy('mov_producto_id') as $productoId => $movs) {
                    $cantidad = $movs->sum('mov_cantidad');
                    DB::table('pro_stock_actual')
                        ->where('stock_producto_id', $productoId)
                        ->decrement('stock_cantidad_reservada', $cantidad);
                }
            }

            // 1.2 Revertir LOTES
            $movimientosLotes = DB::table('pro_movimientos')
                ->where('mov_documento_referencia', $ref)
                ->where('mov_situacion', 3)
                ->whereNotNull('mov_lote_id')
                ->get();

            if ($movimientosLotes->isNotEmpty()) {
                foreach ($movimientosLotes as $mov) {
                    // Devolver cantidad al lote (PRIMERO TOTAL, LUEGO DISPONIBLE)
                    DB::table('pro_lotes')
                        ->where('lote_id', $mov->mov_lote_id)
                        ->increment('lote_cantidad_total', $mov->mov_cantidad);

                    DB::table('pro_lotes')
                        ->where('lote_id', $mov->mov_lote_id)
                        ->increment('lote_cantidad_disponible', $mov->mov_cantidad);

                    // Reactivar lote si estaba agotado
                    $lote = DB::table('pro_lotes')->where('lote_id', $mov->mov_lote_id)->first();
                    if ($lote && $lote->lote_cantidad_disponible > 0 && $lote->lote_situacion == 0) {
                        DB::table('pro_lotes')
                            ->where('lote_id', $mov->mov_lote_id)
                            ->update(['lote_situacion' => 1]);
                    }
                }

                // Cancelar movimientos de lotes
                $lotesIds = $movimientosLotes->pluck('mov_lote_id')->unique();
                DB::table('pro_movimientos')
                    ->whereIn('mov_lote_id', $lotesIds)
                    ->where('mov_documento_referencia', $ref)
                    ->where('mov_situacion', 3)
                    ->update(['mov_situacion' => 0]);

                // Revertir stock reservado
                foreach ($movimientosLotes->groupBy('mov_producto_id') as $productoId => $movs) {
                    $cantidad = $movs->sum('mov_cantidad');
                    DB::table('pro_stock_actual')
                        ->where('stock_producto_id', $productoId)
                        ->decrement('stock_cantidad_reservada', $cantidad);
                }
            }

            // 1.3 Cancelar movimientos sin serie ni lote (situación 1 - ya autorizados previamente o stock general)
            $movimientosGenerales = DB::table('pro_movimientos')
                ->where('mov_documento_referencia', $ref)
                ->where('mov_situacion', 1)
                ->whereNull('mov_serie_id')
                ->whereNull('mov_lote_id')
                ->get();

            if ($movimientosGenerales->isNotEmpty()) {
                foreach ($movimientosGenerales->groupBy('mov_producto_id') as $productoId => $movs) {
                    $cantidad = $movs->sum('mov_cantidad');
                    
                    // Estos movimientos ya estaban confirmados, no tienen stock reservado que revertir
                    // Solo se marcan como ANULADAs
                }

                DB::table('pro_movimientos')
                    ->where('mov_documento_referencia', $ref)
                    ->where('mov_situacion', 1)
                    ->whereNull('mov_serie_id')
                    ->whereNull('mov_lote_id')
                    ->update(['mov_situacion' => 0]);
            }

            // 2. CANCELAR DETALLES DE VENTA
            DB::table('pro_detalle_ventas')
                ->where('det_ven_id', $venId)
                ->update(['det_situacion' => 'ANULADA']);

            // 3. CANCELAR VENTA
            DB::table('pro_ventas')
                ->where('ven_id', $venId)
                ->update([
                    'ven_situacion' => 'ANULADA',
                    'ven_observaciones' => $motivoCancelacion
                ]);

            // 4. CANCELAR PAGOS Y CUOTAS
            $pago = DB::table('pro_pagos')
                ->where('pago_venta_id', $venId)
                ->first();

            if ($pago) {
                // Cancelar pago principal
                DB::table('pro_pagos')
                    ->where('pago_id', $pago->pago_id)
                    ->update([
                        'pago_estado' => 'ANULADA',
                        'updated_at' => now()
                    ]);

                // Cancelar detalles de pago
                DB::table('pro_detalle_pagos')
                    ->where('det_pago_pago_id', $pago->pago_id)
                    ->update([
                        'det_pago_estado' => 'ANULADO',
                        'updated_at' => now()
                    ]);

                // Cancelar cuotas si existen
                DB::table('pro_cuotas')
                    ->where('cuota_control_id', $pago->pago_id)
                    ->update([
                        'cuota_estado' => 'ANULADA',
                        'updated_at' => now()
                    ]);
            }

            // 5. CANCELAR COMISIÓN DEL VENDEDOR
            DB::table('pro_porcentaje_vendedor')
                ->where('porc_vend_ven_id', $venId)
                ->update([
                    'porc_vend_estado' => 'CANCELADO',
                    'porc_vend_situacion' => 'INACTIVO'
                ]);

            // 6. ACTUALIZAR HISTORIAL DE CAJA
            DB::table('cja_historial')
                ->where('cja_id_venta', $venId)
                ->update([
                    'cja_situacion' => 'ANULADA',
                    'cja_observaciones' => $motivoCancelacion
                ]);

        }, 3);

        return response()->json([
            'success' => true,
            'message' => 'Venta cancelada exitosamente',
            'venta_id' => $venId
        ]);

    } catch (\Throwable $e) {
        report($e);
        return response()->json([
            'success' => false,
            'message' => 'No se pudo cancelar la venta.',
            'detalle' => $e->getMessage()
        ], 500);
    }
}


    public function procesarVenta(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cliente_id' => 'required|exists:pro_clientes,cliente_id',
                'fecha_venta' => 'required|date',
                'subtotal' => 'required|numeric|min:0',
                'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
                'descuento_monto' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'metodo_pago' => 'required|in:1,2,3,4,5,6',
                'productos' => 'required|array|min:1',
                'productos.*.producto_id' => 'required|exists:pro_productos,producto_id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0',
                'productos.*.subtotal_producto' => 'required|numeric|min:0',
                'productos.*.requiere_serie' => 'required|in:0,1',
                'productos.*.producto_requiere_stock' => 'required|in:0,1',
                'productos.*.series_seleccionadas' => 'nullable|array',
                'productos.*.series_con_tenencia' => 'nullable|array',
                'productos.*.tiene_lotes' => 'required|boolean',
                'productos.*.lotes_seleccionados' => 'nullable|array',
                'productos.*.lotes_seleccionados.*.lote_id' => 'nullable|exists:pro_lotes,lote_id',
                'productos.*.lotes_seleccionados.*.cantidad' => 'nullable|integer|min:1',
                'pago' => 'required|array',
            ]);

            DB::beginTransaction();

            // 1. CREAR LA VENTA EN pro_ventas
            $ventaId = DB::table('pro_ventas')->insertGetId([
                'ven_user' => auth()->id(),
                'ven_fecha' => $request->fecha_venta,
                'ven_cliente' => $request->cliente_id,
                'ven_total_vendido' => $request->total,
                'ven_descuento' => $request->descuento_monto ?? 0,
                'ven_observaciones' => 'Venta Pendiente de autorizar por digecam',
                'ven_situacion' => 'PENDIENTE'
            ]);

            $totalPagado = 0;
            $cantidadPagos = 0;

            // 2. PROCESAR CADA PRODUCTO
            foreach ($request->productos as $productoData) {
                $producto = DB::table('pro_productos')->where('producto_id', $productoData['producto_id'])->first();

                if (!$producto) {
                    return response()->json([
                        'success' => false,
                        'message' => "Producto con ID {$productoData['producto_id']} no encontrado"
                    ], 422);
                }

                // Validar stock disponible SOLO si el producto lo necesita
                if ($productoData['producto_requiere_stock'] == 1) {
                    $stockActual = DB::table('pro_stock_actual')->where('stock_producto_id', $producto->producto_id)->first();
                    if (!$stockActual || $stockActual->stock_cantidad_disponible < $productoData['cantidad']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stock insuficiente para el producto: {$producto->producto_nombre}"
                        ], 422);
                    }
                }

                // Insertar detalle de venta en pro_detalle_ventas
                $detalleId = DB::table('pro_detalle_ventas')->insertGetId([
                    'det_ven_id' => $ventaId,
                    'det_producto_id' => $producto->producto_id,
                    'det_cantidad' => $productoData['cantidad'],
                    'det_precio' => $productoData['precio_unitario'],
                    'det_descuento' => 0,
                    'det_situacion' => 'PENDIENTE',
                ]);

                if ($productoData['producto_requiere_stock'] == 1) {
                    // PROCESAR SEGÚN TIPO DE PRODUCTO
                    if ($productoData['requiere_serie'] == 1) {
                        // ===============================
                        // PRODUCTO CON SERIES
                        // ===============================
                        $seriesSeleccionadas = $productoData['series_seleccionadas'] ?? [];

                        if (empty($seriesSeleccionadas)) {
                            return response()->json([
                                'success' => false,
                                'message' => "El producto {$producto->producto_nombre} requiere series"
                            ], 422);
                        }

                        if (count($seriesSeleccionadas) !== $productoData['cantidad']) {
                            return response()->json([
                                'success' => false,
                                'message' => "Debe seleccionar exactamente {$productoData['cantidad']} serie(s) para {$producto->producto_nombre}"
                            ], 422);
                        }

                        // Obtener IDs de series por número de serie
                        $seriesInfo = DB::table('pro_series_productos')
                            ->whereIn('serie_numero_serie', $seriesSeleccionadas)
                            ->where('serie_producto_id', $producto->producto_id)
                            ->where('serie_estado', 'disponible')
                            ->where('serie_situacion', 1)
                            ->get();

                        if ($seriesInfo->count() !== count($seriesSeleccionadas)) {
                            return response()->json([
                                'success' => false,
                                'message' => "Una o más series no están disponibles para el producto {$producto->producto_nombre}"
                            ], 422);
                        }

                        // Actualizar series: cambiar estado y situación
                    //  Actualizar cada serie individualmente según si tiene tenencia
                        $seriesConTenencia = $productoData['series_con_tenencia'] ?? [];

                        foreach ($seriesInfo as $serieInfo) {
                            $updateData = [
                                'serie_estado' => 'pendiente',
                                'serie_situacion' => 0,
                            ];

                            // Verificar si esta serie específica tiene tenencia
                            if (isset($seriesConTenencia[$serieInfo->serie_numero_serie])) {
                                $updateData['serie_tiene_tenencia'] = true;
                                $updateData['serie_monto_tenencia'] = self::MONTO_TENENCIA;
                            }

                            DB::table('pro_series_productos')
                                ->where('serie_id', $serieInfo->serie_id)
                                ->update($updateData);
                        }

                        // Registrar movimiento por cada serie
                        foreach ($seriesInfo as $serieInfo) {
                            DB::table('pro_movimientos')->insert([
                                'mov_producto_id' => $producto->producto_id,
                                'mov_tipo' => 'venta',
                                'mov_origen' => 'venta',
                                'mov_destino' => 'cliente',
                                'mov_cantidad' => 1,
                                'mov_precio_unitario' => $productoData['precio_unitario'],
                                'mov_valor_total' => $productoData['precio_unitario'],
                                'mov_fecha' => now(),
                                'mov_usuario_id' => auth()->id(),
                                'mov_serie_id' => $serieInfo->serie_id,
                                'mov_documento_referencia' => "VENTA-{$ventaId}",
                                'mov_observaciones' => "Venta - Serie: {$serieInfo->serie_numero_serie}",
                                'mov_situacion' => 3, //situacion 3 pendiente de validar, situacion 1 autorizado 0 eliminado o ANULADA
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        // Actualizar stock
                        DB::table('pro_stock_actual')
                            ->where('stock_producto_id', $producto->producto_id)
                            ->increment('stock_cantidad_reservada', count($seriesSeleccionadas));
                    } else {
                        // ===============================
                        // PRODUCTO SIN SERIES (CON O SIN LOTES)
                        // ===============================
                        if ($productoData['tiene_lotes'] && !empty($productoData['lotes_seleccionados'])) {
                            // PRODUCTO CON LOTES
                            $lotesSeleccionados = $productoData['lotes_seleccionados'];
                            $totalAsignado = array_sum(array_column($lotesSeleccionados, 'cantidad'));

                            if ($totalAsignado !== $productoData['cantidad']) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "La cantidad asignada en lotes ($totalAsignado) debe coincidir con la cantidad del producto (" . $productoData['cantidad'] . ") para " . $producto->producto_nombre
                                ], 422);
                            }

                            // Procesar cada lote
                            foreach ($lotesSeleccionados as $loteData) {
                                $lote = DB::table('pro_lotes')->where('lote_id', $loteData['lote_id'])->first();

                                if (!$lote || $lote->lote_cantidad_disponible < $loteData['cantidad']) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "El lote no tiene suficiente stock disponible"
                                    ], 422);
                                }

                                // Actualizar cantidad disponible en el lote // quedó igual si se cansela se regresa la cantidad a pro_lotes
                                DB::table('pro_lotes')
                                    ->where('lote_id', $loteData['lote_id'])
                                    ->decrement('lote_cantidad_disponible', $loteData['cantidad']);

                                DB::table('pro_lotes')
                                    ->where('lote_id', $loteData['lote_id'])
                                    ->decrement('lote_cantidad_total', $loteData['cantidad']);


                                // Registrar movimiento de lote
                                DB::table('pro_movimientos')->insert([
                                    'mov_producto_id' => $producto->producto_id,
                                    'mov_tipo' => 'venta',
                                    'mov_origen' => 'venta',
                                    'mov_destino' => 'cliente',
                                    'mov_cantidad' => $loteData['cantidad'],
                                    'mov_precio_unitario' => $productoData['precio_unitario'],
                                    'mov_valor_total' => $productoData['precio_unitario'] * $loteData['cantidad'],
                                    'mov_fecha' => now(),
                                    'mov_usuario_id' => auth()->id(),
                                    'mov_lote_id' => $loteData['lote_id'],
                                    'mov_documento_referencia' => "VENTA-{$ventaId}",
                                    'mov_observaciones' => "Venta - Lote: {$lote->lote_codigo}",
                                    'mov_situacion' => 3,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);

                                // Si el lote se agotó, cambiar situación   /// si cancela y el lote estan en situacion cero se debe de cambiar a uno
                                $loteActualizado = DB::table('pro_lotes')->where('lote_id', $loteData['lote_id'])->first();
                                if ($loteActualizado->lote_cantidad_disponible <= 0) {
                                    DB::table('pro_lotes')
                                        ->where('lote_id', $loteData['lote_id'])
                                        ->update(['lote_situacion' => 0]);
                                }
                            }
                        } else {
                            // PRODUCTO SIN LOTES (STOCK GENERAL)
                            DB::table('pro_movimientos')->insert([
                                'mov_producto_id' => $producto->producto_id,
                                'mov_tipo' => 'venta',
                                'mov_origen' => 'venta',
                                'mov_destino' => 'cliente',
                                'mov_cantidad' => $productoData['cantidad'],
                                'mov_precio_unitario' => $productoData['precio_unitario'],
                                'mov_valor_total' => $productoData['precio_unitario'] * $productoData['cantidad'],
                                'mov_fecha' => now(),
                                'mov_usuario_id' => auth()->id(),
                                'mov_lote_id' => null,
                                'mov_documento_referencia' => "VENTA-{$ventaId}",
                                'mov_observaciones' => "Venta - Stock general",
                                'mov_situacion' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        // Actualizar stock total (común para ambos casos)

                        // Actualizar stock
                        DB::table('pro_stock_actual')
                            ->where('stock_producto_id', $producto->producto_id)
                            ->increment('stock_cantidad_reservada', $productoData['cantidad']);

                    }
                } else {
                    DB::table('pro_movimientos')->insert([
                        'mov_producto_id' => $producto->producto_id,
                        'mov_tipo' => 'venta',
                        'mov_origen' => 'venta',
                        'mov_destino' => 'cliente',
                        'mov_cantidad' => $productoData['cantidad'],
                        'mov_precio_unitario' => $productoData['precio_unitario'],
                        'mov_valor_total' => $productoData['precio_unitario'] * $productoData['cantidad'],
                        'mov_fecha' => now(),
                        'mov_usuario_id' => auth()->id(),
                        'mov_lote_id' => null,
                        'mov_documento_referencia' => "VENTA-{$ventaId}",
                        'mov_observaciones' => "Venta - Stock general",
                        'mov_situacion' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }


            // 3. PROCESAR PAGOS - NUEVAS TABLAS
            // Asegúrate de acceder a los datos dentro de 'pago'
            $metodoPago = $request->metodo_pago;
            $totalVenta = $request->total;

            if ($metodoPago == '6') {
                // SISTEMA DE CUOTAS
                // Acceder a los valores dentro del array 'pago'
                $abonoInicial = $request->pago['abono_inicial'] ?? 0;  // Obtener abono inicial
                $cuotas = $request->pago['cuotas'] ?? [];  // Obtener el arreglo de cuotas, si existe

                // 3.1 Crear registro maestro en la tabla pro_pagos (registro del pago principal)
                $pagoId = DB::table('pro_pagos')->insertGetId([
                    'pago_venta_id' => $ventaId,
                    'pago_monto_total' => $totalVenta,
                    'pago_monto_pagado' => $abonoInicial,
                    'pago_monto_pendiente' => $totalVenta - $abonoInicial,
                    'pago_tipo_pago' => 'CUOTAS',  // Tipo de pago: cuotas
                    'pago_cantidad_cuotas' => $request->pago['cantidad_cuotas'],  // Acceder a cantidad de cuotas
                    'pago_abono_inicial' => $abonoInicial,
                    'pago_estado' => 'PENDIENTE',
                    'pago_fecha_inicio' => now(),  // Fecha de inicio del pago
                    'pago_fecha_completado' => $abonoInicial >= $totalVenta ? now() : null,  // Fecha de completado si ya pagó el total
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 3.2 Registrar el abono inicial si existe
                if ($abonoInicial > 0) {
                    $metodoAbonoId = $request->pago['metodo_abono'] === 'transferencia' ? 4 : 1;  // Método de abono basado en 'transferencia'

                    // Inserta el detalle del abono inicial en la tabla pro_detalle_pagos
                    DB::table('pro_detalle_pagos')->insert([
                        'det_pago_pago_id' => $pagoId,  // Relacionado con el pago principal
                        'det_pago_cuota_id' => null,  // No se asigna cuota ya que es abono inicial
                        'det_pago_fecha' => now(),
                        'det_pago_monto' => $abonoInicial,
                        'det_pago_metodo_pago' => $metodoAbonoId,  // Método de pago
                        // 'det_pago_banco_id' => $request->pago['banco_id_abono'],  // Banco del abono
                        'det_pago_banco_id' => 1,  // Banco del abono
                        'det_pago_numero_autorizacion' => $request->pago['numero_autorizacion_abono'],  // Número de autorización
                        'det_pago_tipo_pago' => 'ABONO_INICIAL',  // Tipo de pago
                        'det_pago_estado' => 'VALIDO',  // Estado del pago
                        'det_pago_observaciones' => 'Abono inicial de la venta',
                        'det_pago_usuario_registro' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Actualiza el monto total pagado
                    $totalPagado += $abonoInicial;
                    $cantidadPagos++;
                }

                // 3.3 Crear cuotas
                $fechaBase = now();  // Fecha base para el cálculo de vencimientos
                foreach ($cuotas as $index => $cuotaData) {
                    if ($cuotaData['monto'] > 0) {
                        // Si el monto de la cuota es mayor a 0, se registra
                        $fechaVencimiento = $fechaBase->copy()->addMonths($index + 1);  // Fecha de vencimiento, sumando meses

                        // Inserta la cuota en la tabla pro_cuotas
                        DB::table('pro_cuotas')->insert([
                            'cuota_control_id' => $pagoId,  // Relaciona la cuota con el pago principal
                            'cuota_numero' => $index + 1,  // Número de cuota
                            'cuota_monto' => $cuotaData['monto'],  // Monto de la cuota
                            'cuota_fecha_vencimiento' => $fechaVencimiento,  // Fecha de vencimiento de la cuota
                            'cuota_estado' => 'PENDIENTE',  // Estado inicial de la cuota
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            } else {
                // PAGO ÚNICO (efectivo, tarjetas, transferencia, cheque)

                // 3.1 Crear registro maestro en pro_pagos (registro del pago único)
                $pagoId = DB::table('pro_pagos')->insertGetId([
                    'pago_venta_id' => $ventaId,
                    'pago_monto_total' => $totalVenta,
                    'pago_monto_pagado' => $totalVenta,
                    'pago_monto_pendiente' => 0,
                    'pago_tipo_pago' => 'UNICO',
                    'pago_cantidad_cuotas' => 1,
                    'pago_abono_inicial' => $totalVenta,
                    'pago_estado' => 'PENDIENTE',
                    'pago_fecha_inicio' => now(),
                    'pago_fecha_completado' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 3.2 Registrar el pago único en pro_detalle_pagos
                DB::table('pro_detalle_pagos')->insert([
                    'det_pago_pago_id' => $pagoId,
                    'det_pago_cuota_id' => null,
                    'det_pago_fecha' => now(),
                    'det_pago_monto' => $totalVenta,
                    'det_pago_metodo_pago' => $metodoPago,
                    // 'det_pago_banco_id' => $request->tipo_banco,
                    'det_pago_banco_id' => 1,
                    'det_pago_numero_autorizacion' => $request->numero_autorizacion,
                    'det_pago_tipo_pago' => 'PAGO_UNICO',
                    'det_pago_estado' => 'VALIDO',
                    'det_pago_observaciones' => 'Pago completo de la venta',
                    'det_pago_usuario_registro' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Actualiza los valores de totalPagado y cantidadPagos
                $totalPagado = $totalVenta;
                $cantidadPagos = 1;
            }

            // 5. CALCULAR PORCENTAJE DEL VENDEDOR
            $porcentaje = 5.0;  // Porcentaje de comisión del vendedor
            $ganancia = $totalVenta * ($porcentaje / 100);  // Cálculo de la ganancia

            // Registrar la comisión en la tabla pro_porcentaje_vendedor
            DB::table('pro_porcentaje_vendedor')->insert([
                'porc_vend_user_id' => auth()->id(),  // ID del vendedor (usuario que registra la venta)
                'porc_vend_ven_id' => $ventaId,  // ID de la venta
                'porc_vend_porcentaje' => $porcentaje,  // Porcentaje de comisión
                'porc_vend_cantidad_ganancia' => $ganancia,  // Ganancia calculada
                'porc_vend_monto_base' => $totalVenta,  // Monto base sobre el que se calcula la comisión
                'porc_vend_fecha_asignacion' => now(),  // Fecha de asignación de la comisión
                'porc_vend_estado' => 'PENDIENTE',  // Estado inicial de la comisión (pendiente)
                'porc_vend_situacion' => 'ACTIVO',  // Situación activa de la comisión
                'porc_vend_observaciones' => 'Comisión por venta',  // Observaciones (puedes personalizarlo según sea necesario)

            ]);

            // 6. REGISTRAR EN HISTORIAL DE CAJA
            DB::table('cja_historial')->insert([
                'cja_tipo' => 'VENTA',
                'cja_id_venta' => $ventaId,
                'cja_usuario' => auth()->id(),
                'cja_monto' => $totalPagado,
                'cja_fecha' => now(),
                'cja_metodo_pago' => $request->metodo_pago,
                'cja_no_referencia' => "VENTA-{$ventaId}",
                'cja_situacion' => 'PENDIENTE',
                'cja_observaciones' => 'Venta registrada',
                'created_at' => now()
            ]);

            // Si todo va bien, confirmamos la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'venta_id' => $ventaId,
                'folio' => "VENTA-{$ventaId}",
                'pago_id' => $pagoId
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }




    public function show(Ventas $ventas)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ventas $ventas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ventas $ventas)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ventas $ventas)
    {
        //
    }
}
