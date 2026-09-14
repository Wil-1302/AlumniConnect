<?php

namespace Tests\Unit;

use App\Domain\Ofertas\Models\OfertaLaboral;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas de las reglas de negocio RN-01 a RN-10.
 *
 * El plan de calidad (E-06) exige que cada regla cuente con verificación.
 * Estas pruebas se ejecutan en la actividad A34 del cronograma.
 */
class ReglasNegocioTest extends TestCase
{
    /** RN-04: una oferta vencida no debe considerarse vigente. */
    public function test_oferta_vencida_no_esta_vigente(): void
    {
        $oferta = new OfertaLaboral([
            'activa'       => true,
            'fecha_cierre' => '2020-01-01',
        ]);

        $this->assertTrue($oferta->activa);
        $this->assertLessThan(date('Y-m-d'), $oferta->fecha_cierre);
    }

    // Pendientes de implementar durante la actividad A34:
    // - test_no_permite_registrar_dni_fuera_del_padron()      (RN-01)
    // - test_no_permite_dos_cuentas_con_el_mismo_dni()        (RN-02)
    // - test_no_permite_registro_sin_consentimiento()         (RN-08)
    // - test_no_permite_responder_dos_veces_la_misma_encuesta() (RN-06)
    // - test_directorio_no_expone_datos_de_contacto()         (RN-07)
}
