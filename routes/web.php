<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\LicenciaImportacionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\CalibreController;
use App\Http\Controllers\CategoriasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarcasController;
use App\Http\Controllers\TipoArmaController;
use App\Http\Controllers\ProModeloController;
use App\Http\Controllers\ProLicenciaParaImportacionController;
use App\Http\Controllers\ProEmpresaDeImportacionController;
use App\Http\Controllers\VentasController;
use App\Models\ProModelo;


// Rutas para licencias de importación

Route::get('/', function () {
    return redirect()->route('login');
});

Route::prefix('licencias-importacion')->name('licencias-importacion.')->group(function () {
    Route::get('/', [LicenciaImportacionController::class, 'index'])->name('index');
    Route::get('/crear', [LicenciaImportacionController::class, 'create'])->name('create');
    Route::post('/', [LicenciaImportacionController::class, 'store'])->name('store');
    Route::get('/{id}', [LicenciaImportacionController::class, 'show'])->name('show');
    Route::get('/{id}/editar', [LicenciaImportacionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LicenciaImportacionController::class, 'update'])->name('update');
    Route::delete('/{id}', [LicenciaImportacionController::class, 'destroy'])->name('destroy');
    Route::patch('/{id}/cambiar-estado', [LicenciaImportacionController::class, 'cambiarEstado'])->name('cambiar-estado');
});
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::resource('proempresas', ProEmpresaDeImportacionController::class);




Route::middleware('auth')->group(function () {
    Route::prefix('prolicencias')->name('prolicencias.')->group(function () {
        Route::get('/', [ProLicenciaParaImportacionController::class, 'index'])->name('index');
        Route::get('create', [ProLicenciaParaImportacionController::class, 'create'])->name('create');
        Route::post('/', [ProLicenciaParaImportacionController::class, 'store'])->name('store');
        Route::get('{id}', [ProLicenciaParaImportacionController::class, 'show'])->name('show');
        Route::get('{id}/edit', [ProLicenciaParaImportacionController::class, 'edit'])->name('edit');
        Route::put('{id}', [ProLicenciaParaImportacionController::class, 'update'])->name('update');
        Route::delete('{id}', [ProLicenciaParaImportacionController::class, 'destroy'])->name('destroy');
    });
    
    
    Route::get('/api/usuarios/verificar', [UserController::class, 'verificarCorreoAPI'])->name('usuarios.verificar');
    Route::get('/confirmemail-register', [UserController::class, 'confirmEmailSucess'])->name('confirmemail.success');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //ruta para usuarios MarinDevelotech
    Route::resource('usuarios', UserController::class);
    Route::get('/api/usuarios/obtener', [UserController::class, 'getUsers'])->name('usuario.get');
    Route::post('/api/usuarios', [UserController::class, 'registroAPI'])->name('usuarios.store');
    Route::put('/api/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/api/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    Route::post('/usuarios/reenviar-verificacion', [UserController::class, 'reenviarVerificacionAPI']);

    // Rutas para métodos de pago MarinDevelotech copia a CarlosDevelotech jaja
    Route::get('/metodos-pago', [MetodoPagoController::class, 'index'])->name('metodos-pago.index');
    Route::get('/metodos-pago/search', [MetodoPagoController::class, 'search'])->name('metodos-pago.search');
    Route::post('/metodos-pago', [MetodoPagoController::class, 'store'])->name('metodos-pago.store');
    Route::put('/metodos-pago/{id}', [MetodoPagoController::class, 'update'])->name('metodos-pago.update');
    Route::delete('/metodos-pago/{id}', [MetodoPagoController::class, 'destroy'])->name('metodos-pago.destroy');

    // Rutas para países  MarinDevelotech 
    Route::get('/paises', [PaisController::class, 'index'])->name('paises.index');
    Route::get('/paises/search', [PaisController::class, 'search'])->name('paises.search');
    Route::post('/paises', [PaisController::class, 'store'])->name('paises.store');
    Route::put('/paises/{id}', [PaisController::class, 'update'])->name('paises.update');
    Route::delete('/paises/{id}', [PaisController::class, 'destroy'])->name('paises.destroy');

    // Rutas para Unidades de Medida Marin
    Route::get('/unidades-medida', [UnidadMedidaController::class, 'index'])->name('unidades-medida.index');
    Route::get('/unidades-medida/search', [UnidadMedidaController::class, 'search'])->name('unidades-medida.search');
    Route::post('/unidades-medida', [UnidadMedidaController::class, 'store'])->name('unidades-medida.store');
    Route::put('/unidades-medida/{id}', [UnidadMedidaController::class, 'update'])->name('unidades-medida.update');
    Route::delete('/unidades-medida/{id}', [UnidadMedidaController::class, 'destroy'])->name('unidades-medida.destroy');
    Route::get('/unidades-medida/activos', [UnidadMedidaController::class, 'getActivos'])->name('unidades-medida.activos');
    Route::get('/unidades-medida/por-tipo', [UnidadMedidaController::class, 'getByTipo'])->name('unidades-medida.por-tipo');

    // Rutas para Calibres Marin
    Route::get('/calibres', [CalibreController::class, 'index'])->name('calibres.index');
    Route::get('/calibres/search', [CalibreController::class, 'search'])->name('calibres.search');
    Route::post('/calibres', [CalibreController::class, 'store'])->name('calibres.store');
    Route::put('/calibres/{id}', [CalibreController::class, 'update'])->name('calibres.update');
    Route::delete('/calibres/{id}', [CalibreController::class, 'destroy'])->name('calibres.destroy');
    Route::get('/calibres/activos', [CalibreController::class, 'getActivos'])->name('calibres.activos');
    Route::get('/calibres/por-unidad', [CalibreController::class, 'getByUnidad'])->name('calibres.por-unidad');



    // Rutas para Categorías
    Route::prefix('categorias')->name('categorias.')->group(function () {
        // Rutas principales de categorías
        Route::get('/', [CategoriasController::class, 'index'])->name('index');
        Route::post('/', [CategoriasController::class, 'store'])->name('store');
        Route::put('/{categoria}', [CategoriasController::class, 'update'])->name('update');
        Route::delete('/{categoria}', [CategoriasController::class, 'destroy'])->name('destroy');

        // Rutas para subcategorías
        Route::post('/subcategorias', [CategoriasController::class, 'storeSubcategoria'])->name('subcategorias.store');
        Route::put('/subcategorias/{subcategoria}', [CategoriasController::class, 'updateSubcategoria'])->name('subcategorias.update');
        Route::delete('/subcategorias/{subcategoria}', [CategoriasController::class, 'destroySubcategoria'])->name('subcategorias.destroy');

        // Ruta auxiliar para obtener categorías activas
        Route::get('/activas', [CategoriasController::class, 'getCategoriasActivas'])->name('activas');
    });


    // ruta para el manteniento de de marcas
    Route::get('/marcas', [MarcasController::class, 'index'])->name('marcas.index');
    Route::get('/marcas/search',       [MarcasController::class, 'search'])->name('marcas.search');
    Route::post('/marcas',             [MarcasController::class, 'store'])->name('marcas.store');
    Route::put('/marcas/{id}',         [MarcasController::class, 'update'])->name('marcas.update');

    // ruta para el Tipo de arma de de marcas
    Route::get('/tipoarma', [TipoArmaController::class, 'index'])->name('tipoarma.index');
    Route::get('/tipoarma/search',       [TipoArmaController::class, 'search'])->name('tipoarma.search');
    Route::post('/tipoarma',             [TipoArmaController::class, 'store'])->name('tipoarma.store');
    Route::put('/tipoarma/{id}',         [TipoArmaController::class, 'update'])->name('tipoarma.update');


    //RUTAS PARA MODELOS DE ARMAS CarlosDevelotech
    Route::get('/modelos', [ProModeloController::class, 'index'])->name('modelos.index');
    Route::post('/modelos',             [ProModeloController::class, 'store'])->name('modelos.crear');
    Route::put('/modelos/actualizar',             [ProModeloController::class, 'edit'])->name('modelos.update');
    Route::delete('/modelos/eliminar', [ProModeloController::class, 'destroy'])->name('modelos.eliminar');

    Route::get('/modelos/marcas-activas', [ProModeloController::class, 'getMarcasActivas'])->name('modelos.marcas.activas');
    //PLOTEAR USERS EN EL MAPA
    Route::get('/mapa', [UserController::class, 'indexMapa'])->name('mapa.index');




    // Ruta principal del inventario
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

    // === APIs para el sistema de inventario ===

    // Obtener productos con stock
    Route::get('/inventario/productos-stock', [InventarioController::class, 'getProductosStock'])->name('inventario.productos-stock');

    // Ingresar nuevo producto
    Route::post('/inventario/ingresar-producto', [InventarioController::class, 'ingresarProducto'])->name('inventario.ingresar-producto');


    Route::post('/inventario/producto/ingresar', [InventarioController::class, 'ingresarProducto'])->name('inventario.producto.ingresar');
    // Registrar egreso
    Route::post('/inventario/registrar-egreso', [InventarioController::class, 'registrarEgreso'])->name('inventario.registrar-egreso');

    // Obtener movimientos
    Route::get('/inventario/movimientos', [InventarioController::class, 'getMovimientos'])->name('inventario.movimientos');

    // Obtener resumen del dashboard
    Route::get('/inventario/resumen-dashboard', [InventarioController::class, 'getResumenDashboard'])->name('inventario.resumen-dashboard');

    // Obtener subcategorías por categoría
    Route::get('/inventario/subcategorias/{categoria_id}', [InventarioController::class, 'getSubcategorias'])->name('inventario.subcategorias');

    // Detalles de un producto específico
    Route::get('/inventario/producto/{id}/detalle', [InventarioController::class, 'getDetalleProducto'])->name('inventario.producto.detalle');

    // Movimientos de un producto específico
    Route::get('/inventario/producto/{id}/movimientos', [InventarioController::class, 'getMovimientosProducto'])->name('inventario.producto.movimientos');

    // Series disponibles de un producto
    Route::get('/inventario/producto/{id}/series', [InventarioController::class, 'getSeriesProducto'])->name('inventario.producto.series');

    // Verificar si una serie está disponible
    Route::get('/inventario/verificar-serie/{numero_serie}', [InventarioController::class, 'verificarSerie'])->name('inventario.verificar-serie');

    // Exportaciones
    Route::get('/inventario/exportar-stock', [InventarioController::class, 'exportarStock'])->name('inventario.exportar-stock');
    Route::get('/inventario/exportar-movimientos', [InventarioController::class, 'exportarMovimientos'])->name('inventario.exportar-movimientos');

    // === Rutas adicionales si las necesitas ===

    // Actualizar producto
    Route::put('/inventario/producto/{id}', [InventarioController::class, 'actualizarProducto'])->name('inventario.producto.actualizar');

    // Eliminar producto (cambiar estado)
    Route::delete('/inventario/producto/{id}', [InventarioController::class, 'eliminarProducto'])->name('inventario.producto.eliminar');

    // Gestión de precios
    Route::post('/inventario/producto/{id}/precio', [InventarioController::class, 'actualizarPrecio'])->name('inventario.producto.precio');

    // Gestión de promociones
    Route::post('/inventario/producto/{id}/promocion', [InventarioController::class, 'crearPromocion'])->name('inventario.producto.promocion');
    Route::delete('/inventario/promocion/{id}', [InventarioController::class, 'eliminarPromocion'])->name('inventario.promocion.eliminar');

    // Reportes y análisis
    Route::get('/inventario/reporte-completo', [InventarioController::class, 'reporteCompleto'])->name('inventario.reporte-completo');
    Route::get('/inventario/analisis-productos', [InventarioController::class, 'analisisProductos'])->name('inventario.analisis-productos');

    // Gestión de lotes
    Route::get('/inventario/lotes', [InventarioController::class, 'getLotes'])->name('inventario.lotes');
    Route::post('/inventario/lote', [InventarioController::class, 'crearLote'])->name('inventario.lote.crear');

    // Alertas de stock
    Route::get('/inventario/alertas-stock', [InventarioController::class, 'getAlertasStock'])->name('inventario.alertas-stock');

    // Búsqueda avanzada
    Route::post('/inventario/buscar', [InventarioController::class, 'buscarProductos'])->name('inventario.buscar');

    // === Rutas para gráficas y estadísticas ===
    Route::get('/inventario/graficas/movimientos', [InventarioController::class, 'graficaMovimientos'])->name('inventario.graficas.movimientos');
    Route::get('/inventario/graficas/stock-categoria', [InventarioController::class, 'graficaStockCategoria'])->name('inventario.graficas.stock-categoria');
    Route::get('/inventario/graficas/tendencias', [InventarioController::class, 'graficaTendencias'])->name('inventario.graficas.tendencias');
    Route::get('/inventario/graficas/top-productos', [InventarioController::class, 'graficaTopProductos'])->name('inventario.graficas.top-productos'); 

           Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index'); 


Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
Route::get('/ventas/search', [VentasController::class, 'search'])->name('ventas.search');
Route::post('/ventas', [VentasController::class, 'store'])->name('ventas.store');
Route::put('/ventas/{id}', [VentasController::class, 'update'])->name('ventas.update');

// APIs para filtros en cascada
Route::get('/api/ventas/subcategorias/{categoria_id}', [VentasController::class, 'getSubcategorias'])->name('ventas.api.subcategorias');
Route::get('/api/ventas/marcas/{subcategoria_id}', [VentasController::class, 'getMarcas'])->name('ventas.api.marcas');
Route::get('/api/ventas/modelos/{marca_id}', [VentasController::class, 'getModelos'])->name('ventas.api.modelos');
Route::get('/api/ventas/calibres/{modelo_id}', [VentasController::class, 'getCalibres'])->name('ventas.api.calibres');
Route::get('/api/ventas/productos', [VentasController::class, 'getProductos'])->name('ventas.api.productos');

});





require __DIR__ . '/auth.php';
