<?php
// app/Services/PaymentTotalsService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class PaymentTotalsService
{
    /**
     * Recalcula el total pagado de UNA licencia y hace upsert en pro_licencias_total_pagado.
     */
    public function recalcTotalForLicense(int $licId): void
    {
        // Opción A: una subconsulta sumando solo activos
        $sql = "
            INSERT INTO pro_licencias_total_pagado (lic_id, total_pagado, updated_at)
            SELECT 
                :licId AS lic_id,
                COALESCE((
                    SELECT SUM(pm.pagomet_monto)
                    FROM pro_pagos_licencias p
                    JOIN pro_pagos_lic_metodos pm ON pm.pagomet_pago_lic = p.pago_lic_id
                    WHERE p.pago_lic_licencia_id = :licId2
                      AND p.pago_lic_situacion = 1
                      AND pm.pagomet_situacion = 1
                ), 0) AS total_pagado,
                NOW() AS updated_at
            ON DUPLICATE KEY UPDATE
                total_pagado = VALUES(total_pagado),
                updated_at   = VALUES(updated_at)
        ";

        DB::statement($sql, ['licId' => $licId, 'licId2' => $licId]);
    }

    /**
     * Recalcula en lote para MUCHAS licencias (útil post-import).
     */
    public function recalcTotalsForLicenses(array $licIds): void
    {
        if (empty($licIds)) return;

        // Usamos una tabla temporal o VALUES para filtrar; aquí un IN simple por claridad.
        $ids = implode(',', array_map('intval', $licIds));

        $sql = "
            INSERT INTO pro_licencias_total_pagado (lic_id, total_pagado, updated_at)
            SELECT 
                l.lipaimp_id,
                COALESCE(SUM(pm.pagomet_monto), 0) AS total_pagado,
                NOW()
            FROM pro_licencias_para_importacion l
            LEFT JOIN pro_pagos_licencias p 
                   ON p.pago_lic_licencia_id = l.lipaimp_id AND p.pago_lic_situacion = 1
            LEFT JOIN pro_pagos_lic_metodos pm
                   ON pm.pagomet_pago_lic = p.pago_lic_id AND pm.pagomet_situacion = 1
            WHERE l.lipaimp_id IN ($ids)
            GROUP BY l.lipaimp_id
            ON DUPLICATE KEY UPDATE
                total_pagado = VALUES(total_pagado),
                updated_at   = VALUES(updated_at)
        ";

        DB::statement($sql);
    }
}
