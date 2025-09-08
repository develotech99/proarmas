<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ProVendedor;
use App\Models\ProClienteVenta;
use App\Models\ProVentaPrincipal;
use App\Models\ProDetalleVenta;
use App\Models\ProPagoVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentasController extends Controller
{
    public function index()
    {
        $ventas = ProVentaPrincipal::with(['cliente', 'vendedor', 'usuarioCreacion'])
                    ->orderBy('vent_fecha', 'desc')
                    ->paginate(15);
                    
        $vendedores = ProVendedor::activos()->orderBy('vend_nombres')->get();
        $clientes = ProClienteVenta::activos()->orderBy('clie_nombre')->get();
        
        return view('ventas.index', compact('ventas', 'vendedores', 'clientes'));
    }

    public function create()
    {
        $vendedores = ProVendedor::activos()->orderBy('vend_nombres')->get();
        $clientes = ProClienteVenta::activos()->orderBy('clie_nombre')->get();
        
        return view('ventas.create', compact('vendedores', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vent_tipo' => 'required|in:cotizacion,venta',
            'vend_vendedor_id' => 'required|exists:pro_vendedores,vend_vendedor_id',
            'clie_cliente_id' => 'nullable|exists:pro_clientes_ventas,clie_cliente_id',
            'vent_cliente_nombre_temporal' => 'required_if:clie_cliente_id,null|string|max:200',
            'vent_cliente_nit_temporal' => 'nullable|string|max:15',
            'vent_cliente_telefono_temporal' => 'nullable|string|max:20',
            'vent_cliente_direccion_temporal' => 'nullable|string',
            'vent_fecha_entrega' => 'nullable|date|after:today',
            'vent_observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.prod_producto_id' => 'required|integer',
            'detalles.*.det_producto_nombre' => 'required|string|max:100',
            'detalles.*.det_cantidad' => 'required|integer|min:1',
            'detalles.*.det_precio_unitario' => 'required|numeric|min:0',
            'detalles.*.det_descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
        ], [
            'vent_tipo.required' => 'El tipo de documento es obligatorio.',
            'vend_vendedor_id.required' => 'El vendedor es obligatorio.',
            'vend_vendedor_id.exists' => 'El vendedor seleccionado no es válido.',
            'vent_cliente_nombre_temporal.required_if' => 'El nombre del cliente es obligatorio.',
            'detalles.required' => 'Debe agregar al menos un producto.',
            'detalles.*.det_cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'detalles.*.det_precio_unitario.min' => 'El precio debe ser mayor a 0.',
        ]);

        try {
            DB::beginTransaction();

            // Crear venta principal
            $venta = ProVentaPrincipal::create([
                'vent_tipo' => $request->vent_tipo,
                'vent_fecha' => now(),
                'clie_cliente_id' => $request->clie_cliente_id,
                'vent_cliente_nombre_temporal' => $request->vent_cliente_nombre_temporal,
                'vent_cliente_nit_temporal' => $request->vent_cliente_nit_temporal,
                'vent_cliente_telefono_temporal' => $request->vent_cliente_telefono_temporal,
                'vent_cliente_direccion_temporal' => $request->vent_cliente_direccion_temporal,
                'vend_vendedor_id' => $request->vend_vendedor_id,
                'vent_fecha_entrega' => $request->vent_fecha_entrega,
                'vent_observaciones' => $request->vent_observaciones,
                'vent_usuario_creacion' => Auth::id(),
            ]);

            // Crear detalles y calcular totales
            $subtotal = 0;
            foreach ($request->detalles as $detalle) {
                $cantidad = $detalle['det_cantidad'];
                $precio = $detalle['det_precio_unitario'];
                $descuentoPorcentaje = $detalle['det_descuento_porcentaje'] ?? 0;
                
                $subtotalDetalle = $cantidad * $precio;
                $descuentoMonto = $subtotalDetalle * ($descuentoPorcentaje / 100);
                $subtotalFinal = $subtotalDetalle - $descuentoMonto;

                ProDetalleVenta::create([
                    'vent_venta_id' => $venta->vent_venta_id,
                    'prod_producto_id' => $detalle['prod_producto_id'],
                    'det_producto_nombre' => $detalle['det_producto_nombre'],
                    'det_producto_marca' => $detalle['det_producto_marca'] ?? null,
                    'det_producto_modelo' => $detalle['det_producto_modelo'] ?? null,
                    'det_producto_calibre' => $detalle['det_producto_calibre'] ?? null,
                    'det_cantidad' => $cantidad,
                    'det_precio_unitario' => $precio,
                    'det_descuento_porcentaje' => $descuentoPorcentaje,
                    'det_descuento_monto' => $descuentoMonto,
                    'det_subtotal' => $subtotalFinal,
                ]);

                $subtotal += $subtotalFinal;
            }

            // Calcular impuestos (12% IVA por ejemplo)
            $impuestos = $subtotal * 0.12;
            $total = $subtotal + $impuestos;

            // Actualizar totales de la venta
            $venta->update([
                'vent_subtotal' => $subtotal,
                'vent_impuestos' => $impuestos,
                'vent_total' => $total,
                'vent_saldo_pendiente' => $total,
            ]);

            DB::commit();

            return redirect()->route('ventas.index')
                           ->with('success', ucfirst($request->vent_tipo) . ' creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al crear la venta: ' . $e->getMessage());
        }
    }

    public function show(ProVentaPrincipal $venta)
    {
        $venta->load(['cliente', 'vendedor', 'usuarioCreacion', 'detalles', 'pagos']);

        return view('ventas.show', compact('venta'));
    }

    public function edit(ProVentaPrincipal $venta)
    {
        // Solo permitir editar borradores y cotizaciones
        if (!in_array($venta->vent_estado, ['borrador', 'cotizado'])) {
            return redirect()->route('ventas.index')
                           ->with('error', 'No se puede editar una venta en estado: ' . $venta->vent_estado);
        }

        $venta->load(['detalles']);
        $vendedores = ProVendedor::activos()->orderBy('vend_nombres')->get();
        $clientes = ProClienteVenta::activos()->orderBy('clie_nombre')->get();

        return view('ventas.edit', compact('venta', 'vendedores', 'clientes'));
    }

    public function update(Request $request, ProVentaPrincipal $venta)
    {
        // Validar que se pueda editar
        if (!in_array($venta->vent_estado, ['borrador', 'cotizado'])) {
            return redirect()->route('ventas.index')
                           ->with('error', 'No se puede editar una venta en estado: ' . $venta->vent_estado);
        }

        $request->validate([
            'vent_tipo' => 'required|in:cotizacion,venta',
            'vend_vendedor_id' => 'required|exists:pro_vendedores,vend_vendedor_id',
            'clie_cliente_id' => 'nullable|exists:pro_clientes_ventas,clie_cliente_id',
            'vent_cliente_nombre_temporal' => 'required_if:clie_cliente_id,null|string|max:200',
            'vent_fecha_entrega' => 'nullable|date|after:today',
            'detalles' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Eliminar detalles existentes
            $venta->detalles()->delete();

            // Actualizar venta principal
            $venta->update([
                'vent_tipo' => $request->vent_tipo,
                'clie_cliente_id' => $request->clie_cliente_id,
                'vent_cliente_nombre_temporal' => $request->vent_cliente_nombre_temporal,
                'vent_cliente_nit_temporal' => $request->vent_cliente_nit_temporal,
                'vent_cliente_telefono_temporal' => $request->vent_cliente_telefono_temporal,
                'vent_cliente_direccion_temporal' => $request->vent_cliente_direccion_temporal,
                'vend_vendedor_id' => $request->vend_vendedor_id,
                'vent_fecha_entrega' => $request->vent_fecha_entrega,
                'vent_observaciones' => $request->vent_observaciones,
                'vent_usuario_modificacion' => Auth::id(),
            ]);

            // Recrear detalles y recalcular totales
            $subtotal = 0;
            foreach ($request->detalles as $detalle) {
                $cantidad = $detalle['det_cantidad'];
                $precio = $detalle['det_precio_unitario'];
                $descuentoPorcentaje = $detalle['det_descuento_porcentaje'] ?? 0;
                
                $subtotalDetalle = $cantidad * $precio;
                $descuentoMonto = $subtotalDetalle * ($descuentoPorcentaje / 100);
                $subtotalFinal = $subtotalDetalle - $descuentoMonto;

                ProDetalleVenta::create([
                    'vent_venta_id' => $venta->vent_venta_id,
                    'prod_producto_id' => $detalle['prod_producto_id'],
                    'det_producto_nombre' => $detalle['det_producto_nombre'],
                    'det_producto_marca' => $detalle['det_producto_marca'] ?? null,
                    'det_producto_modelo' => $detalle['det_producto_modelo'] ?? null,
                    'det_producto_calibre' => $detalle['det_producto_calibre'] ?? null,
                    'det_cantidad' => $cantidad,
                    'det_precio_unitario' => $precio,
                    'det_descuento_porcentaje' => $descuentoPorcentaje,
                    'det_descuento_monto' => $descuentoMonto,
                    'det_subtotal' => $subtotalFinal,
                ]);

                $subtotal += $subtotalFinal;
            }

            // Recalcular totales
            $impuestos = $subtotal * 0.12;
            $total = $subtotal + $impuestos;
            $saldoPendiente = $total - $venta->vent_total_pagado;

            $venta->update([
                'vent_subtotal' => $subtotal,
                'vent_impuestos' => $impuestos,
                'vent_total' => $total,
                'vent_saldo_pendiente' => $saldoPendiente,
            ]);

            DB::commit();

            return redirect()->route('ventas.index')
                           ->with('success', 'Venta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al actualizar la venta: ' . $e->getMessage());
        }
    }

    public function destroy(ProVentaPrincipal $venta)
    {
        // Solo permitir eliminar borradores
        if ($venta->vent_estado !== 'borrador') {
            return redirect()->route('ventas.index')
                           ->with('error', 'Solo se pueden eliminar ventas en borrador.');
        }

        // Verificar que no tenga pagos
        if ($venta->pagos()->count() > 0) {
            return redirect()->route('ventas.index')
                           ->with('error', 'No se puede eliminar una venta con pagos registrados.');
        }

        try {
            DB::beginTransaction();

            $venta->detalles()->delete();
            $venta->delete();

            DB::commit();

            return redirect()->route('ventas.index')
                           ->with('success', 'Venta eliminada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('ventas.index')
                           ->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado de venta
     */
    public function cambiarEstado(Request $request, ProVentaPrincipal $venta)
    {
        $request->validate([
            'nuevo_estado' => 'required|in:borrador,cotizado,confirmado,entregado,cancelado',
            'motivo_cancelacion' => 'required_if:nuevo_estado,cancelado|string|max:255',
        ]);

        $estadoActual = $venta->vent_estado;
        $nuevoEstado = $request->nuevo_estado;

        // Validar transiciones de estado permitidas
        $transicionesPermitidas = [
            'borrador' => ['cotizado', 'cancelado'],
            'cotizado' => ['confirmado', 'cancelado'],
            'confirmado' => ['entregado', 'cancelado'],
            'entregado' => [], // Estado final
            'cancelado' => [], // Estado final
        ];

        if (!in_array($nuevoEstado, $transicionesPermitidas[$estadoActual])) {
            return redirect()->back()
                           ->with('error', "No se puede cambiar de estado '$estadoActual' a '$nuevoEstado'.");
        }

        try {
            DB::beginTransaction();

            $updateData = ['vent_estado' => $nuevoEstado];

            if ($nuevoEstado === 'confirmado') {
                $updateData['vent_fecha_confirmacion'] = now();
            } elseif ($nuevoEstado === 'entregado') {
                $updateData['vent_fecha_completado'] = now();
            } elseif ($nuevoEstado === 'cancelado') {
                $updateData['vent_motivo_cancelacion'] = $request->motivo_cancelacion;
            }

            $venta->update($updateData);

            DB::commit();

            return redirect()->back()
                           ->with('success', 'Estado actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Obtener ventas para API/AJAX
     */
    public function obtenerVentas(Request $request)
    {
        $query = ProVentaPrincipal::with(['cliente', 'vendedor']);

        if ($request->filled('vendedor_id')) {
            $query->where('vend_vendedor_id', $request->vendedor_id);
        }

        if ($request->filled('estado')) {
            $query->where('vent_estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('vent_tipo', $request->tipo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('vent_fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('vent_fecha', '<=', $request->fecha_hasta);
        }

        $ventas = $query->orderBy('vent_fecha', 'desc')
                       ->paginate($request->per_page ?? 15);

        return response()->json($ventas);
    }

    /**
     * Estadísticas de ventas
     */
    public function estadisticas(Request $request)
    {
        $fechaDesde = $request->fecha_desde ?? now()->startOfMonth();
        $fechaHasta = $request->fecha_hasta ?? now()->endOfMonth();

        $stats = [
            'total_ventas' => ProVentaPrincipal::whereDate('vent_fecha', '>=', $fechaDesde)
                                              ->whereDate('vent_fecha', '<=', $fechaHasta)
                                              ->where('vent_tipo', 'venta')
                                              ->count(),
            
            'total_cotizaciones' => ProVentaPrincipal::whereDate('vent_fecha', '>=', $fechaDesde)
                                                    ->whereDate('vent_fecha', '<=', $fechaHasta)
                                                    ->where('vent_tipo', 'cotizacion')
                                                    ->count(),
            
            'ventas_confirmadas' => ProVentaPrincipal::whereDate('vent_fecha', '>=', $fechaDesde)
                                                    ->whereDate('vent_fecha', '<=', $fechaHasta)
                                                    ->where('vent_estado', 'confirmado')
                                                    ->count(),
            
            'monto_total' => ProVentaPrincipal::whereDate('vent_fecha', '>=', $fechaDesde)
                                             ->whereDate('vent_fecha', '<=', $fechaHasta)
                                             ->where('vent_tipo', 'venta')
                                             ->sum('vent_total'),
            
            'por_vendedor' => ProVentaPrincipal::select('vend_vendedor_id')
                                              ->selectRaw('COUNT(*) as total_ventas')
                                              ->selectRaw('SUM(vent_total) as monto_total')
                                              ->with('vendedor:vend_vendedor_id,vend_nombres,vend_apellidos')
                                              ->whereDate('vent_fecha', '>=', $fechaDesde)
                                              ->whereDate('vent_fecha', '<=', $fechaHasta)
                                              ->groupBy('vend_vendedor_id')
                                              ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Buscar ventas
     */
    public function search(Request $request)
    {
        $query = ProVentaPrincipal::with(['cliente', 'vendedor']);

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('vent_codigo', 'like', "%{$searchTerm}%")
                  ->orWhere('vent_cliente_nombre_temporal', 'like', "%{$searchTerm}%")
                  ->orWhereHas('cliente', function($clienteQuery) use ($searchTerm) {
                      $clienteQuery->where('clie_nombre', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $ventas = $query->orderBy('vent_fecha', 'desc')
                       ->paginate(15);

        return response()->json($ventas);
    }
}