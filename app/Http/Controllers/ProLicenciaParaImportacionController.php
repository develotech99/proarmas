<?php

namespace App\Http\Controllers;

use App\Models\ProLicenciaParaImportacion;
use App\Models\ProEmpresaDeImportacion; // Importa el modelo de empresas de importación
use Illuminate\Http\Request;

class ProLicenciaParaImportacionController extends Controller
{
    /**
     * Mostrar todas las licencias de importación
     */
public function index(Request $request)
{
    $query = ProLicenciaParaImportacion::query();

    // Filtros opcionales
    if ($request->has('descripcion')) {
        $query->where('lipaimp_descripcion', 'like', '%' . $request->descripcion . '%');
    }

    if ($request->has('situacion')) {
        $query->where('lipaimp_situacion', $request->situacion);
    }

    // Paginación
    $licencias = $query->paginate(10); // Pagina las licencias con 10 por página

    return view('prolicencias.index', compact('licencias'));
}


    /**
     * Mostrar el formulario para crear una nueva licencia
     */
// Método para crear nueva licencia
public function create()
{
    // Cargar las empresas de importación
    $empresas = ProEmpresaDeImportacion::all(); 

    // Pasar las empresas a la vista
    return view('prolicencias.create', compact('empresas'));
}

// Método para editar una licencia
public function edit($id)
{
    $licencia = ProLicenciaParaImportacion::findOrFail($id);
    $empresas = ProEmpresaDeImportacion::all(); // Cargar las empresas disponibles
    return view('prolicencias.edit', compact('licencia', 'empresas'));
}

    /**
     * Almacenar una nueva licencia
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lipaimp_poliza' => 'nullable|integer',
            'lipaimp_descripcion' => 'nullable|string|max:100',
            'lipaimp_empresa' => 'required|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_fecha_vencimiento' => 'nullable|date',
            'lipaimp_situacion' => 'required|integer|in:1,2,3', // 1 = pendiente, 2 = autorizado, 3 = rechazado
        ]);

        ProLicenciaParaImportacion::create($validated);

        return redirect()->route('prolicencias.index')->with('success', 'Licencia de importación creada exitosamente.');
    }

    /**
     * Mostrar los detalles de una licencia
     */
    public function show($id)
    {
        $licencia = ProLicenciaParaImportacion::findOrFail($id);
        return view('prolicencias.show', compact('licencia'));
    }

    /**
     * Mostrar el formulario para editar una licencia existente
     */
    /**
     * Actualizar una licencia
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'lipaimp_poliza' => 'nullable|integer',
            'lipaimp_descripcion' => 'nullable|string|max:100',
            'lipaimp_empresa' => 'required|exists:pro_empresas_de_importacion,empresaimp_id',
            'lipaimp_fecha_vencimiento' => 'nullable|date',
            'lipaimp_situacion' => 'required|integer|in:1,2,3', // 1 = pendiente, 2 = autorizado, 3 = rechazado
        ]);

        $licencia = ProLicenciaParaImportacion::findOrFail($id);
        $licencia->update($validated);

        return redirect()->route('prolicencias.index')->with('success', 'Licencia de importación actualizada exitosamente.');
    }

    /**
     * Eliminar una licencia
     */
    public function destroy($id)
    {
        $licencia = ProLicenciaParaImportacion::findOrFail($id);
        $licencia->delete();

        return redirect()->route('prolicencias.index')->with('success', 'Licencia de importación eliminada exitosamente.');
    }
}
