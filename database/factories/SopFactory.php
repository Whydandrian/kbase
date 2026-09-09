<?php

namespace Database\Factories;

use App\Models\Sop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sop>
 */
class SopFactory extends Factory
{
    protected $model = Sop::class;

    public function definition(): array
    {
        return [
            'nomor_sop' => fake()->numerify('###/IT10.IV/OT.07/####'),
            'nama_sop' => rtrim(fake()->unique()->sentence(3), '.'),
            'unit_pembuat' => 'UPT Teknologi Informasi dan Komunikasi',
            'kementerian' => 'Kementerian Riset, Teknologi dan Pendidikan Tinggi',
            'institusi' => 'Institut Teknologi Kalimantan',
            'tgl_pembuatan' => fake()->dateTimeBetween('-2 years', 'now'),
            'tgl_revisi' => null,
            'tgl_efektif' => null,
            'status' => 'draft',
        ];
    }
}
