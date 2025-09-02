<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarcasController;

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
    Route::resource('usuarios', UserController::class);

    
// ruta para el manteniento de de marcas
    Route::get('/marcas', [MarcasController::class, 'index'])->name('marcas.index');
    Route::get('/marcas/search',       [MarcasController::class, 'search'])->name('marcas.search');
    Route::post('/marcas',             [MarcasController::class, 'store'])->name('marcas.store');
    Route::put('/marcas/{id}',         [MarcasController::class, 'update'])->name('marcas.update');
    // Route::delete('/marcas/{id}',      [MarcasController::class, 'destroy'])->name('marcas.destroy');

});




require __DIR__.'/auth.php';