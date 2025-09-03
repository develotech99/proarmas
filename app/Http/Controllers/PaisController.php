<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paises = Pais::orderBy('pais_descripcion')->paginate(15);
        
        return view('paises.index', compact('paises'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pais_descripcion' => 'required|string|max:50|unique:pro_paises,pais_descripcion',
            'pais_situacion' => 'required|integer|in:0,1',
        ], [
            'pais_descripcion.required' => 'La descripción del país es obligatoria.',
            'pais_descripcion.max' => 'La descripción no puede tener más de 50 caracteres.',
            'pais_descripcion.unique' => 'Este país ya existe.',
            'pais_situacion.required' => 'El estado es obligatorio.',
            'pais_situacion.in' => 'El estado debe ser activo o inactivo.',
        ]);

        try {
            Pais::create([
                'pais_descripcion' => ucwords(strtolower(trim($request->pais_descripcion))),
                'pais_situacion' => $request->pais_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'País creado exitosamente.'
                ]);
            }

            return redirect()->route('paises.index')
                           ->with('success', 'País creado exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el país: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al crear el país.')
                           ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pais = Pais::findOrFail($id);

        $request->validate([
            'pais_descripcion' => [
                'required', 
                'string', 
                'max:50',
                Rule::unique('pro_paises', 'pais_descripcion')->ignore($pais->pais_id, 'pais_id')
            ],
            'pais_situacion' => 'required|integer|in:0,1',
        ], [
            'pais_descripcion.required' => 'La descripción del país es obligatoria.',
            'pais_descripcion.max' => 'La descripción no puede tener más de 50 caracteres.',
            'pais_descripcion.unique' => 'Este país ya existe.',
            'pais_situacion.required' => 'El estado es obligatorio.',
            'pais_situacion.in' => 'El estado debe ser activo o inactivo.',
        ]);

        try {
            $pais->update([
                'pais_descripcion' => ucwords(strtolower(trim($request->pais_descripcion))),
                'pais_situacion' => $request->pais_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'País actualizado exitosamente.'
                ]);
            }

            return redirect()->route('paises.index')
                           ->with('success', 'País actualizado exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el país: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al actualizar el país.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $pais = Pais::findOrFail($id);
            
            // Verificar si tiene registros relacionados (aquí puedes agregar tus validaciones)
            // Por ejemplo: if ($pais->clientes()->count() > 0) { ... }

            $pais->delete();

            return redirect()->route('paises.index')
                           ->with('success', 'País eliminado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('paises.index')
                           ->with('error', 'Error al eliminar el país. Puede tener registros relacionados.');
        }
    }

    /**
     * Search countries for AJAX
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        $paises = Pais::when($search, function ($query) use ($search) {
                return $query->where('pais_descripcion', 'LIKE', "%{$search}%");
            })
            ->orderBy('pais_descripcion')
            ->limit(20)
            ->get();

        return response()->json($paises);
    }

    /**
     * Get active countries
     */
    public function getActivos()
    {
        $paises = Pais::activos()
                     ->orderBy('pais_descripcion')
                     ->get();

        return response()->json($paises);
    }
}