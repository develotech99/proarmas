<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoFoto;
use App\Models\SerieProducto;
use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Precio;
use App\Models\StockActual;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controlador principal para la gestión de inventario de armería
 * Maneja productos, stock, movimientos, series y precios
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
        return view('inventario.index');
    }

    // ================================
    // PRODUCTOS
    // ================================

    /**
     * Obtener productos con información de stock
     */
    // Reemplazar el método getProductosStock en InventarioController

public function getProductosStock(Request $request): JsonResponse
{
    try {
        $query = Producto::activos()
            ->leftJoin('pro_categorias', 'pro_productos.producto_categoria_id', '=', 'pro_categorias.categoria_id')
            ->leftJoin('pro_subcategorias', 'pro_productos.producto_subcategoria_id', '=', 'pro_subcategorias.subcategoria_id')
            ->leftJoin('pro_marcas', 'pro_productos.producto_marca_id', '=', 'pro_marcas.marca_id')
            ->leftJoin('pro_modelo', 'pro_productos.producto_modelo_id', '=', 'pro_modelo.modelo_id')
            ->leftJoin('pro_stock_actual', 'pro_productos.producto_id', '=', 'pro_stock_actual.stock_producto_id')
            ->select([
                'pro_productos.producto_id',
                'pro_productos.producto_nombre',
                'pro_productos.pro_codigo_sku',
                'pro_productos.producto_codigo_barra',
                'pro_productos.producto_requiere_serie',
                'pro_productos.producto_stock_minimo',
                'pro_productos.producto_stock_maximo',
                'pro_categorias.categoria_nombre',
                'pro_subcategorias.subcategoria_nombre',
                'pro_marcas.marca_descripcion as marca_nombre',
                'pro_modelo.modelo_descripcion as modelo_nombre',
                'pro_stock_actual.stock_cantidad_total',
                'pro_stock_actual.stock_cantidad_disponible',
                'pro_stock_actual.stock_cantidad_reservada'
            ]);

        // Aplicar filtros si existen
        if ($request->filled('categoria')) {
            $query->where('pro_productos.producto_categoria_id', $request->categoria);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pro_productos.producto_nombre', 'LIKE', "%{$search}%")
                  ->orWhere('pro_productos.pro_codigo_sku', 'LIKE', "%{$search}%")
                  ->orWhere('pro_productos.producto_codigo_barra', 'LIKE', "%{$search}%");
            });
        }

        $productos = $query->orderBy('pro_productos.producto_nombre')->get();

        // AGREGAR FOTOS PRINCIPALES A CADA PRODUCTO
        $productosConFotos = $productos->map(function($producto) {
            $fotoPrincipal = ProductoFoto::activas()
                ->where('foto_producto_id', $producto->producto_id)
                ->where('foto_principal', true)
                ->first();

            return [
                'producto_id' => $producto->producto_id,
                'producto_nombre' => $producto->producto_nombre,
                'pro_codigo_sku' => $producto->pro_codigo_sku,
                'producto_codigo_barra' => $producto->producto_codigo_barra,
                'producto_requiere_serie' => $producto->producto_requiere_serie,
                'producto_stock_minimo' => $producto->producto_stock_minimo,
                'producto_stock_maximo' => $producto->producto_stock_maximo,
                'categoria_nombre' => $producto->categoria_nombre,
                'subcategoria_nombre' => $producto->subcategoria_nombre,
                'marca_nombre' => $producto->marca_nombre,
                'modelo_nombre' => $producto->modelo_nombre,
                'stock_cantidad_total' => $producto->stock_cantidad_total,
                'stock_cantidad_disponible' => $producto->stock_cantidad_disponible,
                'stock_cantidad_reservada' => $producto->stock_cantidad_reservada,
                'foto_principal' => $fotoPrincipal ? $fotoPrincipal->url_completa : null
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $productosConFotos
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar productos: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Buscar productos para el modal de ingreso
     */
    public function buscarProductos(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $productos = Producto::activos()
                ->leftJoin('pro_stock_actual', 'pro_productos.producto_id', '=', 'pro_stock_actual.stock_producto_id')
                ->where(function($q) use ($query) {
                    $q->where('producto_nombre', 'LIKE', "%{$query}%")
                      ->orWhere('pro_codigo_sku', 'LIKE', "%{$query}%")
                      ->orWhere('producto_codigo_barra', 'LIKE', "%{$query}%");
                })
                ->select([
                    'pro_productos.producto_id',
                    'pro_productos.producto_nombre',
                    'pro_productos.pro_codigo_sku',
                    'pro_productos.producto_requiere_serie',
                    'pro_stock_actual.stock_cantidad_disponible'
                ])
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $productos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalle de un producto específico
     */
    public function getProducto(Request $request, $id): JsonResponse
    {
        try {
            $producto = Producto::with(['stockActual'])
                ->leftJoin('pro_categorias', 'pro_productos.producto_categoria_id', '=', 'pro_categorias.categoria_id')
                ->leftJoin('pro_marcas', 'pro_productos.producto_marca_id', '=', 'pro_marcas.marca_id')
                ->select([
                    'pro_productos.*',
                    'pro_categorias.categoria_nombre',
                    'pro_marcas.marca_descripcion as marca_nombre'
                ])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $producto
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }

    /**
 * Obtener países activos
 */
public function getPaisesActivos(): JsonResponse
{
    try {
        $paises = DB::table('pro_paises')
            ->where('pais_situacion', 1)
            ->orderBy('pais_descripcion')
            ->get(['pais_id', 'pais_descripcion']);

        return response()->json([
            'success' => true,
            'data' => $paises
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar países'
        ], 500);
    }
}

    /**
     * Registrar nuevo producto
     */
   

public function store(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'producto_nombre' => 'required|string|max:100',
        'producto_descripcion' => 'nullable|string',
        'producto_categoria_id' => 'required|integer|exists:pro_categorias,categoria_id',
        'producto_subcategoria_id' => 'required|integer|exists:pro_subcategorias,subcategoria_id',
        'producto_marca_id' => 'required|integer|exists:pro_marcas,marca_id',
        'producto_modelo_id' => 'nullable|integer|exists:pro_modelo,modelo_id',
        'producto_calibre_id' => 'nullable|integer|exists:pro_calibres,calibre_id',
        'producto_codigo_barra' => 'nullable|string|unique:pro_productos,producto_codigo_barra',
        'producto_requiere_serie' => 'boolean',
        'producto_stock_minimo' => 'nullable|integer|min:0',
        'producto_stock_maximo' => 'nullable|integer|min:0',
        'fotos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    DB::beginTransaction();

    try {
        // Generar SKU único
        $sku = $this->generarSKU($request->producto_categoria_id, $request->producto_marca_id);

        $producto = Producto::create([
            'producto_nombre' => $request->producto_nombre,
            'producto_descripcion' => $request->producto_descripcion,
            'pro_codigo_sku' => $sku,
            'producto_codigo_barra' => $request->producto_codigo_barra,
            'producto_categoria_id' => $request->producto_categoria_id,
            'producto_subcategoria_id' => $request->producto_subcategoria_id,
            'producto_marca_id' => $request->producto_marca_id,
            'producto_modelo_id' => $request->producto_modelo_id,
            'producto_calibre_id' => $request->producto_calibre_id,
            'producto_requiere_serie' => $request->has('producto_requiere_serie'),
            'producto_stock_minimo' => $request->producto_stock_minimo ?? 0,
            'producto_stock_maximo' => $request->producto_stock_maximo ?? 0,
            'producto_situacion' => 1
        ]);

        // Crear registro de stock inicial
        StockActual::create([
            'stock_producto_id' => $producto->producto_id,
            'stock_cantidad_total' => 0,
            'stock_cantidad_disponible' => 0,
            'stock_cantidad_reservada' => 0,
            'stock_valor_total' => 0
        ]);

        // Procesar fotos si existen
        if ($request->hasFile('fotos')) {
            $this->procesarFotosNuevoProducto($request->file('fotos'), $producto->producto_id);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Producto registrado exitosamente',
            'data' => $producto
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Error al registrar producto: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Generar SKU único para el producto
     */
    private function generarSKU($categoriaId, $marcaId): string
    {
        // Obtener iniciales de categoría y marca
        $categoria = DB::table('pro_categorias')->where('categoria_id', $categoriaId)->first();
        $marca = DB::table('pro_marcas')->where('marca_id', $marcaId)->first();
    
        // Validar que existan
        if (!$categoria || !$marca) {
            throw new \Exception('Categoría o marca no encontrada');
        }
    
        $categoriaCode = strtoupper(substr($categoria->categoria_nombre, 0, 3));
        $marcaCode = strtoupper(substr($marca->marca_descripcion, 0, 3));
    
        // Generar número secuencial
        $ultimoNumero = Producto::where('pro_codigo_sku', 'LIKE', "{$categoriaCode}-{$marcaCode}-%")
            ->orderBy('pro_codigo_sku', 'desc')
            ->first();
    
        $numero = 1;
        if ($ultimoNumero) {
            $partes = explode('-', $ultimoNumero->pro_codigo_sku);
            if (count($partes) >= 3) {
                $numero = intval(end($partes)) + 1;
            }
        }
    
        return sprintf('%s-%s-%04d', $categoriaCode, $marcaCode, $numero);
    }

    // ================================
    // MOVIMIENTOS E INGRESOS
    // ================================

    /**
     * Procesar ingreso a inventario
     */
    public function ingresar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|integer|exists:pro_productos,producto_id',
            'mov_tipo' => 'required|in:ingreso,ajuste_positivo,devolucion',
            'mov_origen' => 'required|string|max:100',
            'mov_cantidad' => 'required_without:numeros_series|integer|min:1',
            'numeros_series' => 'required_without:mov_cantidad|string',
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
            $cantidadIngreso = 0;

            if ($producto->producto_requiere_serie) {
                // Procesar números de serie
                $series = array_filter(array_map('trim', explode("\n", $request->numeros_series)));
                
                if (empty($series)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debe proporcionar al menos un número de serie'
                    ], 422);
                }

                // Verificar que no existan series duplicadas
                $seriesExistentes = SerieProducto::whereIn('serie_numero_serie', $series)->pluck('serie_numero_serie');
                if ($seriesExistentes->isNotEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Las siguientes series ya existen: ' . $seriesExistentes->implode(', ')
                    ], 422);
                }

                // Crear registros de series
                foreach ($series as $numeroSerie) {
                    SerieProducto::create([
                        'serie_producto_id' => $producto->producto_id,
                        'serie_numero_serie' => $numeroSerie,
                        'serie_estado' => SerieProducto::ESTADO_DISPONIBLE,
                        'serie_fecha_ingreso' => now(),
                        'serie_observaciones' => $request->mov_observaciones,
                        'serie_situacion' => 1
                    ]);
                }

                $cantidadIngreso = count($series);
            } else {
                $cantidadIngreso = $request->mov_cantidad;
            }

            // Registrar movimiento
            $movimiento = Movimiento::create([
                'mov_producto_id' => $producto->producto_id,
                'mov_tipo' => $request->mov_tipo,
                'mov_origen' => $request->mov_origen,
                'mov_cantidad' => $cantidadIngreso,
                'mov_fecha' => now(),
                'mov_usuario_id' => Auth::id(),
                'mov_observaciones' => $request->mov_observaciones,
                'mov_situacion' => 1
            ]);

            // Actualizar stock
            $this->actualizarStock($producto->producto_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Ingreso procesado exitosamente. {$cantidadIngreso} unidades agregadas al inventario."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar stock actual de un producto
     */
    private function actualizarStock($productoId): void
    {
        $stock = StockActual::firstOrCreate(
            ['stock_producto_id' => $productoId],
            [
                'stock_cantidad_total' => 0,
                'stock_cantidad_disponible' => 0,
                'stock_cantidad_reservada' => 0,
                'stock_valor_total' => 0
            ]
        );

        $producto = Producto::find($productoId);

        if ($producto->producto_requiere_serie) {
            // Para productos con serie, contar series disponibles
            $disponibles = SerieProducto::where('serie_producto_id', $productoId)
                ->where('serie_estado', SerieProducto::ESTADO_DISPONIBLE)
                ->where('serie_situacion', 1)
                ->count();

            $reservadas = SerieProducto::where('serie_producto_id', $productoId)
                ->where('serie_estado', SerieProducto::ESTADO_RESERVADO)
                ->where('serie_situacion', 1)
                ->count();

            $total = $disponibles + $reservadas;
        } else {
            // Para productos sin serie, calcular desde movimientos
            $resumen = Movimiento::resumenPorProducto($productoId);
            $total = $resumen['stock_calculado'];
            $disponibles = max(0, $total - $stock->stock_cantidad_reservada);
            $reservadas = $stock->stock_cantidad_reservada;
        }

        $stock->update([
            'stock_cantidad_total' => $total,
            'stock_cantidad_disponible' => $disponibles,
            'stock_cantidad_reservada' => $reservadas,
            'stock_ultimo_movimiento' => now()
        ]);

        // Verificar alertas de stock
        $this->verificarAlertasStock($producto, $stock);
    }

    /**
     * Verificar y crear alertas de stock bajo/agotado
     */
    private function verificarAlertasStock($producto, $stock): void
    {
        $stockDisponible = $stock->stock_cantidad_disponible;
        $stockMinimo = $producto->producto_stock_minimo;

        // Verificar si ya existe una alerta reciente para este producto
        $alertaReciente = Alerta::where('alerta_producto_id', $producto->producto_id)
            ->whereIn('alerta_tipo', [Alerta::TIPO_STOCK_BAJO, Alerta::TIPO_STOCK_AGOTADO])
            ->where('alerta_fecha', '>=', now()->subDays(1))
            ->where('alerta_resuelta', false)
            ->exists();

        if ($alertaReciente) {
            return; // Ya existe una alerta reciente
        }

        if ($stockDisponible <= 0) {
            // Stock agotado
            Alerta::crearStockAgotado($producto);
        } elseif ($stockMinimo > 0 && $stockDisponible <= $stockMinimo) {
            // Stock bajo
            Alerta::crearStockBajo($producto, $stockDisponible);
        }
    }

    // ================================
    // ESTADÍSTICAS Y ALERTAS
    // ================================

    /**
     * Obtener estadísticas del inventario
     */
    public function getEstadisticas(): JsonResponse
    {
        try {
            $resumen = StockActual::resumenGeneral();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalProductos' => $resumen['total_productos'],
                    'stockTotal' => $resumen['con_stock'],
                    'stockBajo' => $resumen['stock_bajo'],
                    'stockAgotado' => $resumen['sin_stock']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener alertas de stock
     */
    public function getAlertasStock(): JsonResponse
    {
        try {
            $alertas = StockActual::stockBajo()
                ->orWhere('stock_cantidad_disponible', '<=', 0)
                ->with('producto:producto_id,producto_nombre')
                ->select([
                    'stock_producto_id',
                    'stock_cantidad_disponible'
                ])
                ->get()
                ->map(function($stock) {
                    return [
                        'producto_id' => $stock->stock_producto_id,
                        'producto_nombre' => $stock->producto->producto_nombre ?? 'Producto desconocido',
                        'stock_actual' => $stock->stock_cantidad_disponible
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $alertas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar alertas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // APIS PARA SELECTS
    // ================================

    /**
     * Obtener categorías activas
     */
    public function getCategoriasActivas(): JsonResponse
    {
        try {
            $categorias = DB::table('pro_categorias')
                ->where('categoria_situacion', 1)
                ->orderBy('categoria_nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar categorías'
            ], 500);
        }
    }

    /**
     * Obtener subcategorías por categoría
     */
    public function getSubcategoriasPorCategoria($categoriaId): JsonResponse
    {
        try {
            $subcategorias = DB::table('pro_subcategorias')
                ->where('subcategoria_idcategoria', $categoriaId)
                ->where('subcategoria_situacion', 1)
                ->orderBy('subcategoria_nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subcategorias
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar subcategorías'
            ], 500);
        }
    }

    /**
     * Obtener marcas activas
     */
    public function getMarcasActivas(): JsonResponse
    {
        try {
            $marcas = DB::table('pro_marcas')
                ->where('marca_situacion', 1)
                ->orderBy('marca_descripcion')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $marcas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar marcas'
            ], 500);
        }
    }

    /**
     * Obtener modelos por marca
     */
    public function getModelosPorMarca($marcaId): JsonResponse
    {
        try {
            $modelos = DB::table('pro_modelo')
                ->where('modelo_marca_id', $marcaId)
                ->where('modelo_situacion', 1)
                ->orderBy('modelo_descripcion')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $modelos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar modelos'
            ], 500);
        }
    }

    /**
     * Obtener calibres activos
     */
    public function getCalibresActivos(): JsonResponse
    {
        try {
            $calibres = DB::table('pro_calibres')
                ->leftJoin('pro_unidades_medida', 'pro_calibres.calibre_unidad_id', '=', 'pro_unidades_medida.unidad_id')
                ->where('pro_calibres.calibre_situacion', 1)
                ->select([
                    'pro_calibres.calibre_id',
                    'pro_calibres.calibre_nombre',
                    'pro_unidades_medida.unidad_abreviacion'
                ])
                ->orderBy('pro_calibres.calibre_nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $calibres
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar calibres'
            ], 500);
        }
    }

    // Agregar estos métodos al InventarioController existente

/**
 * Subir fotos de un producto - Optimizado con métodos del modelo
 */
public function subirFotos(Request $request, $productoId): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'fotos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $producto = Producto::findOrFail($productoId);
    
    // Verificar límite usando el scope del modelo
    $fotosExistentes = ProductoFoto::activas()
        ->where('foto_producto_id', $productoId)
        ->count();
    
    $fotosNuevas = count($request->file('fotos', []));
    
    if (($fotosExistentes + $fotosNuevas) > 5) {
        return response()->json([
            'success' => false,
            'message' => 'El producto no puede tener más de 5 fotos. Actualmente tiene ' . $fotosExistentes
        ], 422);
    }

    DB::beginTransaction();

    try {
        $fotosGuardadas = [];
        $esPrimeraFoto = $fotosExistentes === 0;
        $siguienteOrden = $fotosExistentes + 1;

        foreach ($request->file('fotos') as $index => $archivo) {
            $nombreArchivo = 'producto_' . $productoId . '_' . time() . '_' . Str::random(8) . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs('productos', $nombreArchivo, 'public');

            $foto = ProductoFoto::create([
                'foto_producto_id' => $productoId,
                'foto_url' => 'productos/' . $nombreArchivo,
                'foto_principal' => $esPrimeraFoto && $index === 0,
                'foto_orden' => $siguienteOrden + $index,
                'foto_situacion' => 1
            ]);

            $fotosGuardadas[] = [
                'foto_id' => $foto->foto_id,
                'foto_url' => $foto->url_completa, // Usar el accessor del modelo
                'foto_principal' => $foto->foto_principal
            ];
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Fotos subidas exitosamente',
            'data' => $fotosGuardadas
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Error al subir fotos: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Eliminar producto (soft delete)
 */// Método destroy mejorado con todas las validaciones necesarias

public function destroy($id): JsonResponse
{
    DB::beginTransaction();

    try {
        $producto = Producto::findOrFail($id);
        
        // VALIDACIÓN 1: Verificar stock actual
        $stockActual = StockActual::where('stock_producto_id', $id)->first();
        if ($stockActual && $stockActual->stock_cantidad_total > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un producto con stock actual (' . $stockActual->stock_cantidad_total . ' unidades)'
            ], 422);
        }

        // VALIDACIÓN 2: Verificar series registradas
        if ($producto->producto_requiere_serie) {
            $seriesActivas = SerieProducto::where('serie_producto_id', $id)
                ->where('serie_situacion', 1)
                ->whereIn('serie_estado', ['disponible', 'reservado'])
                ->count();
            
            if ($seriesActivas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un producto con series registradas (' . $seriesActivas . ' series activas)'
                ], 422);
            }
        }

        // VALIDACIÓN 3: Verificar movimientos recientes (últimos 30 días)
        $movimientosRecientes = Movimiento::where('mov_producto_id', $id)
            ->where('mov_situacion', 1)
            ->where('mov_fecha', '>=', now()->subDays(30))
            ->count();
        
        if ($movimientosRecientes > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un producto con movimientos en los últimos 30 días'
            ], 422);
        }

        // VALIDACIÓN 4: Verificar lotes asociados
        $lotes = Lote::join('pro_movimientos', 'pro_lotes.lote_id', '=', 'pro_movimientos.mov_lote_id')
            ->where('pro_movimientos.mov_producto_id', $id)
            ->where('pro_lotes.lote_situacion', 1)
            ->count();
        
        if ($lotes > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un producto asociado a lotes activos'
            ], 422);
        }

        // Si pasa todas las validaciones, proceder con eliminación
        
        // Eliminar fotos físicas
        $fotos = ProductoFoto::where('foto_producto_id', $id)->get();
        foreach ($fotos as $foto) {
            if (Storage::disk('public')->exists($foto->foto_url)) {
                Storage::disk('public')->delete($foto->foto_url);
            }
            $foto->delete();
        }

        // Eliminar precios asociados
        Precio::where('precio_producto_id', $id)->delete();

        // Eliminar stock actual
        if ($stockActual) {
            $stockActual->delete();
        }

        // Soft delete del producto (cambiar situación a 0)
        $producto->update([
            'producto_situacion' => 0,
            'producto_fecha_eliminacion' => now() // Si tienes este campo
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar producto: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Obtener fotos de un producto
 */
public function getFotosProducto($productoId): JsonResponse
{
    try {
        $fotos = ProductoFoto::where('foto_producto_id', $productoId)
            ->where('foto_situacion', 1)
            ->orderBy('foto_principal', 'desc')
            ->orderBy('foto_id')
            ->get()
            ->map(function($foto) {
                return [
                    'foto_id' => $foto->foto_id,
                    'foto_url' => asset('storage/' . $foto->foto_url),
                    'foto_principal' => $foto->foto_principal
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $fotos
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar fotos'
        ], 500);
    }
}

/**
 * Eliminar foto específica
 */
public function eliminarFoto($fotoId): JsonResponse
{
    try {
        $foto = ProductoFoto::findOrFail($fotoId);
        
        // Eliminar archivo físico
        if (Storage::disk('public')->exists($foto->foto_url)) {
            Storage::disk('public')->delete($foto->foto_url);
        }

        // Si era foto principal, establecer otra como principal
        if ($foto->foto_principal) {
            $nuevaPrincipal = ProductoFoto::where('foto_producto_id', $foto->foto_producto_id)
                ->where('foto_id', '!=', $fotoId)
                ->where('foto_situacion', 1)
                ->first();
            
            if ($nuevaPrincipal) {
                $nuevaPrincipal->update(['foto_principal' => true]);
            }
        }

        $foto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Foto eliminada exitosamente'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar foto'
        ], 500);
    }
}

/**
 * Establecer foto como principal
 */
public function establecerFotoPrincipal($fotoId): JsonResponse
{
    try {
        $foto = ProductoFoto::findOrFail($fotoId);
        
        // Quitar foto_principal de todas las demás del mismo producto
        ProductoFoto::where('foto_producto_id', $foto->foto_producto_id)
            ->where('foto_id', '!=', $fotoId)
            ->update(['foto_principal' => false]);
        
        // Establecer esta como principal
        $foto->update(['foto_principal' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Foto principal actualizada'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar foto principal'
        ], 500);
    }
}


/**
 * Procesar fotos para nuevo producto
 */
private function procesarFotosNuevoProducto($fotos, $productoId)
{
    foreach ($fotos as $index => $archivo) {
        $nombreArchivo = 'producto_' . $productoId . '_' . time() . '_' . Str::random(8) . '.' . $archivo->getClientOriginalExtension();
        $ruta = $archivo->storeAs('productos', $nombreArchivo, 'public');

        ProductoFoto::create([
            'foto_producto_id' => $productoId,
            'foto_url' => 'productos/' . $nombreArchivo,
            'foto_principal' => $index === 0, // Primera foto es principal
            'foto_situacion' => 1
        ]);
    }
}
}