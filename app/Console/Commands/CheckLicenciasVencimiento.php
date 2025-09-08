<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LicenciaImportacion;
use Twilio\Rest\Client;

class CheckLicenciasVencimiento extends Command
{
    protected $signature = 'licencias:check-vencimiento';
    protected $description = 'Verificar licencias por vencer y vencidas y enviar por WhatsApp';

public function handle()
{
    // Configuración de Twilio
    $twilioSid = env('TWILIO_SID');
    $twilioToken = env('TWILIO_AUTH_TOKEN');
    $twilioWhatsApp = env('TWILIO_WHATSAPP_FROM');
    $adminNumber = env('ADMIN_WHATSAPP');

    // Verificar que las variables estén configuradas
    if (!$twilioSid || !$twilioToken || !$twilioWhatsApp || !$adminNumber) {
        $this->error('❌ Faltan variables de configuración de Twilio en el .env');
        return;
    }

    try {
        // Configurar Twilio para ignorar SSL en desarrollo
        $httpClient = new \Twilio\Http\CurlClient([
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $twilio = new Client($twilioSid, $twilioToken, null, null, $httpClient);

        $porVencer = LicenciaImportacion::with(['empresa'])
            ->porVencer(30)
            ->get();

        $vencidas = LicenciaImportacion::with(['empresa'])
            ->vencidas()
            ->get();

        if ($porVencer->count() > 0 || $vencidas->count() > 0) {
            $mensajeResumen = "📋 *RESUMEN DE LICENCIAS DE IMPORTACIÓN*\n\n";
            
            if ($porVencer->count() > 0) {
                $mensajeResumen .= "⚠️ *POR VENCER ({$porVencer->count()}):*\n";
                foreach ($porVencer as $licencia) {
                    $dias = (int)$licencia->dias_hasta_vencimiento;
                    $mensajeResumen .= "• #{$licencia->lipaimp_id} - {$licencia->empresa->empresaimp_descripcion} - Vence en {$dias} " . ($dias == 1 ? 'día' : 'días') . "\n";
                }
                $mensajeResumen .= "\n";
            }

            if ($vencidas->count() > 0) {
                $mensajeResumen .= "❌ *VENCIDAS ({$vencidas->count()}):*\n";
                foreach ($vencidas as $licencia) {
                    $diasVencida = abs((int)$licencia->dias_hasta_vencimiento);
                    $mensajeResumen .= "• #{$licencia->lipaimp_id} - {$licencia->empresa->empresaimp_descripcion} - Vencida hace {$diasVencida} " . ($diasVencida == 1 ? 'día' : 'días') . "\n";
                }
            }

            // Enviar mensaje por WhatsApp
            $twilio->messages->create(
                $adminNumber,
                [
                    'from' => $twilioWhatsApp,
                    'body' => $mensajeResumen
                ]
            );

            $this->info('✅ Resumen enviado por WhatsApp: ' . $porVencer->count() . ' por vencer, ' . $vencidas->count() . ' vencidas');
        } else {
            $this->info('✅ No hay licencias por vencer o vencidas');
        }

    } catch (\Exception $e) {
        $this->error('❌ Error con Twilio: ' . $e->getMessage());
    }
}
}