<?php

namespace App\Domain\Catalogos\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Padrón oficial provisto por Secretaría Académica.
 *
 * Es un registro de referencia: no se vincula por clave foránea con la
 * tabla de egresados, de modo que una depuración del padrón no elimine
 * cuentas activas (E-08 numeral 4.3.1).
 */
class PadronEgresado extends Model
{
    protected $table = 'padron_egresados';
    public $timestamps = false;

    protected $fillable = ['dni', 'nombres', 'apellidos', 'anio_egreso', 'grado'];
}
