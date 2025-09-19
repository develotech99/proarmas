<?php

namespace App\Http\Controllers;

use App\Models\Ventas;
use Illuminate\Http\Request;
use App\Models\MetodoPago;
use App\Models\Clientes;
use Illuminate\Support\Facades\DB;


class VentasController extends Controller
{

    public function index()
    {
        // // Datos necesarios para los selects y filtros
        $categorias = DB::table('pro_categorias')->where('categoria_situacion', 1)->get();
        $clientes = DB::table('users')->where('user_rol', 2)->get();
        $metodopago = MetodoPago::orderBy('metpago_descripcion')->paginate(15);

        return view('ventas.index', compact(
            'categorias', 'clientes', 'metodopago'
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


public function getSubcategorias($categoria_id) {
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

public function getMarcas($subcategoria_id) {
    $marcas = DB::table('pro_productos as p')
        ->join('pro_marcas as m', 'p.producto_marca_id', '=', 'm.marca_id')
        ->where('p.producto_subcategoria_id', $subcategoria_id)
        ->where('p.producto_situacion', 1)
        ->select('m.marca_id', 'm.marca_descripcion')
        ->distinct()
        ->get();
        
    return response()->json($marcas);
}

public function getModelos($marca_id) {
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

public function getCalibres($modelo_id) {
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
        ->join('pro_precios', 'producto_id', '=', 'precio_producto_id')
        ->join('pro_categorias', 'producto_categoria_id', '=', 'categoria_id')
        ->join('pro_subcategorias', 'producto_subcategoria_id', '=', 'subcategoria_id')
        ->join('pro_marcas', 'producto_marca_id', '=', 'marca_id')
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
            $q->where(function($query) use ($busqueda) {
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
            'precio_especial',
            'foto_url',
            'stock_cantidad_total'
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
        'cliente_nombre1'   => ['required','string','max:50'],
        'cliente_nombre2'   => ['nullable','string','max:50'],
        'cliente_apellido1' => ['required','string','max:50'],
        'cliente_apellido2' => ['nullable','string','max:50'],
        'cliente_dpi'       => ['nullable','string','max:20'],
        'cliente_nit'       => ['nullable','string','max:20'],
        'cliente_direccion' => ['nullable','string','max:255'],
        'cliente_telefono'  => ['nullable','string','max:30'],
        'cliente_correo'    => ['nullable','string','max:150'],
        'cliente_tipo'      => ['nullable', 'integer', 'in:0,1,2'],
    ]);
    //  echo json_encode($data);
    // exit;

    $cliente = Clientes::create($data);
    return response()->json($cliente, 201);
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
