<?php

namespace Database\Factories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => Domain::TYPE_DOMAIN,
            'description' => fake()->sentence(),
            'icon' => 'book-open',
            'accent' => fake()->randomElement(['primary', 'success', 'warning']),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function domain(): static
    {
        return $this->state(fn (): array => ['type' => Domain::TYPE_DOMAIN]);
    }

    public function team(): static
    {
        return $this->state(fn (): array => ['type' => Domain::TYPE_TEAM]);
    }

    public function archive(): static
    {
        return $this->state(fn (): array => ['type' => Domain::TYPE_ARCHIVE]);
    }
}
