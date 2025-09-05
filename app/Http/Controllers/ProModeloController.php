<?php

namespace App\Http\Controllers;

use App\Models\ProModelo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProModeloController extends Controller
{

    public function index()
    {

        $modelos = ProModelo::orderBy('modelo_descripcion')->paginate(20);
        return view('modelos.index', compact('modelos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'modelo_descripcion' => 'required|string|max:50|unique:pro_modelo,modelo_descripcion',
        ], [
            'modelo_descripcion.required' => 'La descripcion es obligatoria',
            'modelo_descripcion.max' => 'La descripcion no puede tener mas de 50 caracteres',
            'modelo_descripcion.unique' => 'Ya existe un registro con el mismo nombre, revise'
        ]);


        try {
            ProModelo::create([
                'modelo_descripcion' => trim($request->modelo_descripcion),
                'modelo_situacion' => 1
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'mensaje' => 'Modelo creado exitosamente'
                ]);
            }

            return  redirect()->route('modelos.index')
                ->with('success', 'Modelo creado exitosamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al crear el modelo: ' . $e->getMessage()
                ], 404);
            }

            return redirect()->back()
                ->with('error', 'Error al crear el modelo')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProModelo $proModelo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        // return response()->json([
        //     'success' => false,
        //     'form_data' => $request->all()
        // ], 404);

        $id = (int)$request->modelo_id;
        $modelo = ProModelo::findOrFail($id);

        $request->validate([
            'modelo_descripcion' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pro_modelo', 'modelo_descripcion')
                    ->ignore($modelo->modelo_id, 'modelo_id')
                    ->where(fn($q) => $q->where('modelo_situacion', 1))
            ],
        ], [

            'modelo_descripcion.required' => 'La descripcion es obligatoria',
            'modelo_descripcion.max' => 'La descripcion no puede tener mas de 50 caracteres.',
            'modelo_descripcion.unique' => 'Ya existe un registro con el mismo nombre.'
        ]);

        try {

            $modelo->update([
                'modelo_descripcion' => trim($request->modelo_descripcion),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'mensaje' => 'Modelo actualizado correctamente'
                ]);
            }

            return redirect()->route('modelos.index')
                ->with('success', 'modelo actualizado correctamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return  response()->json([
                    'success' => false,
                    'mensaje' => 'eror al actualizar' . $e->getMessage()
                ], 404);
            }

            return redirect()->back()
                ->with('error', 'Error al actualizar')
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProModelo $proModelo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        try {

            $id = (int) $request->modelo_id;
            $modelo = ProModelo::findOrFail($id);

            $modelo->delete();

            return redirect()->route('modelos.index')
                ->with('succes', 'Modelo eliminado correctamente');
        } catch (\Exception $e) {


            return redirect()->route('modelos.index')
                ->with('error', 'Error al eliminar el modelo, puede tener registro relacionados');
        }
    }
}
