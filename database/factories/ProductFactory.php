<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3, false),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 0, 99),
            'file_path' => 'products/'.fake()->uuid().'.zip',
            'thumbnail' => 'https://picsum.photos/seed/'.fake()->word().'/400/400',
            'is_active' => true,
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function free(): static
    {
        return $this->state(['price' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
