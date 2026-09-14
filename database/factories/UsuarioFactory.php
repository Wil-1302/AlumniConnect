<?php

namespace Database\Factories;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * El hash de contraseña actual usado por la factory.
     */
    protected static ?string $passwordHash;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email'          => fake()->unique()->safeEmail(),
            'password_hash'  => static::$passwordHash ??= Hash::make('password'),
            'rol'            => Usuario::ROL_EGRESADO,
            'activo'         => true,
            'ultimo_acceso'  => null,
        ];
    }

    /** Estado: usuario administrador (RF-14, RF-19...). */
    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => Usuario::ROL_ADMINISTRADOR,
        ]);
    }

    /** Estado: usuario inactivo. */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
