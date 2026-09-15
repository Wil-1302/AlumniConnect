<?php

namespace Database\Seeders;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Egresados\Models\ExperienciaLaboral;
use App\Domain\Ofertas\Models\OfertaLaboral;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * SOLO PARA DESARROLLO/DEMO, igual que PadronPruebaSeeder: no ejecutar en
 * producción. Registra como egresados reales (con cuenta y contraseña
 * conocida) a los del padrón de prueba, con datos de perfil al azar, y
 * publica un lote de ofertas laborales variadas para poder ver y probar
 * el listado, los filtros y las acciones de administración (desactivar,
 * reactivar, eliminar).
 *
 * Uso: php artisan db:seed --class=DatosDemoSeeder
 */
class DatosDemoSeeder extends Seeder
{
    /** Contraseña de todas las cuentas de egresado que crea este seeder. */
    public const PASSWORD_DEMO = 'Egresado123!';

    public function run(): void
    {
        $this->call([CatalogosSeeder::class, PadronPruebaSeeder::class]);

        $admin = Usuario::where('rol', Usuario::ROL_ADMIN_PRINCIPAL)->first()
            ?? Usuario::factory()->create([
                'email' => 'admin-demo@alumniconnect.test',
                'rol' => Usuario::ROL_ADMIN_PRINCIPAL,
            ]);

        $this->registrarEgresadosDelPadron();
        $this->publicarOfertasDeEjemplo($admin);
    }

    /** Crea usuario + egresado para cada entrada del padrón que todavía no tiene cuenta. */
    private function registrarEgresadosDelPadron(): void
    {
        $ciudades = ['Cerro de Pasco', 'Huánuco', 'Lima', 'Huancayo', 'Tarma', null];
        $situaciones = DB::table('situaciones_laborales')->get();
        $rubroIds = DB::table('rubros')->pluck('id');

        $sinCuenta = DB::table('padron_egresados')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('egresados')
                    ->whereColumn('egresados.dni', 'padron_egresados.dni');
            })
            ->get();

        foreach ($sinCuenta as $registro) {
            $usuario = Usuario::create([
                'email' => Str::slug($registro->nombres.' '.$registro->apellidos).'@example.test',
                'password_hash' => Hash::make(self::PASSWORD_DEMO),
                'rol' => Usuario::ROL_EGRESADO,
                'activo' => true,
            ]);

            $egresado = Egresado::create([
                'usuario_id' => $usuario->id,
                'dni' => $registro->dni,
                'nombres' => $registro->nombres,
                'apellidos' => $registro->apellidos,
                'anio_egreso' => $registro->anio_egreso,
                'grado' => $registro->grado,
                'telefono' => fake()->boolean(70) ? fake()->numerify('9########') : null,
                'ciudad' => fake()->randomElement($ciudades),
                'perfil_visible' => fake()->boolean(80),
                'consentimiento_en' => now(),
            ]);

            $situacion = $situaciones->random();
            $requiereDetalle = (bool) $situacion->requiere_detalle_laboral;

            ExperienciaLaboral::create([
                'egresado_id' => $egresado->id,
                'situacion_id' => $situacion->id,
                'rubro_id' => $requiereDetalle ? $rubroIds->random() : null,
                'empresa' => $requiereDetalle ? fake()->company() : null,
                'cargo' => $requiereDetalle ? fake()->jobTitle() : null,
                'ciudad' => $requiereDetalle ? fake()->randomElement($ciudades) : null,
                'fecha_inicio' => $requiereDetalle ? fake()->dateTimeBetween('-3 years')->format('Y-m-d') : null,
                'es_actual' => true,
            ]);
        }
    }

    private function publicarOfertasDeEjemplo(Usuario $admin): void
    {
        OfertaLaboral::factory()->count(20)->create(['creada_por' => $admin->id]);
        OfertaLaboral::factory()->desactivada()->count(6)->create(['creada_por' => $admin->id]);
        OfertaLaboral::factory()->cerrada()->count(6)->create(['creada_por' => $admin->id]);
    }
}
