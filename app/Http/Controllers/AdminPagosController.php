<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CajaSaldo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminPagosController extends Controller
{
    /* ===========================
     * Helpers de respuesta
     * =========================== */
    private function ok(array $data = [], int $code = 200)
    {
        return response()->json(['ok' => true] + $data, $code);
    }

    private function err(string $msg, int $code = 400, array $extra = [])
    {
        return response()->json(['ok' => false, 'msg' => $msg] + $extra, $code);
    }

    /* ===========================
     * Tarjetas de dashboard
     * GET /admin/pagos/dashboard-stats
     * =========================== */
    public function stats(Request $request)
    {
        try {
            $saldos = DB::table('caja_saldos as s')
                ->join('pro_metodos_pago as m', 'm.metpago_id', '=', 's.caja_saldo_metodo_pago')
                ->select(
                    's.caja_saldo_metodo_pago as metodo_id',
                    'm.metpago_descripcion as metodo',
                    's.caja_saldo_moneda',
                    's.caja_saldo_monto_actual',
                    's.caja_saldo_actualizado'
                )
                ->orderBy('m.metpago_descripcion')
                ->get();

            $totalGTQ   = (float) $saldos->where('caja_saldo_moneda', 'GTQ')->sum('caja_saldo_monto_actual');
            $pendientes = DB::table('pro_pagos_subidos')->where('ps_estado', 'PENDIENTE_VALIDACION')->count();
            $ultimaCarga = DB::table('pro_estados_cuenta')->max('created_at');

            return $this->ok([
                'saldo_total_gtq' => $totalGTQ,
                'saldos'          => $saldos,
                'pendientes'      => $pendientes,
                'ultima_carga'    => $ultimaCarga,
            ]);
        } catch (\Throwable $e) {
            return $this->err('No se pudieron obtener las estadísticas.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Bandeja de validación
     * GET /admin/pagos/pendientes
     * =========================== */
    public function pendientes(Request $request)
    {
        try {
            $q = trim((string) $request->query('q', ''));
            $estado = (string) $request->query('estado', '');

            $rows = DB::table('pro_pagos_subidos as ps')
                ->join('pro_ventas as v', 'v.ven_id', '=', 'ps.ps_venta_id')
                ->join('pro_pagos as pg', 'pg.pago_venta_id', '=', 'v.ven_id')
                ->leftJoin('users as u', 'u.id', '=', 'ps.ps_cliente_user_id')
                ->leftJoin('pro_clientes as c', 'c.cliente_user_id', '=', 'ps.ps_cliente_user_id')
                ->select([
                    'ps.ps_id',
                    'ps.ps_venta_id',
                    'ps.ps_estado',
                    'ps.ps_referencia',
                    'ps.ps_concepto',
                    'ps.ps_imagen_path',
                    'ps.ps_monto_comprobante',
                    'ps.ps_monto_total_cuotas_front',
                    'ps.ps_diferencia',
                    'ps.created_at',
                    'v.ven_id',
                    'v.ven_fecha',
                    'v.ven_total_vendido',
                    'v.ven_observaciones',
                    'pg.pago_id',
                    'pg.pago_monto_total',
                    'pg.pago_monto_pagado',
                    'pg.pago_monto_pendiente',
                    'pg.pago_estado',
                    DB::raw("COALESCE(u.name, c.cliente_nombre, 'Cliente') as cliente"),
                ])
                ->when($estado !== '', fn($qq) => $qq->where('ps.ps_estado', $estado))
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('ps.ps_referencia', 'like', "%{$q}%")
                            ->orWhere('ps.ps_concepto', 'like', "%{$q}%")
                            ->orWhere('ven_observaciones', 'like', "%{$q}%")
                            ->orWhere('ven_id', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('ps.created_at')
                ->limit(300)
                ->get();

            // Resumen de items por venta
            $labelsAgg = DB::table('pro_detalle_ventas as d')
                ->join('pro_productos as p', 'p.producto_id', '=', 'd.det_producto_id')
                ->leftJoin('pro_marcas as ma', 'ma.marca_id', '=', 'p.producto_marca_id')
                ->leftJoin('pro_modelo as mo', 'mo.modelo_id', '=', 'p.producto_modelo_id')
                ->leftJoin('pro_calibres as ca', 'ca.calibre_id', '=', 'p.producto_calibre_id')
                ->whereIn('d.det_ven_id', $rows->pluck('ven_id')->all())
                ->select([
                    'd.det_ven_id',
                    DB::raw("TRIM(CONCAT_WS(' ', ma.marca_descripcion, mo.modelo_descripcion, p.producto_nombre, IFNULL(CONCAT('(',ca.calibre_nombre,')'), ''))) as label"),
                    DB::raw('SUM(d.det_cantidad) as qty'),
                    DB::raw('MAX(d.det_id) as ord'),
                ])
                ->groupBy('d.det_ven_id', 'label');

            $conceptoSub = DB::query()->fromSub($labelsAgg, 'x')
                ->select([
                    'x.det_ven_id',
                    DB::raw("GROUP_CONCAT(CONCAT(x.qty,' ',x.label) ORDER BY x.ord SEPARATOR ', ') as concepto_resumen"),
                    DB::raw('COUNT(*) as items_count'),
                ])
                ->groupBy('x.det_ven_id')
                ->get()
                ->keyBy('det_ven_id');

            $data = $rows->map(function ($r) use ($conceptoSub) {
                $c = $conceptoSub[$r->ven_id] ?? null;
                $debia = (float) ($r->pago_monto_pendiente ?? max($r->pago_monto_total - $r->pago_monto_pagado, 0));
                $depositado = (float) ($r->ps_monto_comprobante ?? 0);
                $dif = $depositado - $debia;

                return [
                    'ps_id'       => (int) $r->ps_id,
                    'venta_id'    => (int) $r->ven_id,
                    'fecha'       => $r->ven_fecha,
                    'cliente'     => $r->cliente,
                    'concepto'    => $c->concepto_resumen ?? '—',
                    'items_count' => (int) ($c->items_count ?? 0),
                    'debia'       => round($debia, 2),
                    'depositado'  => round($depositado, 2),
                    'diferencia'  => round($dif, 2),
                    'estado'      => $r->ps_estado,
                    'referencia'  => $r->ps_referencia,
                    'imagen'      => $r->ps_imagen_path,
                    'observaciones_venta' => $r->ven_observaciones,
                    'created_at'  => $r->created_at,
                ];
            })->values();

            return $this->ok(['data' => $data]);
        } catch (\Throwable $e) {
            return $this->err('No se pudo cargar la bandeja de pendientes.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Aprobar pago
     * POST /admin/pagos/aprobar
     * =========================== */
    public function aprobar(Request $request)
    {
        try {
            $data = $request->validate([
                'ps_id'        => ['required', 'integer', 'min:1'],
                'observaciones' => ['nullable', 'string', 'max:255'],
                'metodo_id'    => ['nullable', 'integer', 'min:1'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return $this->err('Datos inválidos.', 422, ['errors' => $ve->errors()]);
        }

        try {
            $metodoEfectivoId = (int) ($data['metodo_id'] ?? 1);

            $ps = DB::table('pro_pagos_subidos')->where('ps_id', $data['ps_id'])->first();
            if (!$ps) return $this->err('Registro no encontrado', 404);
            if ($ps->ps_estado !== 'PENDIENTE_VALIDACION') {
                return $this->err('El registro no está pendiente.', 422);
            }

            $venta = DB::table('pro_ventas as v')
                ->join('pro_pagos as pg', 'pg.pago_venta_id', '=', 'v.ven_id')
                ->select(
                    'v.ven_id',
                    'v.ven_cliente',
                    'pg.pago_id',
                    'pg.pago_estado',
                    'pg.pago_monto_total',
                    'pg.pago_monto_pagado',
                    'pg.pago_monto_pendiente'
                )
                ->where('v.ven_id', $ps->ps_venta_id)
                ->first();

            if (!$venta) return $this->err('Venta asociada no encontrada', 404);

            $monto = (float) $ps->ps_monto_comprobante;
            $fecha = $ps->ps_fecha_comprobante ?: now();

            DB::beginTransaction();

            // 1) Detalle de pago
            $detId = DB::table('pro_detalle_pagos')->insertGetId([
                'det_pago_pago_id'             => $venta->pago_id,
                'det_pago_cuota_id'            => null,
                'det_pago_fecha'               => $fecha,
                'det_pago_monto'               => $monto,
                'det_pago_metodo_pago'         => $metodoEfectivoId,
                'det_pago_banco_id'            => $ps->ps_banco_id ?? null,
                'det_pago_numero_autorizacion' => $ps->ps_referencia ?? null,
                'det_pago_imagen_boucher'      => $ps->ps_imagen_path ?? null,
                'det_pago_tipo_pago'           => 'PAGO_UNICO',
                'det_pago_estado'              => 'VALIDO',
                'det_pago_observaciones'       => $data['observaciones'] ?? $ps->ps_concepto,
                'det_pago_usuario_registro'    => auth()->id(),
                'created_at'                   => now(),
                'updated_at'                   => now(),
            ]);

            // 2) Master de pagos
            $nuevoPagado    = (float) $venta->pago_monto_pagado + $monto;
            $nuevoPendiente = max((float) $venta->pago_monto_total - $nuevoPagado, 0);
            $nuevoEstado    = $nuevoPendiente <= 0 ? 'COMPLETADO' : 'PARCIAL';

            DB::table('pro_pagos')
                ->where('pago_id', $venta->pago_id)
                ->update([
                    'pago_monto_pagado'     => $nuevoPagado,
                    'pago_monto_pendiente'  => $nuevoPendiente,
                    'pago_estado'           => $nuevoEstado,
                    'pago_fecha_completado' => $nuevoPendiente <= 0 ? now() : null,
                    'updated_at'            => now(),
                ]);

            // 3) Caja (historial)
            DB::table('cja_historial')->insert([
                'cja_tipo'          => 'DEPOSITO',
                'cja_id_venta'      => $venta->ven_id,
                'cja_usuario'       => auth()->id(),
                'cja_monto'         => $monto,
                'cja_fecha'         => now(),
                'cja_metodo_pago'   => $metodoEfectivoId,
                'cja_no_referencia' => $ps->ps_referencia ?? null,
                'cja_situacion'     => 'ACTIVO',
                'cja_observaciones' => 'Aprobación ps#' . $ps->ps_id,
                'created_at'        => now(),
            ]);

            // 4) Saldos
            CajaSaldo::ensureRow($metodoEfectivoId, 'GTQ')->addAmount($monto);

            // 5) Cambiar estado del PS
            DB::table('pro_pagos_subidos')
                ->where('ps_id', $ps->ps_id)
                ->update([
                    'ps_estado'    => 'APROBADO',
                    'ps_obs_admin' => $data['observaciones'] ?? null,
                    'updated_at'   => now(),
                ]);

            DB::commit();

            return $this->ok(['msg' => 'Pago aprobado', 'det_pago_id' => $detId]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->err('Error al aprobar.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Rechazar pago
     * POST /admin/pagos/rechazar
     * =========================== */
    public function rechazar(Request $request)
    {
        try {
            $data = $request->validate([
                'ps_id'  => ['required', 'integer', 'min:1'],
                'motivo' => ['required', 'string', 'min:5', 'max:255'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return $this->err('Datos inválidos.', 422, ['errors' => $ve->errors()]);
        }

        try {
            $ps = DB::table('pro_pagos_subidos')->where('ps_id', $data['ps_id'])->first();
            if (!$ps) return $this->err('Registro no encontrado', 404);
            if ($ps->ps_estado !== 'PENDIENTE_VALIDACION') {
                return $this->err('El registro no está pendiente.', 422);
            }

            DB::table('pro_pagos_subidos')
                ->where('ps_id', $data['ps_id'])
                ->update([
                    'ps_estado'    => 'RECHAZADO',
                    'ps_obs_admin' => $data['motivo'],
                    'updated_at'   => now(),
                ]);

            return $this->ok(['msg' => 'Pago rechazado.']);
        } catch (\Throwable $e) {
            return $this->err('No se pudo rechazar el pago.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Movimientos de caja
     * GET /admin/pagos/movimientos
     * =========================== */
    public function movimientos(Request $request)
    {
        try {
            $from = $request->query('from') ?: Carbon::now()->startOfMonth()->toDateString();
            $to   = $request->query('to')   ?: Carbon::now()->endOfMonth()->toDateString();
            $metodoId = $request->query('metodo_id');

            $q = DB::table('cja_historial as h')
                ->leftJoin('pro_metodos_pago as m', 'm.metpago_id', '=', 'h.cja_metodo_pago')
                ->select(
                    'h.cja_id',
                    'h.cja_fecha',
                    'h.cja_tipo',
                    'h.cja_no_referencia',
                    'm.metpago_descripcion as metodo',
                    'h.cja_monto',
                    'h.cja_situacion'
                )
                // Si cja_fecha es DATE, whereDate evita problemas con horas:
                ->whereDate('h.cja_fecha', '>=', $from)
                ->whereDate('h.cja_fecha', '<=', $to)
                ->when($metodoId, fn($qq) => $qq->where('h.cja_metodo_pago', $metodoId))
                ->orderBy('h.cja_fecha', 'desc');

            $rows = $q->get();

            $total = 0.0;
            foreach ($rows as $r) {
                if ($r->cja_situacion === 'ANULADO') continue;
                $total += in_array($r->cja_tipo, ['VENTA', 'DEPOSITO', 'AJUSTE_POS'])
                    ? (float) $r->cja_monto
                    : -(float) $r->cja_monto;
            }

            return $this->ok([
                'data'  => $rows,
                'total' => round($total, 2),
            ]);
        } catch (\Throwable $e) {
            return $this->err('No se pudieron cargar los movimientos.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Registrar egreso de caja
     * POST /admin/pagos/egresos
     * =========================== */
    public function registrarEgreso(Request $request)
    {
        try {
            $data = $request->validate([
                'fecha'      => ['nullable', 'date'],
                'metodo_id'  => ['required', 'integer', 'min:1'],
                'monto'      => ['required', 'numeric', 'gt:0'],
                'motivo'     => ['required', 'string', 'max:200'],
                'referencia' => ['nullable', 'string', 'max:100'],
                'archivo'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return $this->err('Datos inválidos.', 422, ['errors' => $ve->errors()]);
        }

        $path = null;

        try {
            if ($request->hasFile('archivo')) {
                $path = $request->file('archivo')->store('egresos', 'public');
            }

            DB::beginTransaction();

            DB::table('cja_historial')->insert([
                'cja_tipo'          => 'EGRESO',
                'cja_id_venta'      => null,
                'cja_id_import'     => null,
                'cja_usuario'       => auth()->id(),
                'cja_monto'         => $data['monto'],
                'cja_fecha'         => $data['fecha'] ? Carbon::parse($data['fecha']) : now(),
                'cja_metodo_pago'   => $data['metodo_id'],
                'cja_no_referencia' => $data['referencia'] ?? null,
                'cja_situacion'     => 'ACTIVO',
                'cja_observaciones' => $data['motivo'],
                'created_at'        => now(),
            ]);

            CajaSaldo::ensureRow($data['metodo_id'], 'GTQ')->subtractAmount($data['monto']);

            DB::commit();

            return $this->ok(['msg' => 'Egreso registrado', 'archivo' => $path]);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($path) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable $__) { /* noop */
                }
            }
            return $this->err('No se pudo registrar el egreso.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Upload/preview estado de cuenta
     * POST /admin/pagos/movs/upload
     * =========================== */
    public function estadoCuentaPreview(Request $request)
    {
        try {
            $request->validate([
                'archivo'  => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
                'banco_id' => ['nullable', 'integer'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return $this->err('Validación inválida.', 422, ['errors' => $ve->errors()]);
        }

        try {
            $file = $request->file('archivo');
            $path = $file->store('estados_cuenta/tmp', 'public');

            [$headers, $rows] = $this->parseSheet(storage_path('app/public/' . $path));

            return $this->ok([
                'path'    => $path,
                'headers' => $headers,
                'rows'    => array_slice($rows, 0, 50),
            ]);
        } catch (\Throwable $e) {
            return $this->err('No se pudo generar la vista previa.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Procesar estado de cuenta (guardar control)
     * POST /admin/pagos/movs/procesar
     * =========================== */
    public function estadoCuentaProcesar(Request $request)
    {
        try {
            $data = $request->validate([
                'archivo_path' => ['required', 'string'],
                'banco_id'     => ['nullable', 'integer'],
                'fecha_inicio' => ['nullable', 'date'],
                'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return $this->err('Validación inválida.', 422, ['errors' => $ve->errors()]);
        }

        try {
            $full = storage_path('app/public/' . $data['archivo_path']);
            if (!file_exists($full)) {
                return $this->err('Archivo no encontrado', 404);
            }

            [$headers, $rows] = $this->parseSheet($full);

            $ecId = DB::table('pro_estados_cuenta')->insertGetId([
                'ec_banco_id'  => $data['banco_id'] ?? null,
                'ec_archivo'   => $data['archivo_path'],
                'ec_headers'   => json_encode($headers, JSON_UNESCAPED_UNICODE),
                'ec_rows'      => json_encode($rows, JSON_UNESCAPED_UNICODE),
                'ec_fecha_ini' => $data['fecha_inicio'] ?? null,
                'ec_fecha_fin' => $data['fecha_fin'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return $this->ok(['ec_id' => $ecId, 'rows_count' => count($rows)]);
        } catch (\Throwable $e) {
            return $this->err('No se pudo procesar el archivo.', 500, ['error' => $e->getMessage()]);
        }
    }

    /* ===========================
     * Utilidades privadas
     * =========================== */

    /**
     * Lee CSV/XLSX y devuelve [headers, rows normalizados].
     * Estandariza: fecha, descripcion, referencia, monto
     */
    private function parseSheet(string $fullPath): array
    {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // CSV/TXT
        if (in_array($ext, ['csv', 'txt'])) {
            $fh = fopen($fullPath, 'r');
            if ($fh === false) {
                throw new \RuntimeException('No se pudo abrir el archivo CSV/TXT.');
            }
            $headers = fgetcsv($fh) ?: [];
            $rows = [];
            while (($r = fgetcsv($fh)) !== false) {
                $rows[] = $this->normalizeRow($headers, $r);
            }
            fclose($fh);
            return [$headers, $rows];
        }

        // Excel
        $reader = IOFactory::createReaderForFile($fullPath);
        $spread = $reader->load($fullPath);
        $sheet  = $spread->getSheet(0);
        $rowsRaw = $sheet->toArray(null, true, true, true);

        $first   = array_shift($rowsRaw) ?: [];
        $headers = array_values(array_map(fn($v) => is_null($v) ? '' : trim((string) $v), $first));

        $rows = [];
        foreach ($rowsRaw as $row) {
            $vals = array_values($row);
            $rows[] = $this->normalizeRow($headers, $vals);
        }
        return [$headers, $rows];
    }

    private function normalizeRow(array $headers, array $values): array
    {
        $map = [];
        foreach ($headers as $i => $h) {
            $key = strtolower(trim((string) $h));
            $map[$key] = $i;
        }

        $get = function (array $names, $default = null) use ($map, $values) {
            foreach ($names as $name) {
                if (isset($map[$name])) return $values[$map[$name]] ?? $default;
            }
            // fallback por "contiene"
            foreach ($map as $k => $idx) {
                foreach ($names as $name) {
                    if (str_contains($k, $name)) return $values[$idx] ?? $default;
                }
            }
            return $default;
        };

        $rawFecha = $get(['fecha', 'date', 'f.'], null);
        $fecha = null;
        if ($rawFecha) {
            try {
                $fecha = Carbon::parse($rawFecha)->format('Y-m-d');
            } catch (\Throwable $e) {
                $fecha = null;
            }
        }

        $desc     = (string) ($get(['descripcion', 'description', 'detalle', 'concepto'], '') ?? '');
        $ref      = (string) ($get(['referencia', 'ref', 'autorizacion', 'aut'], '') ?? '');
        $montoRaw = $get(['monto', 'importe', 'valor', 'credito', 'debito', 'amount'], 0);

        // Normalización gentil: quita Q/espacios, cambia coma por punto si hace falta
        $val = trim((string) $montoRaw);
        $val = str_ireplace(['Q', ' '], '', $val);
        // Si hay coma decimal pero no punto, cambia coma por punto.
        if (strpos($val, ',') !== false && strpos($val, '.') === false) {
            $val = str_replace(',', '.', $val);
        } else {
            $val = str_replace(',', '', $val);
        }
        $monto = (float) $val;

        return [
            'fecha'       => $fecha,
            'descripcion' => trim($desc),
            'referencia'  => trim($ref),
            'monto'       => round($monto, 2),
        ];
    }
}
