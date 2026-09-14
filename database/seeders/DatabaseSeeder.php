<?php

namespace Database\Seeders;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Usuario::factory()->administrador()->create([
            'email' => 'admin@alumniconnect.test',
        ]);
    }
}
