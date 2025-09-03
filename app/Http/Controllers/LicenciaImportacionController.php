<?php

namespace App\Http\Controllers;

use App\Models\LicenciaImportacion;
use App\Models\ArmaLicenciada;
use App\Models\EmpresaImportacion;
use App\Models\ClasePistola;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Calibre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class LicenciaImportacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $licencias = LicenciaImportacion::with(['empresa', 'armasLicenciadas'])
                                      ->withCount('armasLicenciadas')
                                      ->orderBy('lipaimp_id', 'desc')
                                      ->paginate(15);

        // Datos para los formularios
        $empresas = EmpresaImportacion::activos()->orderBy('empresaimp_nombre')->get();
        $clases = ClasePistola::activos()->orderBy('clase_descripcion')->get();
        $marcas = Marca::activos()->orderBy('marca_descripcion')->get();
        $modelos = Modelo::activos()->orderBy('modelo_descripcion')->get();
        $calibres = Calibre::activos()->with('unidadMedida')->orderBy('calibre_nombre')->get();
        
        return view('licencias-importacion.index', compact(
            'licencias', 'empresas', 'clases', 'marcas', 'modelos', 'calibres'
        ));
    }

    /**
     * Store a newly created license.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lipaimp_poliza' => 'nullable|integer',
            'lipaimp_descripcion' => 'required|string|max:100',
            'lipaimp_empresa' => 'required|integer|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_fecha_vencimiento' => 'nullable|date|after:today',
            'lipaimp_situacion' => 'required|integer|in:0,1',
        ], [
            'lipaimp_descripcion.required' => 'La descripción es obligatoria.',
            'lipaimp_descripcion.max' => 'La descripción no puede tener más de 100 caracteres.',
            'lipaimp_empresa.required' => 'La empresa es obligatoria.',
            'lipaimp_empresa.exists' => 'La empresa seleccionada no es válida.',
            'lipaimp_fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
            'lipaimp_situacion.required' => 'El estado es obligatorio.',
            'lipaimp_situacion.in' => 'El estado debe ser activo o inactivo.',
        ]);

        try {
            $licencia = LicenciaImportacion::create([
                'lipaimp_poliza' => $request->lipaimp_poliza,
                'lipaimp_descripcion' => trim($request->lipaimp_descripcion),
                'lipaimp_empresa' => $request->lipaimp_empresa,
                'lipaimp_fecha_vencimiento' => $request->lipaimp_fecha_vencimiento,
                'lipaimp_situacion' => $request->lipaimp_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Licencia de importación creada exitosamente.',
                    'licencia' => $licencia->load('empresa')
                ]);
            }

            return redirect()->route('licencias-importacion.index')
                           ->with('success', 'Licencia de importación creada exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la licencia: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al crear la licencia.')
                           ->withInput();
        }
    }

    /**
     * Update the specified license.
     */
    public function update(Request $request, $id)
    {
        $licencia = LicenciaImportacion::findOrFail($id);

        $request->validate([
            'lipaimp_poliza' => 'nullable|integer',
            'lipaimp_descripcion' => 'required|string|max:100',
            'lipaimp_empresa' => 'required|integer|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_fecha_vencimiento' => 'nullable|date|after:today',
            'lipaimp_situacion' => 'required|integer|in:0,1',
        ], [
            'lipaimp_descripcion.required' => 'La descripción es obligatoria.',
            'lipaimp_descripcion.max' => 'La descripción no puede tener más de 100 caracteres.',
            'lipaimp_empresa.required' => 'La empresa es obligatoria.',
            'lipaimp_empresa.exists' => 'La empresa seleccionada no es válida.',
            'lipaimp_fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
            'lipaimp_situacion.required' => 'El estado es obligatorio.',
            'lipaimp_situacion.in' => 'El estado debe ser activo o inactivo.',
        ]);

        try {
            $licencia->update([
                'lipaimp_poliza' => $request->lipaimp_poliza,
                'lipaimp_descripcion' => trim($request->lipaimp_descripcion),
                'lipaimp_empresa' => $request->lipaimp_empresa,
                'lipaimp_fecha_vencimiento' => $request->lipaimp_fecha_vencimiento,
                'lipaimp_situacion' => $request->lipaimp_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Licencia actualizada exitosamente.'
                ]);
            }

            return redirect()->route('licencias-importacion.index')
                           ->with('success', 'Licencia actualizada exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la licencia: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al actualizar la licencia.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified license.
     */
    public function destroy($id)
    {
        try {
            $licencia = LicenciaImportacion::findOrFail($id);
            
            // Verificar si tiene armas asociadas
            if ($licencia->armasLicenciadas()->count() > 0) {
                return redirect()->route('licencias-importacion.index')
                               ->with('error', 'No se puede eliminar la licencia porque tiene armas asociadas.');
            }

            $licencia->delete();

            return redirect()->route('licencias-importacion.index')
                           ->with('success', 'Licencia eliminada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('licencias-importacion.index')
                           ->with('error', 'Error al eliminar la licencia.');
        }
    }

    /**
     * Store a new weapon for a license.
     */
    public function storeArma(Request $request)
    {
        $request->validate([
            'arma_licencia_id' => 'required|integer|exists:pro_licencias_para_importacion,lipaimp_id',
            'arma_clase_id' => 'required|integer|exists:pro_clases_pistolas,clase_id',
            'arma_marca_id' => 'required|integer|exists:pro_marcas,marca_id',
            'arma_modelo_id' => 'required|integer|exists:pro_modelo,modelo_id',
            'arma_calibre_id' => 'required|integer|exists:pro_calibres,calibre_id',
            'arma_cantidad' => 'required|integer|min:1|max:99999',
            'arma_situacion' => 'required|integer|in:0,1',
        ], [
            'arma_licencia_id.required' => 'La licencia es obligatoria.',
            'arma_licencia_id.exists' => 'La licencia seleccionada no es válida.',
            'arma_clase_id.required' => 'La clase es obligatoria.',
            'arma_clase_id.exists' => 'La clase seleccionada no es válida.',
            'arma_marca_id.required' => 'La marca es obligatoria.',
            'arma_marca_id.exists' => 'La marca seleccionada no es válida.',
            'arma_modelo_id.required' => 'El modelo es obligatorio.',
            'arma_modelo_id.exists' => 'El modelo seleccionado no es válido.',
            'arma_calibre_id.required' => 'El calibre es obligatorio.',
            'arma_calibre_id.exists' => 'El calibre seleccionado no es válido.',
            'arma_cantidad.required' => 'La cantidad es obligatoria.',
            'arma_cantidad.min' => 'La cantidad debe ser al menos 1.',
            'arma_cantidad.max' => 'La cantidad no puede ser mayor a 99999.',
            'arma_situacion.required' => 'El estado es obligatorio.',
            'arma_situacion.in' => 'El estado debe ser activo o inactivo.',
        ]);

        try {
            $arma = ArmaLicenciada::create([
                'arma_licencia_id' => $request->arma_licencia_id,
                'arma_clase_id' => $request->arma_clase_id,
                'arma_marca_id' => $request->arma_marca_id,
                'arma_modelo_id' => $request->arma_modelo_id,
                'arma_calibre_id' => $request->arma_calibre_id,
                'arma_cantidad' => $request->arma_cantidad,
                'arma_situacion' => $request->arma_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Arma agregada exitosamente.',
                    'arma' => $arma->load(['clase', 'marca', 'modelo', 'calibre'])
                ]);
            }

            return redirect()->route('licencias-importacion.index')
                           ->with('success', 'Arma agregada exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al agregar el arma: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al agregar el arma.')
                           ->withInput();
        }
    }

    /**
     * Update weapon.
     */
    public function updateArma(Request $request, $armaId)
    {
        $arma = ArmaLicenciada::findOrFail($armaId);

        $request->validate([
            'arma_clase_id' => 'required|integer|exists:pro_clases_pistolas,clase_id',
            'arma_marca_id' => 'required|integer|exists:pro_marcas,marca_id',
            'arma_modelo_id' => 'required|integer|exists:pro_modelo,modelo_id',
            'arma_calibre_id' => 'required|integer|exists:pro_calibres,calibre_id',
            'arma_cantidad' => 'required|integer|min:1|max:99999',
            'arma_situacion' => 'required|integer|in:0,1',
        ]);

        try {
            $arma->update([
                'arma_clase_id' => $request->arma_clase_id,
                'arma_marca_id' => $request->arma_marca_id,
                'arma_modelo_id' => $request->arma_modelo_id,
                'arma_calibre_id' => $request->arma_calibre_id,
                'arma_cantidad' => $request->arma_cantidad,
                'arma_situacion' => $request->arma_situacion,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Arma actualizada exitosamente.'
                ]);
            }

            return redirect()->route('licencias-importacion.index')
                           ->with('success', 'Arma actualizada exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el arma: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Error al actualizar el arma.');
        }
    }

    /**
     * Remove weapon.
     */
    public function destroyArma($armaId)
    {
        try {
            $arma = ArmaLicenciada::findOrFail($armaId);
            $arma->delete();

            return response()->json([
                'success' => true,
                'message' => 'Arma eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el arma.'
            ], 500);
        }
    }

    /**
     * Get license details with weapons.
     */
    public function show($id)
    {
        $licencia = LicenciaImportacion::with([
                'empresa', 
                'armasLicenciadas.clase', 
                'armasLicenciadas.marca', 
                'armasLicenciadas.modelo', 
                'armasLicenciadas.calibre'
            ])
            ->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'licencia' => $licencia
            ]);
        }

        return response()->json($licencia);
    }
}