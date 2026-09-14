<?php

namespace App\Shared\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando se incumple una regla de negocio (RN-01 a RN-10).
 *
 * La capa de presentación la captura y la traduce a un mensaje para el
 * usuario. Los servicios nunca devuelven respuestas HTTP directamente.
 */
class ReglaNegocioException extends RuntimeException
{
    public function __construct(
        string $mensaje,
        public readonly string $regla = ''
    ) {
        parent::__construct($mensaje);
    }

    public static function regla(string $codigo, string $mensaje): self
    {
        return new self($mensaje, $codigo);
    }
}
