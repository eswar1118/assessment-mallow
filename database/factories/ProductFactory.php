<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
            'name' => fake()->words(2, true),
            'code' => 'PRD-' . strtoupper(fake()->unique()->bothify('??###')),
            'price' => fake()->randomFloat(2, 10, 1000),
            'tax_percent' => fake()->randomElement([0.00, 5.00, 12.00, 18.00, 28.00]),
            'stock' => fake()->numberBetween(10, 150),
        ];
    }

    /**
     * Indicate that the product has low stock.
     */
    public function lowStock(int $stock = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock,
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
