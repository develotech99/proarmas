<?php

namespace App\Http\Controllers;

use App\Models\LicenciaParaImportacion;
use App\Models\EmpresaImportacion;
use App\Models\ClasePistola;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Calibre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenciaImportacionController extends Controller
{
    public function index(Request $request)
    {
        $query = LicenciaParaImportacion::with(['empresa', 'clase', 'marca', 'modelo', 'calibre']);

        // Filtros
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('lipaimp_poliza', 'like', '%' . $request->search . '%')
                  ->orWhere('lipaimp_descripcion', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('empresa') && $request->empresa != '') {
            $query->where('lipaimp_empresa', $request->empresa);
        }

        if ($request->has('estado') && $request->estado != '') {
            switch ($request->estado) {
                case 'activas':
                    $query->where('lipaimp_situacion', 1);
                    break;
                case 'inactivas':
                    $query->where('lipaimp_situacion', 0);
                    break;
                case 'vencidas':
                    $query->where('lipaimp_fecha_vencimiento', '<', now());
                    break;
                case 'por_vencer':
                    $query->whereBetween('lipaimp_fecha_vencimiento', [now(), now()->addDays(30)]);
                    break;
            }
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'lipaimp_id');
        $sortDirection = $request->get('direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);

        $licencias = $query->paginate(20);

        $empresas = EmpresaImportacion::where('empresaimp_situacion', 1)->get();
        $clases = ClasePistola::where('clase_situacion', 1)->get();
        $marcas = Marca::where('marca_situacion', 1)->get();
        $modelos = Modelo::where('modelo_situacion', 1)->get();
        $calibres = Calibre::where('calibre_situacion', 1)->get();

        return view('licencias-importacion.index', compact(
            'licencias', 
            'empresas', 
            'clases', 
            'marcas', 
            'modelos', 
            'calibres'
        ));
    }

    public function create()
    {
        $empresas = EmpresaImportacion::where('empresaimp_situacion', 1)->get();
        $clases = ClasePistola::where('clase_situacion', 1)->get();
        $marcas = Marca::where('marca_situacion', 1)->get();
        $modelos = Modelo::where('modelo_situacion', 1)->get();
        $calibres = Calibre::where('calibre_situacion', 1)->get();

        return view('licencias-importacion.create', compact(
            'empresas', 
            'clases', 
            'marcas', 
            'modelos', 
            'calibres'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lipaimp_poliza' => 'required|integer',
            'lipaimp_descripcion' => 'required|string|max:100',
            'lipaimp_empresa' => 'required|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_clase' => 'nullable|exists:pro_clases_pistolas,clase_id',
            'lipaimp_marca' => 'nullable|exists:pro_marcas,marca_id',
            'lipaimp_modelo' => 'nullable|exists:pro_modelo,modelo_id',
            'lipaimp_calibre' => 'nullable|exists:pro_calibres,calibre_id',
            'lipaimp_fecha_vencimiento' => 'nullable|date',
            'lipaimp_situacion' => 'required|in:0,1'
        ]);

        try {
            DB::beginTransaction();

            $licencia = LicenciaParaImportacion::create($validated);

            DB::commit();

            return redirect()->route('licencias-importacion.index')
                ->with('success', 'Licencia creada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear la licencia: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $licencia = LicenciaParaImportacion::with([
            'empresa', 
            'clase', 
            'marca', 
            'modelo', 
            'calibre',
            'digecam',
            'pagosLicencias',
            'documentacion'
        ])->findOrFail($id);

        return view('licencias-importacion.show', compact('licencia'));
    }

    public function edit($id)
    {
        $licencia = LicenciaParaImportacion::findOrFail($id);
        
        $empresas = EmpresaImportacion::where('empresaimp_situacion', 1)->get();
        $clases = ClasePistola::where('clase_situacion', 1)->get();
        $marcas = Marca::where('marca_situacion', 1)->get();
        $modelos = Modelo::where('modelo_situacion', 1)->get();
        $calibres = Calibre::where('calibre_situacion', 1)->get();

        return view('licencias-importacion.edit', compact(
            'licencia', 
            'empresas', 
            'clases', 
            'marcas', 
            'modelos', 
            'calibres'
        ));
    }

    public function update(Request $request, $id)
    {
        $licencia = LicenciaParaImportacion::findOrFail($id);

        $validated = $request->validate([
            'lipaimp_poliza' => 'required|integer',
            'lipaimp_descripcion' => 'required|string|max:100',
            'lipaimp_empresa' => 'required|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_clase' => 'nullable|exists:pro_clases_pistolas,clase_id',
            'lipaimp_marca' => 'nullable|exists:pro_marcas,marca_id',
            'lipaimp_modelo' => 'nullable|exists:pro_modelo,modelo_id',
            'lipaimp_calibre' => 'nullable|exists:pro_calibres,calibre_id',
            'lipaimp_fecha_vencimiento' => 'nullable|date',
            'lipaimp_situacion' => 'required|in:0,1'
        ]);

        try {
            DB::beginTransaction();

            $licencia->update($validated);

            DB::commit();

            return redirect()->route('licencias-importacion.index')
                ->with('success', 'Licencia actualizada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la licencia: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $licencia = LicenciaParaImportacion::findOrFail($id);

        try {
            DB::beginTransaction();

            // Verificar si tiene relaciones antes de eliminar
            if ($licencia->digecam()->exists()) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la licencia porque tiene documentación DIGECAM asociada.');
            }

            if ($licencia->pagosLicencias()->exists()) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la licencia porque tiene pagos asociados.');
            }

            $licencia->delete();

            DB::commit();

            return redirect()->route('licencias-importacion.index')
                ->with('success', 'Licencia eliminada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al eliminar la licencia: ' . $e->getMessage());
        }
    }

    public function cambiarEstado($id)
    {
        $licencia = LicenciaParaImportacion::findOrFail($id);
        
        $nuevoEstado = $licencia->lipaimp_situacion == 1 ? 0 : 1;
        $licencia->update(['lipaimp_situacion' => $nuevoEstado]);

        $estadoTexto = $nuevoEstado == 1 ? 'activada' : 'desactivada';
        
        return redirect()->back()
            ->with('success', "Licencia {$estadoTexto} exitosamente.");
    }
}