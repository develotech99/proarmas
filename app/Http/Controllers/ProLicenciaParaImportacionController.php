<?php

namespace App\Http\Controllers;

use App\Models\ProLicenciaParaImportacion;
use App\Models\ProEmpresaDeImportacion;
use App\Models\ProModelo;

use Illuminate\Http\Request;

class ProLicenciaParaImportacionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProLicenciaParaImportacion::with(['empresa', 'modelo']);

        if ($request->filled('descripcion')) {
            $query->where('lipaimp_descripcion', 'like', '%' . $request->descripcion . '%');
        }

        if ($request->filled('situacion')) {
            $query->where('lipaimp_situacion', $request->situacion);
        }

        if ($request->filled('empresa')) {
            $query->where('lipaimp_empresa', $request->empresa);
        }

        if ($request->filled('modelo')) {
            $query->where('lipaimp_modelo', $request->modelo);
        }

        $licencias = $query->paginate(10)->withQueryString();

        $empresas = ProEmpresaDeImportacion::orderBy('empresaimp_descripcion')->get(['empresaimp_id', 'empresaimp_descripcion']);
        $modelos = ProModelo::orderBy('modelo_descripcion')->get(['modelo_id', 'modelo_descripcion']);
      

        return view('prolicencias.index', compact('licencias', 'empresas', 'modelos'));
    }

    public function create()
    {
        $empresas = ProEmpresaDeImportacion::all();
        $modelos = ProModelo::all();
    
        
        return view('prolicencias.index', compact('empresas', 'modelos'));
    }

    public function store(Request $request)
    {
         \Log::debug('Datos recibidos ANTES de validar:', $request->all());
        $validated = $request->validate([
            'lipaimp_modelo' => 'required|exists:pro_modelo,modelo_id',
            'lipaimp_largo_canon' => 'required|numeric',
            'lipaimp_poliza' => 'nullable|integer',
            'lipaimp_numero_licencia' => 'nullable|string|max:50|unique:pro_licencias_para_importacion,lipaimp_numero_licencia',
            'lipaimp_descripcion' => 'nullable|string|max:255',
            'lipaimp_empresa' => 'required|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_fecha_emision' => 'nullable|date',
            'lipaimp_fecha_vencimiento' => 'nullable|date',
            'lipaimp_observaciones' => 'nullable|string',
            'lipaimp_situacion' => 'required|integer|in:1,2,3,4,5',
            'lipaimp_cantidad_armas' => 'required|integer',
        ]);

        $licencia = ProLicenciaParaImportacion::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '¡Licencia creada exitosamente!',
                'licencia' => $licencia->load(['empresa', 'modelo'])
            ]);
        }

        return redirect()->route('prolicencias.index')->with('success', '¡Licencia creada exitosamente!');
    }

    public function show($id)
    {
        $licencia = ProLicenciaParaImportacion::with(['empresa', 'modelo'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'licencia' => $licencia
            ]);
        }

        return view('prolicencias.index', compact('licencia'));
    }

    public function edit($id)
    {
        $licencia = ProLicenciaParaImportacion::findOrFail($id);
        $empresas = ProEmpresaDeImportacion::all();
        $modelos = ProModelo::all();


        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'licencia' => $licencia,
                'empresas' => $empresas,
                'modelos' => $modelos,
              
            ]);
        }

        return view('prolicencias.index', compact('licencia', 'empresas', 'modelos'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'lipaimp_modelo' => 'required|exists:pro_modelo,modelo_id',
            'lipaimp_largo_canon' => 'required|numeric',
            'lipaimp_poliza' => 'nullable|integer',
            'lipaimp_numero_licencia' => 'nullable|string|max:50|unique:pro_licencias_para_importacion,lipaimp_numero_licencia,' . $id . ',lipaimp_id',
            'lipaimp_descripcion' => 'nullable|string|max:255',
            'lipaimp_empresa' => 'required|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_fecha_emision' => 'nullable|date',
            'lipaimp_fecha_vencimiento' => 'nullable|date',
            'lipaimp_observaciones' => 'nullable|string',
            'lipaimp_situacion' => 'required|integer|in:1,2,3,4,5',
            'lipaimp_cantidad_armas' => 'required|integer',
        ]);

        $licencia = ProLicenciaParaImportacion::findOrFail($id);
        $licencia->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '¡Licencia actualizada exitosamente!',
                'licencia' => $licencia->load(['empresa', 'modelo'])
            ]);
        }

        return redirect()->route('prolicencias.index')->with('success', '¡Licencia actualizada exitosamente!');
    }

    public function destroy($id)
    {
        try {
            $licencia = ProLicenciaParaImportacion::findOrFail($id);
            $licencia->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '¡Licencia eliminada exitosamente!'
                ]);
            }

            return redirect()->route('prolicencias.index')->with('success', '¡Licencia eliminada exitosamente!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la licencia: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('prolicencias.index')->with('error', 'Error al eliminar la licencia.');
        }
    }

    public function getLicencias(Request $request)
    {
        $query = ProLicenciaParaImportacion::with(['empresa', 'modelo']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('lipaimp_descripcion', 'like', "%{$search}%")
                  ->orWhere('lipaimp_numero_licencia', 'like', "%{$search}%");
            });
        }

        if ($request->filled('empresa')) {
            $query->where('lipaimp_empresa', $request->empresa);
        }

        if ($request->filled('modelo')) {
            $query->where('lipaimp_modelo', $request->modelo);
        }

        if ($request->filled('estado')) {
            $query->where('lipaimp_situacion', $request->estado);
        }

        $licencias = $query->get();

        return response()->json([
            'success' => true,
            'licencias' => $licencias
        ]);
    }
    
}
