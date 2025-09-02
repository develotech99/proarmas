<?php

namespace App\Http\Controllers;

use App\Models\TipoArma;
use Illuminate\Http\Request;

class TipoArmaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        $tipoarma = TipoArma::all();
        return view('tipoarma.index', compact('tipoarma')); // Pasar las marcas a la vista
    }

  // Método para buscar marcas con filtros
    public function search(Request $request)
    {
        $query = TipoArma::query();

        // Buscar por descripción
        if ($request->filled('descripcion')) {
            $query->where('clase_descripcion', 'LIKE', '%' . $request->descripcion . '%');
        }

        // Filtrar por situación
        if ($request->filled('situacion')) {
            $query->where('clase_situacion', $request->situacion);
        }

        $marcas = $query->get();

        return view('tipoarma.index', compact('tipoarma'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'clase_descripcion' => 'required|string|max:255',
            'clase_situacion'   => 'required|in:1,0',  
        ]);

        TipoArma::create([
            'clase_descripcion' => $request->clase_descripcion,
            'clase_situacion'   => (int)$request->clase_situacion, // <— a int
        ]);

        return redirect()->route('tipoarma.index')->with('success', 'Tipo de Arma creada exitosamente');
    }

   public function update(Request $request, $id)
    {
        $request->validate([
            'clase_descripcion' => 'required|string|max:255',
            'clase_situacion'   => 'required|in:1,0',   // <— números
        ]);

        $marca = TipoArma::findOrFail($id);
        $marca->update([
            'clase_descripcion' => $request->clase_descripcion,
            'clase_situacion'   => (int)$request->clase_situacion, // <— a int
        ]);

        return redirect()->route('tipoarma.index')->with('success', 'Tipo de Arma actualizada exitosamente');
    }
}
