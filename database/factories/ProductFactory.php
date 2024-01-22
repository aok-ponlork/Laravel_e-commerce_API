<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_name' => $this->faker->words(3, true), 
            'category_id' => Category::all()->random()->category_id,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'cost' => $this->faker->randomFloat(2, 5, 50), 
            'stock_qty' => $this->faker->numberBetween(0, 100), 
            'description' => $this->faker->paragraph, 
            'image' => $this->faker->imageUrl(),
        ];
    }
}
