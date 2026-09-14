<?php

namespace App\Domain\Seguridad\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * RF-29 y RN-09: bitácora de accesos administrativos y exportaciones.
 */
class AuditoriaService
{
    public function registrar(?int $usuarioId, string $accion, ?string $entidad = null): void
    {
        DB::table('auditoria_accesos')->insert([
            'usuario_id' => $usuarioId,
            'accion'     => $accion,
            'entidad'    => $entidad,
            'ip'         => Request::ip(),
            'fecha_hora' => now(),
        ]);
    }
}
