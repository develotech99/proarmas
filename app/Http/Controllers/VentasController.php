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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class VentasController extends Controller
{

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
                'cliente_dpi'
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
                'producto_requiere_stock'
            )
            ->orderBy('producto_nombre')
            ->get();

        // Series + LOTES (igual que series, pero para pro_lotes)
        $productos = $productos->map(function ($producto) {
            $productoArray = (array) $producto;

            // SERIES
            if ($producto->producto_requiere_serie == 1) {
                $seriesDisponibles = DB::table('pro_series_productos')
                    ->where('serie_producto_id', $producto->producto_id)
                    ->where('serie_situacion', 1)
                    ->select('serie_producto_id', 'serie_numero_serie', 'serie_situacion')
                    ->orderBy('serie_numero_serie')
                    ->get();

                $productoArray['series_disponibles'] = $seriesDisponibles;
                $productoArray['cantidad_series']    = $seriesDisponibles->count();
            } else {
                $productoArray['series_disponibles'] = [];
                $productoArray['cantidad_series']    = 0;
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

            $productoArray['lotes']                = $lotes;                           // listado de lotes
            $productoArray['cantidad_lotes']       = $lotes->count();                  // cuántos lotes
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
        // Validación de los datos
        $data = $request->validate([
            'cliente_nombre1'   => ['required', 'string', 'max:50'],
            'cliente_nombre2'   => ['nullable', 'string', 'max:50'],
            'cliente_apellido1' => ['required', 'string', 'max:50'],
            'cliente_apellido2' => ['nullable', 'string', 'max:50'],
            'cliente_dpi'       => ['nullable', 'string', 'max:20'],
            'cliente_nit'       => ['nullable', 'string', 'max:20'],
            'cliente_direccion' => ['nullable', 'string', 'max:255'],
            'cliente_telefono'  => ['nullable', 'string', 'max:30'],
            'cliente_correo'    => ['nullable', 'string', 'max:150'],
            'cliente_tipo'      => ['nullable', 'integer', 'in:0,1,2'],
            'cliente_user_id' => ['nullable', 'integer'],

        ]);
        //  echo json_encode($data);
        // exit;

        $cliente = Clientes::create($data);
        return response()->json($cliente, 201);
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
                'ven_observaciones' => 'Venta procesada desde sistema',
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

                // // Validar stock disponible
                // $stockActual = DB::table('pro_stock_actual')->where('stock_producto_id', $producto->producto_id)->first();
                // if (!$stockActual || $stockActual->stock_cantidad_disponible < $productoData['cantidad']) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => "Stock insuficiente para el producto: {$producto->producto_nombre}"
                //     ], 422);
                // }

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
                        $seriesIds = $seriesInfo->pluck('serie_id');
                        DB::table('pro_series_productos')
                            ->whereIn('serie_id', $seriesIds)
                            ->update([
                                'serie_estado' => 'vendido',
                                'serie_situacion' => 0,
                            ]);

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
                                'mov_situacion' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        // Actualizar stock
                        DB::table('pro_stock_actual')
                            ->where('stock_producto_id', $producto->producto_id)
                            ->decrement('stock_cantidad_disponible', count($seriesSeleccionadas));

                        DB::table('pro_stock_actual')
                            ->where('stock_producto_id', $producto->producto_id)
                            ->decrement('stock_cantidad_total', count($seriesSeleccionadas));
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

                                // Actualizar cantidad disponible en el lote
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
                                    'mov_situacion' => 1,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);

                                // Si el lote se agotó, cambiar situación
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
                        DB::table('pro_stock_actual')
                            ->where('stock_producto_id', $producto->producto_id)
                            ->decrement('stock_cantidad_disponible', $productoData['cantidad']);

                        DB::table('pro_stock_actual')
                            ->where('stock_producto_id', $producto->producto_id)
                            ->decrement('stock_cantidad_total', $productoData['cantidad']);
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
                    'pago_estado' => $abonoInicial >= $totalVenta ? 'COMPLETADO' : ($abonoInicial > 0 ? 'PARCIAL' : 'PENDIENTE'),
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
                    'pago_estado' => 'COMPLETADO',
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





    // // 3. PROCESAR PAGOS
    // $datosPago = $request->pago;
    // $totalVenta = $request->total;

    // switch ($datosPago['tipo']) {
    //     case 'efectivo':
    //     case 'tarjeta_credito':
    //     case 'tarjeta_debito':
    //     case 'transferencia':
    //     case 'cheque':
    //         $tipoMetodo = [
    //             'efectivo' => 1,
    //             'tarjeta_credito' => 2,
    //             'tarjeta_debito' => 3,
    //             'transferencia' => 4,
    //             'cheque' => 5
    //         ];

    //         DB::table('pro_pagos')->insert([
    //             'pago_id_venta' => $ventaId,
    //             'pago_fecha' => now(),
    //             'pago_metodo_pago' => $tipoMetodo[$datosPago['tipo']],
    //             'pago_no_referencia' => $datosPago['numero_autorizacion'] ?? null,
    //             'pago_monto' => $totalVenta,
    //             'pago_situacion' => 'VALIDO',
    //             'pago_observaciones' => 'Pago completo - ' . $datosPago['tipo'],
    //             'created_at' => now(),
    //             'updated_at' => now()
    //         ]);
    //         $totalPagado = $totalVenta;
    //         $cantidadPagos = 1;
    //         break;

    //     case 'cuotas':
    //         // Abono inicial si existe
    //         if (($datosPago['abono_inicial'] ?? 0) > 0) {
    //             $tipoAbono = ($datosPago['metodo_abono'] === 'transferencia') ? 4 : 1;

    //             DB::table('pro_pagos')->insert([
    //                 'pago_id_venta' => $ventaId,
    //                 'pago_fecha' => now(),
    //                 'pago_metodo_pago' => $tipoAbono,
    //                 'pago_no_referencia' => $datosPago['numero_autorizacion_abono'] ?? null,
    //                 'pago_monto' => $datosPago['abono_inicial'],
    //                 'pago_situacion' => 'VALIDO',
    //                 'pago_observaciones' => 'Abono inicial - cuotas',
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ]);
    //             $totalPagado += $datosPago['abono_inicial'];
    //             $cantidadPagos++;
    //         }
    //         break;
    // }

    // // 4. CREAR REGISTRO EN pro_detalle_pagos
    // $estadoPago = ($totalPagado >= $totalVenta) ? 'COMPLETADO' : 
    //              ($totalPagado > 0 ? 'PARCIAL' : 'PENDIENTE');

    // $montoPendiente = max(0, $totalVenta - $totalPagado);

    // DB::table('pro_detalle_pagos')->insert([
    //     'det_pago_id_venta' => $ventaId,
    //     'det_pago_cantidad_pagos' => $cantidadPagos,
    //     'det_pago_monto_total' => $totalPagado,
    //     'det_pago_monto_pendiente' => $montoPendiente,
    //     'det_pago_estado' => $estadoPago,
    //     'det_pago_fecha_inicio' => now(),
    //     'det_pago_fecha_completado' => ($estadoPago === 'COMPLETADO') ? now() : null,
    //     'det_pago_situacion' => 'ACTIVO',
    //     'created_at' => now(),
    //     'updated_at' => now()
    // ]);

    // // 5. CALCULAR PORCENTAJE DEL VENDEDOR
    // $porcentaje = 5.0; // 5% por ejemplo - puedes configurarlo
    // $ganancia = $totalVenta * ($porcentaje / 100);

    // DB::table('pro_porcentaje_vendedor')->insert([
    //     'porc_vend_user_id' => auth()->id(),
    //     'porc_vend_ven_id' => $ventaId,
    //     'porc_vend_porcentaje' => $porcentaje,
    //     'porc_vend_cantidad_ganancia' => $ganancia,
    //     'porc_vend_monto_base' => $totalVenta,
    //     'porc_vend_fecha_asignacion' => now(),
    //     'porc_vend_estado' => 'PENDIENTE',
    //     'porc_vend_situacion' => 'ACTIVO',
    //     'porc_vend_observaciones' => 'Comisión por venta',
    //     'created_at' => now(),
    //     'updated_at' => now()
    // ]);

    // // 6. REGISTRAR EN HISTORIAL DE CAJA
    // DB::table('cja_historial')->insert([
    //     'cja_tipo' => 'VENTA',
    //     'cja_id_venta' => $ventaId,
    //     'cja_usuario' => auth()->id(),
    //     'cja_monto' => $totalPagado,
    //     'cja_fecha' => now(),
    //     'cja_metodo_pago' => $request->metodo_pago,
    //     'cja_no_referencia' => "VENTA-{$ventaId}",
    //     'cja_situacion' => 'ACTIVO',
    //     'cja_observaciones' => 'Venta registrada',
    //     'created_at' => now()
    // ]);

    // DB::commit();








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
