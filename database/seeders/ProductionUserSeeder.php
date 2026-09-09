<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed satu user administrator untuk lingkungan production.
 *
 * GANTI password sebelum deploy ke server:
 *   php artisan tinker --execute 'User::find(1)->update(["password" => Hash::make("password-baru")]);'
 * atau ubah langsung di sini sebelum menjalankan seeder.
 *
 * Dijalankan via: php artisan db:seed --class=ProductionSeeder
 */
class ProductionUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'administrator@itk.ac.id'],
            [
                'name' => 'Administrator UPT TIK',
                'jabatan' => 'Kepala UPT Teknologi Informasi dan Komunikasi',
                'password' => Hash::make('@kbase*2026#'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->command->info('✓ Admin user seeded: admin@upt-tik.itk.ac.id');
        $this->command->warn('  ⚠ Segera ganti password default setelah pertama login!');
    }
}
