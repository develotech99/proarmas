<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientesController extends Controller
{
    public function index()
    {
        $clientes = Clientes::where('cliente_situacion', 1)
                           ->orderBy('cliente_id', 'desc')
                           ->paginate(20);
        
        // Para uso en JavaScript
        $clientesData = $clientes->getCollection()->map(function($cliente) {
            return [
                'cliente_id' => $cliente->cliente_id,
                'cliente_nombre1' => $cliente->cliente_nombre1,
                'cliente_nombre2' => $cliente->cliente_nombre2,
                'cliente_apellido1' => $cliente->cliente_apellido1,
                'cliente_apellido2' => $cliente->cliente_apellido2,
                'cliente_dpi' => $cliente->cliente_dpi,
                'cliente_nit' => $cliente->cliente_nit,
                'cliente_direccion' => $cliente->cliente_direccion,
                'cliente_telefono' => $cliente->cliente_telefono,
                'cliente_correo' => $cliente->cliente_correo,
                'cliente_tipo' => $cliente->cliente_tipo,
                'cliente_situacion' => $cliente->cliente_situacion,
                'cliente_user_id' => $cliente->cliente_user_id,
                'cliente_nom_empresa' => $cliente->cliente_nom_empresa,
                'cliente_nom_vendedor' => $cliente->cliente_nom_vendedor,
                'cliente_cel_vendedor' => $cliente->cliente_cel_vendedor,
                'cliente_ubicacion' => $cliente->cliente_ubicacion,
                'nombre_completo' => trim($cliente->cliente_nombre1 . ' ' . 
                                         ($cliente->cliente_nombre2 ?? '') . ' ' . 
                                         $cliente->cliente_apellido1 . ' ' . 
                                         ($cliente->cliente_apellido2 ?? '')),
                'created_at' => $cliente->created_at,
            ];
        });

        // Obtener usuarios premium para el select
        $usuariosPremium = DB::table('users')->where('user_rol', 2)->get();

        // Estadísticas para KPIs
        $stats = [
            'total' => Clientes::where('cliente_situacion', 1)->count(),
            'normales' => Clientes::where('cliente_situacion', 1)->where('cliente_tipo', 1)->count(),
            'premium' => Clientes::where('cliente_situacion', 1)->where('cliente_tipo', 2)->count(),
            'empresas' => Clientes::where('cliente_situacion', 1)->where('cliente_tipo', 3)->count(),
            'este_mes' => Clientes::where('cliente_situacion', 1)
                                 ->whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count(),
        ];

        return view('clientes.index', compact('clientes', 'clientesData', 'usuariosPremium', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cliente_nombre1' => ['required', 'string', 'max:50'],
                'cliente_nombre2' => ['nullable', 'string', 'max:50'],
                'cliente_apellido1' => ['required', 'string', 'max:50'],
                'cliente_apellido2' => ['nullable', 'string', 'max:50'],
                'cliente_dpi' => ['nullable', 'string', 'max:20'],
                'cliente_nit' => ['nullable', 'string', 'max:20'],
                'cliente_direccion' => ['nullable', 'string', 'max:255'],
                'cliente_telefono' => ['nullable', 'string', 'max:30'],
                'cliente_correo' => ['nullable', 'email', 'max:150'],
                'cliente_tipo' => ['required', 'integer', 'in:1,2,3'],
                'cliente_user_id' => ['nullable', 'integer'],
                'cliente_nom_empresa' => ['nullable', 'string', 'max:255'],
                'cliente_nom_vendedor' => ['nullable', 'string', 'max:255'],
                'cliente_cel_vendedor' => ['nullable', 'string', 'max:30'],
                'cliente_ubicacion' => ['nullable', 'string', 'max:255'],
            ], [
                'cliente_nombre1.required' => 'El primer nombre es obligatorio',
                'cliente_apellido1.required' => 'El primer apellido es obligatorio',
                'cliente_tipo.required' => 'El tipo de cliente es obligatorio',
                'cliente_tipo.in' => 'El tipo de cliente no es válido',
                'cliente_correo.email' => 'El correo electrónico no es válido',
            ]);

            // Asegurar situación activa
            $validated['cliente_situacion'] = 1;
            
            // Limpiar cliente_user_id si está vacío
            if (!isset($validated['cliente_user_id']) || $validated['cliente_user_id'] === '') {
                $validated['cliente_user_id'] = null;
            }

            // Si no es tipo empresa (3), limpiar campos de empresa
            if ($validated['cliente_tipo'] != 3) {
                $validated['cliente_nom_empresa'] = null;
                $validated['cliente_nom_vendedor'] = null;
                $validated['cliente_cel_vendedor'] = null;
                $validated['cliente_ubicacion'] = null;
            }

            $cliente = Clientes::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'mensaje' => 'Cliente creado exitosamente',
                    'data' => $cliente
                ], 201);
            }

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente creado exitosamente');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', ['errors' => $e->errors()]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error al crear cliente:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al crear el cliente',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al crear el cliente')
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $id = (int)$request->cliente_id;
            
            if (!$id) {
                throw new \Exception('ID de cliente no proporcionado');
            }

            $cliente = Clientes::findOrFail($id);

            $validated = $request->validate([
                'cliente_nombre1' => ['required', 'string', 'max:50'],
                'cliente_nombre2' => ['nullable', 'string', 'max:50'],
                'cliente_apellido1' => ['required', 'string', 'max:50'],
                'cliente_apellido2' => ['nullable', 'string', 'max:50'],
                'cliente_dpi' => ['nullable', 'string', 'max:20'],
                'cliente_nit' => ['nullable', 'string', 'max:20'],
                'cliente_direccion' => ['nullable', 'string', 'max:255'],
                'cliente_telefono' => ['nullable', 'string', 'max:30'],
                'cliente_correo' => ['nullable', 'email', 'max:150'],
                'cliente_tipo' => ['required', 'integer', 'in:1,2,3'],
                'cliente_user_id' => ['nullable', 'integer'],
                'cliente_nom_empresa' => ['nullable', 'string', 'max:255'],
                'cliente_nom_vendedor' => ['nullable', 'string', 'max:255'],
                'cliente_cel_vendedor' => ['nullable', 'string', 'max:30'],
                'cliente_ubicacion' => ['nullable', 'string', 'max:255'],
            ], [
                'cliente_nombre1.required' => 'El primer nombre es obligatorio',
                'cliente_apellido1.required' => 'El primer apellido es obligatorio',
                'cliente_tipo.required' => 'El tipo de cliente es obligatorio',
                'cliente_correo.email' => 'El correo electrónico no es válido',
            ]);

            // Limpiar cliente_user_id si está vacío
            if (!isset($validated['cliente_user_id']) || $validated['cliente_user_id'] === '') {
                $validated['cliente_user_id'] = null;
            }

            // Si no es tipo empresa (3), limpiar campos de empresa
            if ($validated['cliente_tipo'] != 3) {
                $validated['cliente_nom_empresa'] = null;
                $validated['cliente_nom_vendedor'] = null;
                $validated['cliente_cel_vendedor'] = null;
                $validated['cliente_ubicacion'] = null;
            }

            $cliente->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'mensaje' => 'Cliente actualizado correctamente',
                    'data' => $cliente
                ]);
            }

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente actualizado correctamente');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente:', [
                'message' => $e->getMessage(),
                'cliente_id' => $request->cliente_id ?? 'N/A'
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al actualizar: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al actualizar el cliente')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Request $request)
    {
        try {
            $id = (int) $request->cliente_id;
            
            if (!$id) {
                throw new \Exception('ID de cliente no proporcionado');
            }

            $cliente = Clientes::findOrFail($id);

            // Soft delete - cambiar situación a 0
            $cliente->update(['cliente_situacion' => 0]);

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente eliminado correctamente');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar cliente:', [
                'message' => $e->getMessage(),
                'cliente_id' => $request->cliente_id ?? 'N/A'
            ]);

            return redirect()->route('clientes.index')
                ->with('error', 'Error al eliminar el cliente');
        }
    }
}