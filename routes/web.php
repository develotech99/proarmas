<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PaisController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarcasController;
use App\Http\Controllers\TipoArmaController;
use App\Http\Controllers\ProModeloController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    //ruta para usuarios MarinDevelotech
    Route::resource('usuarios', UserController::class);
   
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
});





require __DIR__ . '/auth.php';
