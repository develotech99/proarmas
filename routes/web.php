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

Route::middleware('auth')->group(function () {
    Route::resource('proempresas', ProEmpresaDeImportacionController::class);

    Route::resource('prolicencias', ProLicenciaParaImportacionController::class);
      Route::redirect('/dashboard', '/prolicencias')->name('dashboard');

    
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




    // ================================
    // INVENTARIO - RUTAS PRINCIPALES
    // ================================
    
    // Vista principal del inventario
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    
    // ================================
    // PRODUCTOS
    // ================================
    
    // Obtener productos con stock (AJAX)
    Route::get('/inventario/productos-stock', [InventarioController::class, 'getProductosStock'])
          ->name('inventario.productos-stock');
    
    // Buscar productos (AJAX)
    Route::get('/inventario/buscar-productos', [InventarioController::class, 'buscarProductos'])
          ->name('inventario.buscar-productos');
    
    // Obtener detalle de producto específico (AJAX)
    Route::get('/inventario/productos/{id}', [InventarioController::class, 'getProducto'])
          ->name('inventario.producto.detalle');
    
    // Registrar nuevo producto (AJAX)
    Route::post('/inventario/productos', [InventarioController::class, 'store'])
          ->name('inventario.productos.store');
    
    // ================================
    // MOVIMIENTOS E INGRESOS
    // ================================
    
    // Procesar ingreso a inventario (AJAX)
    Route::post('/inventario/ingresar', [InventarioController::class, 'ingresar'])
          ->name('inventario.ingresar');
    
    // ================================
    // ESTADÍSTICAS Y DASHBOARDS
    // ================================
    
    // Obtener estadísticas del inventario (AJAX)
    Route::get('/inventario/estadisticas', [InventarioController::class, 'getEstadisticas'])
          ->name('inventario.estadisticas');
    
    // Obtener alertas de stock (AJAX)
    Route::get('/inventario/alertas-stock', [InventarioController::class, 'getAlertasStock'])
          ->name('inventario.alertas-stock');
    
    // ================================
    // APIS PARA SELECTS Y FORMULARIOS
    // ================================
    
    // Categorías activas (AJAX)
    Route::get('/categorias/activas', [InventarioController::class, 'getCategoriasActivas'])
          ->name('categorias.activas');
    
    // Subcategorías por categoría (AJAX)
    Route::get('/categorias/{categoria}/subcategorias', [InventarioController::class, 'getSubcategoriasPorCategoria'])
          ->name('categorias.subcategorias');
    
    // Marcas activas (AJAX)
    Route::get('/marcas/activas', [InventarioController::class, 'getMarcasActivas'])
          ->name('marcas.activas');
    
    // Modelos por marca (AJAX)
    Route::get('/marcas/{marca}/modelos', [InventarioController::class, 'getModelosPorMarca'])
          ->name('marcas.modelos');
   
    Route::get('paises/activos', [InventarioController::class, 'getPaisesActivos']);
    // Calibres activos (AJAX)
    Route::get('/calibres/activos', [InventarioController::class, 'getCalibresActivos'])
          ->name('calibres.activos');
  
Route::get('/inventario/productos/{id}/requiere-licencia', [InventarioController::class, 'verificarRequiereLicencia']);

          Route::get('productos/{id}/fotos', [InventarioController::class, 'getFotosProducto']);
          Route::post('productos/{id}/fotos', [InventarioController::class, 'subirFotos']);
          Route::delete('fotos/{id}', [InventarioController::class, 'eliminarFoto']);
          Route::put('fotos/{id}/principal', [InventarioController::class, 'establecerFotoPrincipal']);
          
          Route::delete('productos/{id}', [InventarioController::class, 'destroy']);


      Route::get('/licencias/buscar', [InventarioController::class, 'buscarLicencias']);
      Route::get('/licencias/{id}', [InventarioController::class, 'getLicencia']);


      // Detalle completo de producto
            Route::get('/inventario/productos/{id}/detalle', [InventarioController::class, 'getDetalleProducto'])
            ->name('inventario.producto.detalle');

            // Actualizar producto
            Route::put('/inventario/productos/{id}', [InventarioController::class, 'update'])
            ->name('inventario.producto.update');

            // Movimientos de un producto específico
            Route::get('/inventario/productos/{id}/movimientos', [InventarioController::class, 'getMovimientosProducto'])
            ->name('inventario.producto.movimientos');

            // Series de un producto específico
            Route::get('/inventario/productos/{id}/series', [InventarioController::class, 'getSeriesProducto'])
            ->name('inventario.producto.series');
    // ================================
    // RUTAS ADICIONALES (ESTO ESTA QAP 73)
    // ================================
    
    // Rutas para gestión de egresos
    /*
    Route::post('/inventario/egreso', [InventarioController::class, 'registrarEgreso'])
          ->name('inventario.egreso');
    
    Route::get('/inventario/movimientos', [InventarioController::class, 'getMovimientos'])
          ->name('inventario.movimientos');
    */
    
    // Rutas para reportes
    /*
    Route::get('/inventario/reportes/stock', [InventarioController::class, 'reporteStock'])
          ->name('inventario.reportes.stock');
    
    Route::get('/inventario/reportes/movimientos', [InventarioController::class, 'reporteMovimientos'])
          ->name('inventario.reportes.movimientos');
    */
    
    // Rutas para gestión de series
    /*
    Route::get('/inventario/series/{producto}', [InventarioController::class, 'getSeriesProducto'])
          ->name('inventario.series');
    
    Route::post('/inventario/series/cambiar-estado', [InventarioController::class, 'cambiarEstadoSerie'])
          ->name('inventario.series.cambiar-estado');
    */
    
    // Rutas para gestión de alertas
    /*
    Route::post('/inventario/alertas/{alerta}/marcar-vista', [InventarioController::class, 'marcarAlertaVista'])
          ->name('inventario.alertas.marcar-vista');
    
    Route::post('/inventario/alertas/{alerta}/resolver', [InventarioController::class, 'resolverAlerta'])
          ->name('inventario.alertas.resolver');
    */






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