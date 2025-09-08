<?php

namespace App\Http\Controllers;

use App\Models\Ventas;

use Illuminate\Http\Request;

use App\Models\MetodoPago;

use Illuminate\Support\Facades\DB;


class VentasController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    //   public function index()
    // {
   
    //     return view('ventas.index');
    // }

    public function index()
    {
        // // Datos necesarios para los selects y filtros
        $categorias = DB::table('pro_categorias')->where('categoria_situacion', 1)->get();
        // $subcategorias = DB::table('pro_subcategorias')->where('subcategoria_situacion', 1)->get();
        // $marcas = DB::table('pro_marcas')->where('marca_situacion', 1)->get();
        // $modelos = DB::table('pro_modelo')->where('modelo_situacion', 1)->get();
        // $calibres = DB::table('pro_calibres')->where('calibre_situacion', 1)->get();
        $clientes = DB::table('pro_clientes')->where('situacion', 1)->get();
        $metodopago = MetodoPago::orderBy('metpago_descripcion')->paginate(15);

        return view('ventas.index', compact(
            'categorias', 'clientes', 'metodopago'
        ));
    }

    public function getCalibres($modelo_id)
{
    $calibres = DB::table('pro_productos')
        ->where('producto_modelo_id', $modelo_id)
        ->where('producto_situacion', 1)
        ->distinct()
        ->pluck('producto_calibre_id', 'producto_calibre_id'); // Obtener calibres únicos

    return response()->json($calibres);
}
public function getModelos($marca_id)
{
    $modelos = DB::table('pro_productos')
        ->where('producto_marca_id', $marca_id)
        ->where('producto_situacion', 1)
        ->distinct()
        ->pluck('producto_modelo_id', 'producto_modelo_id'); // Obtener modelos únicos

    return response()->json($modelos);
}
public function getMarcas($subcategoria_id)
{
    $marcas = DB::table('pro_productos')
        ->where('producto_subcategoria_id', $subcategoria_id)
        ->where('producto_situacion', 1)
        ->distinct()
        ->pluck('producto_marca_id', 'producto_marca_id'); // Obtener marcas únicas

    return response()->json($marcas);
}
public function getSubcategorias($categoria_id)
{
    $subcategorias = DB::table('pro_productos')
        ->where('producto_categoria_id', $categoria_id)
        ->where('producto_situacion', 1)
        ->distinct()
        ->pluck('producto_subcategoria_id', 'producto_subcategoria_id'); // Obtener subcategorías únicas
    
    return response()->json($subcategorias);
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

    // Obtener los productos con los filtros aplicados
    $productos = DB::table('pro_productos')
        ->join('pro_precios', 'pro_productos.producto_id', '=', 'pro_precios.precio_producto_id')
        ->where('pro_productos.producto_categoria_id', $request->categoria_id)
        ->where('pro_productos.producto_subcategoria_id', $request->subcategoria_id)
        ->where('pro_productos.producto_marca_id', $request->marca_id)
        ->where('pro_productos.producto_modelo_id', $request->modelo_id)
        ->where('pro_productos.producto_calibre_id', $request->calibre_id)
        ->where('pro_productos.producto_situacion', 1)
        ->where('pro_precios.precio_situacion', 1)
        ->select('pro_productos.producto_id', 'pro_productos.producto_nombre', 'pro_precios.precio_venta', 'pro_productos.producto_stock')
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
