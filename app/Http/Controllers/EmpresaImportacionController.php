<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;
use App\Models\EmpresaImportacion;

class EmpresaImportacionController extends Controller
{
    public function index()
    {
        $empresas = EmpresaImportacion::conPais()->get();
        $paises = Pais::where('pais_situacion', 1)->orderBy('pais_descripcion')->get();
        return view('empresas-importacion.index', compact('empresas', 'paises'));
    }

    // Método para buscar empresas con filtros
    public function search(Request $request)
    {
        $query = EmpresaImportacion::conPais();

        // Buscar por descripción
        if ($request->filled('descripcion')) {
            $query->where('empresaimp_descripcion', 'LIKE', '%' . $request->descripcion . '%');
        }

        // Filtrar por país
        if ($request->filled('pais')) {
            $query->where('empresaimp_pais', $request->pais);
        }

        // Filtrar por situación
        if ($request->filled('situacion')) {
            $query->where('empresaimp_situacion', $request->situacion);
        }

        $empresas = $query->get();
        $paises = Pais::where('pais_situacion', 1)->orderBy('pais_descripcion')->get();

        return view('empresas-importacion.index', compact('empresas', 'paises'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresaimp_descripcion' => 'required|string|max:50',
            'empresaimp_pais' => 'required|exists:pro_paises,pais_id',
            'empresaimp_situacion' => 'required|in:1,0',
        ], [
            'empresaimp_descripcion.required' => 'La descripción es obligatoria.',
            'empresaimp_descripcion.max' => 'La descripción no puede exceder 50 caracteres.',
            'empresaimp_pais.required' => 'El país es obligatorio.',
            'empresaimp_pais.exists' => 'El país seleccionado no es válido.',
            'empresaimp_situacion.required' => 'La situación es obligatoria.',
            'empresaimp_situacion.in' => 'La situación debe ser activa o inactiva.',
        ]);

        EmpresaImportacion::create([
            'empresaimp_descripcion' => $request->empresaimp_descripcion,
            'empresaimp_pais' => (int)$request->empresaimp_pais,
            'empresaimp_situacion' => (int)$request->empresaimp_situacion,
        ]);

        return redirect()->route('empresas-importacion.index')
                        ->with('success', 'Empresa de importación creada exitosamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'empresaimp_descripcion' => 'required|string|max:50',
            'empresaimp_pais' => 'required|exists:pro_paises,pais_id',
            'empresaimp_situacion' => 'required|in:1,0',
        ], [
            'empresaimp_descripcion.required' => 'La descripción es obligatoria.',
            'empresaimp_descripcion.max' => 'La descripción no puede exceder 50 caracteres.',
            'empresaimp_pais.required' => 'El país es obligatorio.',
            'empresaimp_pais.exists' => 'El país seleccionado no es válido.',
            'empresaimp_situacion.required' => 'La situación es obligatoria.',
            'empresaimp_situacion.in' => 'La situación debe ser activa o inactiva.',
        ]);

        $empresa = EmpresaImportacion::findOrFail($id);
        $empresa->update([
            'empresaimp_descripcion' => $request->empresaimp_descripcion,
            'empresaimp_pais' => (int)$request->empresaimp_pais,
            'empresaimp_situacion' => (int)$request->empresaimp_situacion,
        ]);

        return redirect()->route('empresas-importacion.index')
                        ->with('success', 'Empresa de importación actualizada exitosamente');
    }

    public function destroy($id)
    {
        try {
            $empresa = EmpresaImportacion::findOrFail($id);
            
            // Verificar si tiene registros relacionados (licencias, etc.)
            // Aquí puedes agregar validaciones adicionales según tu lógica de negocio
            
            $empresa->delete();

            return redirect()->route('empresas-importacion.index')
                            ->with('success', 'Empresa de importación eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('empresas-importacion.index')
                            ->with('error', 'No se pudo eliminar la empresa. Puede tener registros relacionados.');
        }
    }

    /**
     * Obtener empresas activas para select/API
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
     * Obtener empresas por país
     */
    public function getByPais($paisId)
    {
        $empresas = EmpresaImportacion::where('empresaimp_pais', $paisId)
                                    ->activos()
                                    ->orderBy('empresaimp_descripcion')
                                    ->get();

        return response()->json($empresas);
    }
}