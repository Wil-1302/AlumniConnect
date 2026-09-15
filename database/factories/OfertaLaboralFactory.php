<?php

namespace Database\Factories;

use App\Domain\Ofertas\Models\OfertaLaboral;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<OfertaLaboral>
 */
class OfertaLaboralFactory extends Factory
{
    protected $model = OfertaLaboral::class;

    public function definition(): array
    {
        $fechaPublicacion = fake()->dateTimeBetween('-4 months', 'now');

        return [
            'rubro_id' => fn () => DB::table('rubros')->inRandomOrder()->value('id'),
            'creada_por' => Usuario::factory()->administrador(),
            'titulo' => fake()->jobTitle(),
            'empresa' => fake()->company(),
            'descripcion' => fake()->paragraphs(3, true),
            'requisitos' => fake()->paragraphs(2, true),
            'modalidad' => fake()->randomElement(['presencial', 'remoto', 'hibrido']),
            'fecha_publicacion' => $fechaPublicacion->format('Y-m-d'),
            'fecha_cierre' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'activa' => true,
        ];
    }

    /** Desactivada manualmente por un administrador (RF-18): sigue en el histórico. */
    public function desactivada(): static
    {
        return $this->state(fn (array $attributes) => ['activa' => false]);
    }

    /** Su fecha de cierre ya pasó; sigue activa pero ya no aparece en la bolsa (RN-04). */
    public function cerrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_publicacion' => fake()->dateTimeBetween('-4 months', '-2 months')->format('Y-m-d'),
            'fecha_cierre' => fake()->dateTimeBetween('-6 weeks', '-1 day')->format('Y-m-d'),
        ]);
    }
}
