<?php

namespace App\Domain\Catalogos\Repositories;

use App\Domain\Catalogos\Models\PadronEgresado;

class PadronRepository
{
    public function buscarPorDni(string $dni): ?PadronEgresado
    {
        return PadronEgresado::where('dni', $dni)->first();
    }

    public function existe(string $dni): bool
    {
        return PadronEgresado::where('dni', $dni)->exists();
    }
}
