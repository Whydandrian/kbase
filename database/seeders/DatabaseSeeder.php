<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk lingkungan DEVELOPMENT & testing.
 * Mengisi data mock (factory, fake docs, 24 theses, dll).
 *
 * Untuk PRODUCTION gunakan:
 *   php artisan db:seed --class=ProductionSeeder
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@kbase.test',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->call([
            KnowledgeBaseSeeder::class,
            SopSeeder::class,
        ]);
    }
}
