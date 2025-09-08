<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;
use App\Models\EmpresaImportacion;
use Illuminate\Validation\Rule;

class EmpresaImportacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresas = EmpresaImportacion::conPais()
                                    ->orderBy('empresaimp_descripcion')
                                    ->get();
        
        $paises = Pais::where('pais_situacion', 1)
                     ->orderBy('pais_descripcion')
                     ->get();
        
        return view('empresas-importacion.index', compact('empresas', 'paises'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'empresaimp_descripcion' => 'required|string|max:50|unique:pro_empresas_de_importacion,empresaimp_descripcion',
            'empresaimp_pais' => 'required|exists:pro_paises,pais_id',
            'empresaimp_situacion' => 'required|integer|in:0,1',
        ], [
            'empresaimp_descripcion.required' => 'La descripción es obligatoria.',
            'empresaimp_descripcion.max' => 'La descripción no puede tener más de 50 caracteres.',
            'empresaimp_descripcion.unique' => 'Esta empresa ya existe.',
            'empresaimp_pais.required' => 'El país es obligatorio.',
            'empresaimp_pais.exists' => 'El país seleccionado no es válido.',
            'empresaimp_situacion.required' => 'El estado es obligatorio.',
            'empresaimp_situacion.in' => 'El estado debe ser activo o inactivo.',
        ]);

        try {
            EmpresaImportacion::create([
                'empresaimp_descripcion' => ucfirst(strtolower(trim($request->empresaimp_descripcion))),
                'empresaimp_pais' => $request->empresaimp_pais,
                'empresaimp_situacion' => $request->empresaimp_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Empresa de importación creada exitosamente.'
                ]);
            }

            return redirect()->route('empresas-importacion.index')
                           ->with('success', 'Empresa de importación creada exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la empresa: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al crear la empresa de importación.')
                           ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $empresa = EmpresaImportacion::findOrFail($id);

        $request->validate([
            'empresaimp_descripcion' => [
                'required', 
                'string', 
                'max:50',
                Rule::unique('pro_empresas_de_importacion', 'empresaimp_descripcion')->ignore($empresa->empresaimp_id, 'empresaimp_id')
            ],
            'empresaimp_pais' => 'required|exists:pro_paises,pais_id',
            'empresaimp_situacion' => 'required|integer|in:0,1',
        ], [
            'empresaimp_descripcion.required' => 'La descripción es obligatoria.',
            'empresaimp_descripcion.max' => 'La descripción no puede tener más de 50 caracteres.',
            'empresaimp_descripcion.unique' => 'Esta empresa ya existe.',
            'empresaimp_pais.required' => 'El país es obligatorio.',
            'empresaimp_pais.exists' => 'El país seleccionado no es válido.',
            'empresaimp_situacion.required' => 'El estado es obligatorio.',
            'empresaimp_situacion.in' => 'El estado debe ser activo o inactivo.',
        ]);

        try {
            $empresa->update([
                'empresaimp_descripcion' => ucfirst(strtolower(trim($request->empresaimp_descripcion))),
                'empresaimp_pais' => $request->empresaimp_pais,
                'empresaimp_situacion' => $request->empresaimp_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Empresa de importación actualizada exitosamente.'
                ]);
            }

            return redirect()->route('empresas-importacion.index')
                           ->with('success', 'Empresa de importación actualizada exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la empresa: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al actualizar la empresa de importación.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $empresa = EmpresaImportacion::findOrFail($id);
            
            // Verificar si tiene registros relacionados
            // Aquí puedes agregar validaciones adicionales según tu lógica de negocio
            
            $empresa->delete();

            return redirect()->route('empresas-importacion.index')
                           ->with('success', 'Empresa de importación eliminada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('empresas-importacion.index')
                           ->with('error', 'Error al eliminar la empresa. Puede tener registros relacionados.');
        }
    }

    /**
     * Search companies for AJAX and normal forms
     */
    public function search(Request $request)
    {
        $query = EmpresaImportacion::conPais();

        // Buscar por descripción
        if ($request->filled('descripcion') || $request->filled('search')) {
            $searchTerm = $request->descripcion ?: $request->search;
            $query->where('empresaimp_descripcion', 'LIKE', "%{$searchTerm}%");
        }

        // Filtrar por país
        if ($request->filled('pais')) {
            $query->where('empresaimp_pais', $request->pais);
        }

        // Filtrar por situación
        if ($request->filled('situacion')) {
            $query->where('empresaimp_situacion', $request->situacion);
        }

        $empresas = $query->orderBy('empresaimp_descripcion')
                         ->limit(20)
                         ->get();

        if ($request->ajax()) {
            return response()->json($empresas);
        }

        // Para formularios GET normales (tu funcionalidad original)
        $paises = Pais::where('pais_situacion', 1)->orderBy('pais_descripcion')->get();
        return view('empresas-importacion.index', compact('empresas', 'paises'));
    }

    /**
     * Get active companies
     */
    public function getActivas()
    {
        $empresas = EmpresaImportacion::activos()
                                    ->conPais()
                                    ->orderBy('empresaimp_descripcion')
                                    ->get();

        return response()->json($empresas);
    }

    /**
     * Get companies by country
     */
    public function getByPais($paisId)
    {
        $empresas = EmpresaImportacion::where('empresaimp_pais', $paisId)
                                    ->activos()
                                    ->conPais()
                                    ->orderBy('empresaimp_descripcion')
                                    ->get();

        return response()->json($empresas);
    }
}