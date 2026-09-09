<?php

namespace Database\Factories;

use App\Models\Thesis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thesis>
 */
class ThesisFactory extends Factory
{
    protected $model = Thesis::class;

    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->unique()->sentence(6), '.'),
            'author' => fake()->name(),
            'university' => fake()->randomElement([
                'Institut Teknologi Kalimantan',
                'Universitas Mulawarman',
                'Institut Teknologi Sepuluh Nopember',
                'Universitas Gadjah Mada',
            ]),
            'study_program' => fake()->randomElement([
                'Informatika',
                'Sistem Informasi',
                'Teknik Elektro',
                'Matematika',
            ]),
            'year' => fake()->numberBetween(2019, 2025),
            'drive_url' => fake()->boolean(70) ? fake()->url() : null,
            'file_path' => null,
        ];
    }

    public function withDriveUrl(): static
    {
        return $this->state(fn (): array => ['drive_url' => fake()->url(), 'file_path' => null]);
    }
}
