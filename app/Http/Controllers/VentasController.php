<?php

namespace App\Http\Controllers;

use App\Models\Ventas;
use App\Models\MetodoPago;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // // Datos necesarios para los selects y filtros
        $categorias = DB::table('pro_categorias')->where('categoria_situacion', 1)->get();
        // $subcategorias = DB::table('pro_subcategorias')->where('subcategoria_situacion', 1)->get();
        // $marcas = DB::table('pro_marcas')->where('marca_situacion', 1)->get();
        // $modelos = DB::table('pro_modelo')->where('modelo_situacion', 1)->get();
        // $calibres = DB::table('pro_calibres')->where('calibre_situacion', 1)->get();
        $clientes = DB::table('users')->where('user_rol', 2)->get();
        $metodopago = MetodoPago::orderBy('metpago_descripcion')->paginate(15);

        return view('ventas.index', compact(
            'categorias', 'clientes', 'metodopago'
        ));
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


public function getProductos(Request $request)
{
    // Validación de parámetros
    $request->validate([
        'categoria_id' => 'required|integer|exists:pro_categorias,categoria_id',
        'subcategoria_id' => 'required|integer|exists:pro_subcategorias,subcategoria_id',
        'marca_id' => 'required|integer|exists:pro_marcas,marca_id',
        'modelo_id' => 'required|integer|exists:pro_modelo,modelo_id',
        'calibre_id' => 'required|integer|exists:pro_calibres,calibre_id',
    ]);

    // Obtener los productos con los filtros aplicados y concatenar información
    $productos = DB::table('pro_productos as p')
        ->join('pro_precios as pr', 'p.producto_id', '=', 'pr.precio_producto_id')
        ->join('pro_marcas as m', 'p.producto_marca_id', '=', 'm.marca_id')
        ->join('pro_modelo as mo', 'p.producto_modelo_id', '=', 'mo.modelo_id')
        ->join('pro_calibres as c', 'p.producto_calibre_id', '=', 'c.calibre_id')
        ->where('p.producto_categoria_id', $request->categoria_id)
        ->where('p.producto_subcategoria_id', $request->subcategoria_id)
        ->where('p.producto_marca_id', $request->marca_id)
        ->where('p.producto_modelo_id', $request->modelo_id)
        ->where('p.producto_calibre_id', $request->calibre_id)
        ->where('p.producto_situacion', 1)
        ->where('pr.precio_situacion', 1)
        ->select(
            'p.producto_id',
            'p.producto_nombre',
            'pr.precio_venta',
            'p.producto_stock',
            // Concatenar marca, modelo y calibre
            DB::raw("CONCAT(m.marca_nombre, ' - ', mo.modelo_nombre, ' - ', c.calibre_descripcion) as producto_completo"),
            // Información individual para el dashboard
            'm.marca_nombre',
            'mo.modelo_nombre', 
            'c.calibre_descripcion'
        )
        ->get();

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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
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
