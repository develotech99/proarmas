<?php

use App\Http\Controllers\AdminPagosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\CalibreController;
use App\Http\Controllers\CategoriasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarcasController;
use App\Http\Controllers\TipoArmaController;
use App\Http\Controllers\ProModeloController;
use App\Http\Controllers\ProLicenciaParaImportacionController;
use App\Http\Controllers\ProEmpresaDeImportacionController;
use App\Http\Controllers\PagoLicenciaController;
use App\Http\Controllers\UsersUbicacionController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ComisionesController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\ReportesController;



// Rutas para licencias de importación

Route::get('/', function () {
      return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

      Route::resource('proempresas', ProEmpresaDeImportacionController::class);

      Route::resource('prolicencias', ProLicenciaParaImportacionController::class);
      Route::redirect('/dashboard', '/prolicencias')->name('dashboard');
      Route::get('/dashboard', function () {
            return view('dashboard');
      })->name('dashboard');
      Route::resource('proempresas', ProEmpresaDeImportacionController::class);


      // En routes/web.php
      Route::get('/prolicencias/{licencia}/pagos', [PagoLicenciaController::class, 'index']);
      Route::post('/prolicencias/{licencia}/pagos', [PagoLicenciaController::class, 'store']);
      Route::get('/prolicencias/pagos/{pago}', [PagoLicenciaController::class, 'show']);
      Route::put('/prolicencias/pagos/{pago}', [PagoLicenciaController::class, 'update']);
      // Route::delete('/prolicencias/pagos/{pago}', [PagoLicenciaController::class, 'destroy']);
      Route::delete('prolicencias/pagos/{pago:pago_lic_id}', [PagoLicenciaController::class, 'destroy']);


      Route::post('prolicencias/{licencia}/upload-pdfs', [ProLicenciaParaImportacionController::class, 'uploadPdfs']);
      Route::get('prolicencias/{licencia}/documentos', [ProLicenciaParaImportacionController::class, 'listDocumentos']);
      Route::get('prolicencias/{licencia}/documentos/info', [ProLicenciaParaImportacionController::class, 'getDocumentos']);
      Route::delete('prolicencias/documento/{documento}', [ProLicenciaParaImportacionController::class, 'destroyDocumento'])
            ->name('prolicencias.documento.destroy');

      Route::get('prolicencias/comprobante/{filename}', [ProLicenciaParaImportacionController::class, 'serveComprobante'])
            ->name('prolicencias.comprobante.serve')
            ->where('filename', '[A-Za-z0-9\-_\.]+');


      Route::get('prolicencias/file/{path}', [ProLicenciaParaImportacionController::class, 'serveFile'])
            ->name('prolicencias.file.serve')
            ->where('path', '.*');

      Route::put('prolicencias/{id}/estado', [ProLicenciaParaImportacionController::class, 'updateEstado'])->name('prolicencias.updateEstado');
      Route::resource('prolicencias', ProLicenciaParaImportacionController::class);
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
      Route::get('/marcas/search', [MarcasController::class, 'search'])->name('marcas.search');
      Route::post('/marcas', [MarcasController::class, 'store'])->name('marcas.store');
      Route::put('/marcas/{id}', [MarcasController::class, 'update'])->name('marcas.update');

      // ruta para el Tipo de arma de de marcas
      Route::get('/tipoarma', [TipoArmaController::class, 'index'])->name('tipoarma.index');
      Route::get('/tipoarma/search', [TipoArmaController::class, 'search'])->name('tipoarma.search');
      Route::post('/tipoarma', [TipoArmaController::class, 'store'])->name('tipoarma.store');
      Route::put('/tipoarma/{id}', [TipoArmaController::class, 'update'])->name('tipoarma.update');

      //RUTAS PARA MODELOS DE ARMAS CarlosDevelotech
      Route::get('/modelos', [ProModeloController::class, 'index'])->name('modelos.index');
      Route::post('/modelos',             [ProModeloController::class, 'store'])->name('modelos.crear');
      Route::put('/modelos/actualizar',             [ProModeloController::class, 'edit'])->name('modelos.update');
      Route::post('/modelos', [ProModeloController::class, 'store'])->name('modelos.crear');
      Route::put('/modelos/actualizar', [ProModeloController::class, 'edit'])->name('modelos.update');
      Route::delete('/modelos/eliminar', [ProModeloController::class, 'destroy'])->name('modelos.eliminar');

      Route::get('/modelos/marcas-activas', [ProModeloController::class, 'getMarcasActivas'])->name('modelos.marcas.activas');

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

      // Detalle completo de producto
      Route::get('/inventario/productos/{id}/detalle', [InventarioController::class, 'getDetalleProducto'])
            ->name('inventario.producto.detalle');

      // Obtener detalle de producto específico (AJAX)
      Route::get('/inventario/productos/{id}', [InventarioController::class, 'getProducto'])
            ->name('inventario.producto.get');

      // Registrar nuevo producto (AJAX)
      Route::post('/inventario/productos', [InventarioController::class, 'store'])
            ->name('inventario.productos.store');

      // Actualizar producto
      Route::put('/inventario/productos/{id}', [InventarioController::class, 'update'])
            ->name('inventario.producto.update');

      // Eliminar producto
      Route::delete('/inventario/productos/{id}', [InventarioController::class, 'destroy'])
            ->name('inventario.producto.delete');

      // Movimientos de un producto específico
      Route::get('/inventario/productos/{id}/movimientos', [InventarioController::class, 'getMovimientosProducto'])
            ->name('inventario.producto.movimientos');

      // Series de un producto específico
      Route::get('/inventario/productos/{id}/series', [InventarioController::class, 'getSeriesProducto'])
            ->name('inventario.producto.series');

      // Verificar si requiere licencia
      Route::get('/inventario/productos/{id}/requiere-licencia', [InventarioController::class, 'verificarRequiereLicencia'])
            ->name('inventario.producto.requiere-licencia');

      // ================================
      // GESTIÓN DE FOTOS
      // ================================

      // Obtener fotos de producto
      Route::get('/inventario/productos/{id}/fotos', [InventarioController::class, 'getFotosProducto'])
            ->name('inventario.producto.fotos');

      // Subir fotos a producto
      Route::post('/inventario/productos/{id}/fotos', [InventarioController::class, 'subirFotos'])
            ->name('inventario.producto.fotos.subir');

      // Eliminar foto específica
      Route::delete('/inventario/fotos/{id}', [InventarioController::class, 'eliminarFoto'])
            ->name('inventario.foto.eliminar');

      // Establecer foto como principal
      Route::put('/inventario/fotos/{id}/principal', [InventarioController::class, 'establecerFotoPrincipal'])
            ->name('inventario.foto.principal');

      // ================================
      // MOVIMIENTOS E INGRESOS
      // ================================

      // Procesar ingreso a inventario (AJAX)
      Route::post('/inventario/ingresar', [InventarioController::class, 'ingresar'])
            ->name('inventario.ingresar');

      // Procesar egreso de inventario (AJAX) - NUEVA RUTA
      Route::post('/inventario/egresar', [InventarioController::class, 'egresar'])
            ->name('inventario.egresar');

      Route::get('/inventario/productos/{id}/stock-lotes', [InventarioController::class, 'getStockPorLotes'])
            ->name('inventario.producto.stock-lotes');

      Route::get('/inventario/productos/{id}/series-disponibles', [InventarioController::class, 'getSeriesDisponibles'])
            ->name('inventario.producto.series-disponibles');

      Route::get('/inventario/productos-excel', [InventarioController::class, 'getProductosExcel'])->name('inventario.productos-excel');

      // En la sección de inventario
      Route::get('/inventario/movimientos', [InventarioController::class, 'getMovimientos'])
            ->name('inventario.movimientos');
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

      // Países activos (AJAX) - CORREGIDO
      Route::get('/paises/activos', [InventarioController::class, 'getPaisesActivos'])
            ->name('paises.activos');

      // Calibres activos (AJAX)
      Route::get('/calibres/activos', [InventarioController::class, 'getCalibresActivos'])
            ->name('calibres.activos');

      Route::get('/inventario/lotes/buscar', [InventarioController::class, 'buscarLotes'])->name('inventario.lotes.buscar');
      Route::get('/inventario/lotes/{id}', [InventarioController::class, 'obtenerLote'])->name('inventario.lote.detalle');

      // ================================
      // LICENCIAS
      // ================================

      // Buscar licencias
      Route::get('/licencias/buscar', [InventarioController::class, 'buscarLicencias'])
            ->name('licencias.buscar');

      // Obtener licencia específica
      Route::get('/licencias/{id}', [InventarioController::class, 'getLicencia'])
            ->name('licencias.get');

      Route::get('/inventario/productos/{id}/precios', [InventarioController::class, 'getHistorialPrecios'])
            ->name('inventario.producto.precios');

      Route::put('/inventario/productos/{id}/precios', [InventarioController::class, 'actualizarPrecios'])
            ->name('inventario.producto.precios.actualizar');
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
      Route::post('/inventario/egreso', [InventarioController::class, 'registrarEgreso'])
            ->name('inventario.egreso');

      Route::get('/inventario/movimientos', [InventarioController::class, 'getMovimientos'])
            ->name('inventario.movimientos');


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

      // APIs para filtros en cascada
      Route::get('/api/ventas/subcategorias/{categoriaId}', [VentasController::class, 'getSubcategorias'])->name('ventas.api.subcategorias');
      Route::get('/api/ventas/marcas/{subcategoriaId}', [VentasController::class, 'getMarcas'])->name('ventas.api.marcas');
      Route::get('/api/ventas/modelos/{marcaId}', [VentasController::class, 'getModelos'])->name('ventas.api.modelos');
      Route::get('/api/ventas/calibres/{modeloId}', [VentasController::class, 'getCalibres'])->name('ventas.api.calibres');

      // RUTA QUE FALTABA - Para obtener productos
      Route::get('/api/ventas/buscar-productos', [VentasController::class, 'buscarProductos'])->name('ventas.api.productos');
      Route::get('/api/ventas/buscar', [VentasController::class, 'buscarClientes'])->name('ventas.api.clientes.buscar');
      Route::post('/api/clientes/guardar', [VentasController::class, 'guardarCliente'])->name('ventas.api.clientes.guardar');

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



      // Rutas para Comisiones (siguiendo tu patrón establecido)
      Route::get('/comisiones', [ComisionesController::class, 'index'])->name('comisiones.index');
      Route::get('/comisiones/search', [ComisionesController::class, 'search'])->name('comisiones.search');
      Route::get('/comisiones/resumen', [ComisionesController::class, 'getResumen'])->name('comisiones.resumen');
      Route::put('/comisiones', [ComisionesController::class, 'update'])->name('comisiones.update');
      Route::put('/comisiones/cancelar', [ComisionesController::class, 'cancelar'])->name('comisiones.cancelar');

      // Rutas para ubicaciones
      Route::prefix('api/ubicaciones')->name('ubicaciones.')->group(function () {
            Route::post('/', [UsersUbicacionController::class, 'create'])->name('ubi.create');
            Route::put('/', [UsersUbicacionController::class, 'update'])->name('ubi.update');
            Route::put('/{id}', [UsersUbicacionController::class, 'update'])->name('ubi.update.id'); // <- AGREGAR ESTA LÍNEA
            Route::get('/', [UsersUbicacionController::class, 'getDatos'])->name('ubi.getDatos');
            Route::get('/{user}/detalle', [UsersUbicacionController::class, 'detalle'])->name('ubi.detalle');
            Route::delete('/{id}', [UsersUbicacionController::class, 'eliminarUbicacion'])->name('ubi.delete');
      });

      Route::post('/api/ventas/procesar-venta', [VentasController::class, 'procesarVenta'])->name('ventas.api.ventas.procesar');

      //PLOTEAR USERS EN EL MAPA
      Route::get('/mapa', [UserController::class, 'indexMapa'])->name('mapa.index');

      // Rutas para Comisiones (siguiendo tu patrón establecido)
      Route::get('/comisiones', [ComisionesController::class, 'index'])->name('comisiones.index');
      Route::get('/comisiones/search', [ComisionesController::class, 'search'])->name('comisiones.search');
      Route::get('/comisiones/resumen', [ComisionesController::class, 'getResumen'])->name('comisiones.resumen');
      Route::put('/comisiones', [ComisionesController::class, 'update'])->name('comisiones.update');
      Route::put('/comisiones/cancelar', [ComisionesController::class, 'cancelar'])->name('comisiones.cancelar');

      // RUTAS PARA EL CONTROL DE PAGOS PARA ADMIN AND USER

      Route::prefix('pagos')->group(function () {
            Route::get('/', [PagosController::class, 'index'])->name('mis.pagos');
            Route::get('subir', [PagosController::class, 'index2'])->name('subir.pago');
            Route::get('admin', [PagosController::class, 'index3'])->name('admin.pagos');
            Route::get('obtener/mispagos', [PagosController::class, 'MisFacturasPendientes'])->name('misfacturas.pendientes');
            Route::post('cuotas/pagar', [PagosController::class, 'pagarCuotas']);
      });


      Route::prefix('admin/pagos')->group(function () {
            Route::get('dashboard-stats', [AdminPagosController::class, 'stats']);
            Route::post('movs/upload',   [AdminPagosController::class, 'estadoCuentaPreview']);
            Route::post('movs/procesar', [AdminPagosController::class, 'estadoCuentaProcesar']);
            Route::get('pendientes',     [AdminPagosController::class, 'pendientes']);
            Route::post('aprobar',       [AdminPagosController::class, 'aprobar']);
            Route::post('rechazar',      [AdminPagosController::class, 'rechazar']);
            Route::get('movimientos',    [AdminPagosController::class, 'movimientos']);
            Route::post('egresos',       [AdminPagosController::class, 'registrarEgreso']);
            Route::post('conciliar', [AdminPagosController::class, 'conciliarAutomatico']);
      });
});


require __DIR__ . '/auth.php';
