<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Entry point seeder untuk lingkungan PRODUCTION.
 *
 * Cara menjalankan (setelah php artisan migrate):
 *   php artisan db:seed --class=ProductionSeeder
 *
 * Seeder ini aman dijalankan ulang (idempotent — pakai updateOrCreate).
 * Tidak ada data dummy/factory — hanya struktur domain, kategori, dan 1 user admin.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('=== Production Seeder ===');

        $this->call([
            ProductionUserSeeder::class,
            ProductionKnowledgeBaseSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✓ Seeding selesai. Akun admin: admin@upt-tik.itk.ac.id');
        $this->command->warn('  ⚠ Segera ganti password setelah pertama kali login!');
        $this->command->info('');
    }
}
