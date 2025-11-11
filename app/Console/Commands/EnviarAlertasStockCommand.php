<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlertaStockBajo;
use Carbon\Carbon;

class EnviarAlertasStockCommand extends Command
{
    protected $signature = 'alertas:enviar-stock';
    protected $description = 'Envía emails diarios de alertas de stock bajo a administradores';

    public function handle()
    {
        $this->info('🚀 Iniciando envío de alertas de stock...');

        // 1. Obtener alertas pendientes
        $alertasPendientes = DB::table('pro_alertas')
            ->join('pro_productos', 'pro_alertas.alerta_producto_id', '=', 'pro_productos.producto_id')
            ->whereIn('alerta_tipo', ['stock_bajo', 'stock_agotado'])
            ->where('alerta_resuelta', false)
            ->where(function($q) {
                $q->where('email_enviado', false)
                  ->orWhere('email_fecha_envio', '<', Carbon::today());
            })
            ->select([
                'pro_alertas.alerta_id',
                'pro_alertas.alerta_tipo',
                'pro_alertas.alerta_titulo',
                'pro_alertas.alerta_mensaje',
                'pro_alertas.alerta_prioridad',
                'pro_productos.producto_nombre',
                'pro_productos.pro_codigo_sku'
            ])
            ->get();

        if ($alertasPendientes->isEmpty()) {
            $this->info('✅ No hay alertas pendientes.');
            return 0;
        }

        // 2. Obtener administradores (rol_id = 1)
        $administradores = DB::table('users')
            ->where('user_rol', 1)
            ->where('user_situacion', 1)
            ->whereNotNull('email')
            ->get(['user_id', 'email', 'user_primer_nombre']);

        if ($administradores->isEmpty()) {
            $this->warn('⚠️  No hay administradores activos.');
            return 0;
        }

        // 3. Agrupar alertas
        $alertasAgrupadas = [
            'critica' => $alertasPendientes->where('alerta_prioridad', 'critica'),
            'alta' => $alertasPendientes->where('alerta_prioridad', 'alta'),
            'media' => $alertasPendientes->where('alerta_prioridad', 'media'),
        ];

        // 4. Enviar emails
        foreach ($administradores as $admin) {
            try {
                Mail::to($admin->email)->send(
                    new AlertaStockBajo($alertasAgrupadas, $admin->user_primer_nombre)
                );
                $this->info("✓ Email enviado a: {$admin->email}");
            } catch (\Exception $e) {
                $this->error("✗ Error: {$admin->email} - " . $e->getMessage());
            }
        }

        // 5. Marcar como enviadas
        DB::table('pro_alertas')
            ->whereIn('alerta_id', $alertasPendientes->pluck('alerta_id'))
            ->update([
                'email_enviado' => true,
                'email_fecha_envio' => Carbon::now()
            ]);

        $this->info("✅ Completado: {$alertasPendientes->count()} alertas procesadas");
        return 0;
    }
}