<?php

namespace App\Http\Controllers;

use App\Models\Marcas; // Cambiado de Marcas a Marca (singular)
use Illuminate\Http\Request;

class MarcasController extends Controller
{
    public function index()
    {
        $marcas = Marcas::all();
        return view('marcas.index', compact('marcas')); // Pasar las marcas a la vista
    }

    // Método para buscar marcas con filtros
    public function search(Request $request)
    {
        $query = Marcas::query();

        // Buscar por descripción
        if ($request->filled('descripcion')) {
            $query->where('marca_descripcion', 'LIKE', '%' . $request->descripcion . '%');
        }

        // Filtrar por situación
        if ($request->filled('situacion')) {
            $query->where('marca_situacion', $request->situacion);
        }

        $marcas = $query->get();

        return view('marcas.index', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca_descripcion' => 'required|string|max:255',
            'marca_situacion'   => 'required|in:1,0',  
        ]);

        Marcas::create([
            'marca_descripcion' => $request->marca_descripcion,
            'marca_situacion'   => (int)$request->marca_situacion, // <— a int
        ]);

        return redirect()->route('marcas.index')->with('success', 'Marca creada exitosamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'marca_descripcion' => 'required|string|max:255',
            'marca_situacion'   => 'required|in:1,0',   // <— números
        ]);

        $marca = Marcas::findOrFail($id);
        $marca->update([
            'marca_descripcion' => $request->marca_descripcion,
            'marca_situacion'   => (int)$request->marca_situacion, // <— a int
        ]);

        return redirect()->route('marcas.index')->with('success', 'Marca actualizada exitosamente');
    }

}
