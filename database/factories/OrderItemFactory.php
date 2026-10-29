<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 500);
        $taxPercent = fake()->randomElement([0.00, 5.00, 12.00, 18.00]);
        $lineSubtotal = round($quantity * $unitPrice, 2);
        $lineTax = round($lineSubtotal * ($taxPercent / 100), 2);
        $lineTotal = round($lineSubtotal + $lineTax, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_percent' => $taxPercent,
            'line_subtotal' => $lineSubtotal,
            'line_tax' => $lineTax,
            'line_total' => $lineTotal,
        ];
    }
}
