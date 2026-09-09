<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'category_id' => Category::factory(),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->sentence(),
            'doc_category' => fake()->randomElement(['SOP', 'Procedure', 'Guideline', 'Runbook', 'How-to guide']),
            'owner' => fake()->randomElement(['Network Team', 'Web Development', 'Administration', 'UPT TIK']),
            'icon' => fake()->randomElement(['file-text', 'book-open', 'settings', 'shield-check']),
            'url' => fake()->url(),
            'file_path' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
