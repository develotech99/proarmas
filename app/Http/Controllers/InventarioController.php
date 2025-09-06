<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoFoto;
use App\Models\SerieProducto;
use App\Models\Lote;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controlador principal para la gestión de inventario de armería
 * Maneja productos, stock, movimientos y series
 */
class InventarioController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Vista principal del módulo de inventario
     */
    public function index()
    {
        // Datos necesarios para los selects y filtros
        $categorias = DB::table('pro_categorias')->where('categoria_situacion', 1)->get();
        $marcas = DB::table('pro_marcas')->where('marca_situacion', 1)->get();
        $modelos = DB::table('pro_modelo')->where('modelo_situacion', 1)->get();
        $calibres = DB::table('pro_calibres')->where('calibre_situacion', 1)->get();
        
        // Tabla de licencias no está en la migración, así que la omitimos por ahora
        $licencias = collect(); // Array vacío

        return view('inventario.index', compact(
            'categorias', 'marcas', 'modelos', 'calibres', 'licencias'
        ));
    }

    /**
     * Obtiene la lista de productos con stock para DataTable
     */
    public function getProductosStock(Request $request): JsonResponse
    {
        try {
            $query = Producto::activos()
                ->leftJoin('pro_categorias', 'pro_productos.producto_categoria_id', '=', 'pro_categorias.categoria_id')
                ->leftJoin('pro_marcas', 'pro_productos.producto_marca_id', '=', 'pro_marcas.marca_id')
                ->leftJoin('pro_modelo', 'pro_productos.producto_modelo_id', '=', 'pro_modelo.modelo_id')
                ->select([
                    'pro_productos.producto_id',
                    'pro_productos.producto_nombre',
                    'pro_productos.producto_codigo_barra',
                    'pro_productos.producto_requiere_serie',
                    'pro_categorias.categoria_nombre as categoria_nombre',
                    'pro_marcas.marca_descripcion as marca_nombre',
                    'pro_modelo.modelo_descripcion as modelo_nombre'
                ]);

            // Aplicar filtros si existen
            if ($request->filled('categoria')) {
                $query->where('pro_productos.producto_categoria_id', $request->categoria);
            }

            if ($request->filled('marca')) {
                $query->where('pro_productos.producto_marca_id', $request->marca);
            }

            $productos = $query->get();

            // Calcular stock para cada producto
            $data = $productos->map(function ($producto) {
                $productoModel = Producto::find($producto->producto_id);
                
                return [
                    'producto_id' => $producto->producto_id,
                    'nombre' => $producto->producto_nombre,
                    'codigo_barra' => $producto->producto_codigo_barra,
                    'categoria' => $producto->categoria_nombre,
                    'marca' => $producto->marca_nombre,
                    'modelo' => $producto->modelo_nombre,
                    'requiere_serie' => $producto->producto_requiere_serie,
                    'stock_actual' => $productoModel->stock_actual,
                    'series_disponibles' => $producto->producto_requiere_serie ? 
                        $productoModel->seriesDisponibles()->count() : 0,
                    'acciones' => $producto->producto_id
                ];
            });

            return response()->json([
                'data' => $data,
                'recordsTotal' => $data->count(),
                'recordsFiltered' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra un nuevo producto en el inventario
     */
    public function ingresarProducto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'producto_nombre' => 'required|string|max:100',
            'producto_codigo_barra' => 'nullable|string|max:100|unique:pro_productos,producto_codigo_barra',
            'producto_categoria_id' => 'required|integer|exists:pro_categorias,categoria_id',
            'producto_subcategoria_id' => 'required|integer|exists:pro_subcategorias,subcategoria_id',
            'producto_marca_id' => 'required|integer|exists:pro_marcas,marca_id',
            'producto_modelo_id' => 'nullable|integer|exists:pro_modelo,modelo_id',
            'producto_calibre_id' => 'nullable|integer|exists:pro_calibres,calibre_id',
            'producto_requiere_serie' => 'boolean',
            'producto_es_importado' => 'boolean',
            'producto_id_licencia' => 'nullable|integer',
            'cantidad_inicial' => 'required_if:producto_requiere_serie,false|integer|min:1',
            'series' => 'required_if:producto_requiere_serie,true|array',
            'series.*' => 'required_if:producto_requiere_serie,true|string|unique:pro_series_productos,serie_numero_serie',
            'lote_codigo' => 'required_if:producto_requiere_serie,false|string|max:100|unique:pro_lotes,lote_codigo',
            'mov_origen' => 'required|string|max:100',
            'mov_observaciones' => 'nullable|string|max:250',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            // 1. Crear el producto
            $producto = Producto::create($request->only([
                'producto_nombre', 'producto_codigo_barra', 'producto_categoria_id',
                'producto_subcategoria_id', 'producto_marca_id', 'producto_modelo_id',
                'producto_calibre_id', 'producto_requiere_serie', 'producto_es_importado',
                'producto_id_licencia'
            ]));

            // 2. Procesar fotos si existen
            if ($request->hasFile('fotos')) {
                $this->procesarFotos($request->file('fotos'), $producto->producto_id);
            }

            // 3. Crear lote si no requiere serie
            $lote_id = null;
            if (!$request->producto_requiere_serie) {
                $lote = Lote::create([
                    'lote_codigo' => $request->lote_codigo,
                    'lote_descripcion' => "Lote inicial para {$producto->producto_nombre}"
                ]);
                $lote_id = $lote->lote_id;
            }

            // 4. Registrar movimiento inicial y series
            if ($request->producto_requiere_serie) {
                // Crear series individuales
                foreach ($request->series as $numeroSerie) {
                    SerieProducto::create([
                        'serie_producto_id' => $producto->producto_id,
                        'serie_numero_serie' => $numeroSerie,
                        'serie_estado' => 'disponible'
                    ]);
                }

                // Registrar movimiento por la cantidad de series
                Movimiento::create([
                    'mov_producto_id' => $producto->producto_id,
                    'mov_tipo' => 'ingreso',
                    'mov_origen' => $request->mov_origen,
                    'mov_cantidad' => count($request->series),
                    'mov_usuario_id' => Auth::id(),
                    'mov_observaciones' => $request->mov_observaciones
                ]);
            } else {
                // Registrar movimiento por cantidad
                Movimiento::create([
                    'mov_producto_id' => $producto->producto_id,
                    'mov_tipo' => 'ingreso',
                    'mov_origen' => $request->mov_origen,
                    'mov_cantidad' => $request->cantidad_inicial,
                    'mov_usuario_id' => Auth::id(),
                    'mov_lote_id' => $lote_id,
                    'mov_observaciones' => $request->mov_observaciones
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto ingresado correctamente al inventario',
                'producto_id' => $producto->producto_id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al ingresar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra un movimiento de inventario (egreso o baja)
     */
    public function registrarMovimiento(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|integer|exists:pro_productos,producto_id',
            'mov_tipo' => 'required|in:egreso,baja',
            'mov_origen' => 'required|string|max:100',
            'mov_cantidad' => 'required_if:requiere_serie,false|integer|min:1',
            'series_seleccionadas' => 'required_if:requiere_serie,true|array',
            'series_seleccionadas.*' => 'required_if:requiere_serie,true|integer|exists:pro_series_productos,serie_id',
            'mov_observaciones' => 'nullable|string|max:250'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($request->producto_id);

            if ($producto->producto_requiere_serie) {
                // Verificar que las series estén disponibles
                $series = SerieProducto::whereIn('serie_id', $request->series_seleccionadas)
                    ->where('serie_estado', 'disponible')
                    ->get();

                if ($series->count() !== count($request->series_seleccionadas)) {
                    throw new \Exception('Algunas series seleccionadas no están disponibles');
                }

                // Actualizar estado de las series
                $nuevoEstado = $request->mov_tipo === 'egreso' ? 'vendido' : 'baja';
                SerieProducto::whereIn('serie_id', $request->series_seleccionadas)
                    ->update(['serie_estado' => $nuevoEstado]);

                $cantidad = count($request->series_seleccionadas);
            } else {
                // Verificar stock disponible
                if (!$producto->tieneStock($request->mov_cantidad)) {
                    throw new \Exception('Stock insuficiente. Stock actual: ' . $producto->stock_actual);
                }

                $cantidad = $request->mov_cantidad;
            }

            // Registrar el movimiento
            Movimiento::create([
                'mov_producto_id' => $request->producto_id,
                'mov_tipo' => $request->mov_tipo,
                'mov_origen' => $request->mov_origen,
                'mov_cantidad' => $cantidad,
                'mov_usuario_id' => Auth::id(),
                'mov_observaciones' => $request->mov_observaciones
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->mov_tipo) . ' registrado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las series disponibles de un producto
     */
    public function getSeriesDisponibles($producto_id): JsonResponse
    {
        try {
            $series = SerieProducto::where('serie_producto_id', $producto_id)
                ->where('serie_estado', 'disponible')
                ->where('serie_situacion', 1)
                ->orderBy('serie_numero_serie')
                ->get(['serie_id', 'serie_numero_serie', 'serie_fecha_ingreso']);

            return response()->json([
                'success' => true,
                'data' => $series
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar series: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el historial de movimientos de un producto
     */
    public function getHistorialMovimientos($producto_id): JsonResponse
    {
        try {
            $movimientos = Movimiento::where('mov_producto_id', $producto_id)
                ->activos()
                ->with(['lote:lote_id,lote_codigo'])
                ->orderBy('mov_fecha', 'desc')
                ->get();

            $data = $movimientos->map(function ($mov) {
                return [
                    'mov_id' => $mov->mov_id,
                    'tipo' => ucfirst($mov->mov_tipo),
                    'origen' => $mov->mov_origen,
                    'cantidad' => $mov->mov_cantidad,
                    'fecha' => $mov->mov_fecha->format('d/m/Y H:i'),
                    'usuario' => 'Usuario ID: ' . $mov->mov_usuario_id,
                    'lote' => $mov->lote->lote_codigo ?? 'N/A',
                    'observaciones' => $mov->mov_observaciones
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene detalles de un producto específico
     */
    public function getDetalleProducto($producto_id): JsonResponse
    {
        try {
            $producto = Producto::with(['fotos', 'series'])
                ->findOrFail($producto_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto,
                    'stock_actual' => $producto->stock_actual,
                    'requiere_serie' => $producto->producto_requiere_serie,
                    'fotos' => $producto->fotos,
                    'series_disponibles' => $producto->seriesDisponibles()->count(),
                    'series_total' => $producto->series()->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener subcategorías por categoría
     */
    public function getSubcategorias($categoria_id): JsonResponse
    {
        try {
            $subcategorias = DB::table('pro_subcategorias')
                ->where('subcategoria_idcategoria', $categoria_id)
                ->where('subcategoria_situacion', 1)
                ->get(['subcategoria_id', 'subcategoria_nombre']);

            return response()->json([
                'success' => true,
                'data' => $subcategorias
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar subcategorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen para el dashboard
     */
    public function getResumenDashboard(): JsonResponse
    {
        try {
            $totalProductos = Producto::activos()->count();
            
            $totalSeries = SerieProducto::where('serie_estado', 'disponible')
                ->where('serie_situacion', 1)
                ->count();
            
            $movimientosHoy = Movimiento::whereDate('mov_fecha', today())
                ->where('mov_situacion', 1)
                ->count();
            
            // Stock bajo (productos con menos de 5 unidades)
            $stockBajo = Producto::activos()->get()->filter(function($producto) {
                return $producto->stock_actual > 0 && $producto->stock_actual <= 5;
            })->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_productos' => $totalProductos,
                    'total_series' => $totalSeries,
                    'movimientos_hoy' => $movimientosHoy,
                    'stock_bajo' => $stockBajo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar resumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los movimientos para historial general
     */
    public function getMovimientos(Request $request): JsonResponse
    {
        try {
            $query = Movimiento::with(['producto:producto_id,producto_nombre', 'lote:lote_id,lote_codigo'])
                ->where('mov_situacion', 1);

            // Aplicar filtros
            if ($request->filled('producto_id')) {
                $query->where('mov_producto_id', $request->producto_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('mov_fecha', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('mov_fecha', '<=', $request->fecha_hasta);
            }

            if ($request->filled('tipo')) {
                $query->where('mov_tipo', $request->tipo);
            }

            $movimientos = $query->orderBy('mov_fecha', 'desc')->get();

            $data = $movimientos->map(function ($mov) {
                return [
                    'mov_id' => $mov->mov_id,
                    'fecha' => $mov->mov_fecha->format('d/m/Y H:i'),
                    'producto_nombre' => $mov->producto->producto_nombre ?? 'N/A',
                    'tipo' => ucfirst($mov->mov_tipo),
                    'origen' => $mov->mov_origen,
                    'cantidad' => $mov->mov_cantidad,
                    'usuario' => 'Usuario ID: ' . $mov->mov_usuario_id,
                    'lote' => $mov->lote->lote_codigo ?? 'N/A',
                    'observaciones' => $mov->mov_observaciones
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar movimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa y guarda las fotos del producto
     */
    private function procesarFotos($fotos, $producto_id)
    {
        $esPrimera = true;
        
        foreach ($fotos as $foto) {
            $nombreArchivo = 'producto_' . $producto_id . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
            $ruta = $foto->storeAs('productos', $nombreArchivo, 'public');

            ProductoFoto::create([
                'foto_producto_id' => $producto_id,
                'foto_url' => Storage::url($ruta),
                'foto_principal' => $esPrimera
            ]);

            $esPrimera = false;
        }
    }
}