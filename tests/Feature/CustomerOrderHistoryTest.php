<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement 7: Customer order history returns the correct orders and order items.
     */
    public function test_customer_order_history_returns_orders_and_order_items(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Rahul Sharma',
            'email' => 'rahul.test@example.com',
        ]);

        $product1 = Product::factory()->create([
            'name' => 'Parle-G 1kg',
            'code' => 'PARL-1KG',
            'price' => 120.00,
            'tax_percent' => 5.00,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Eggs 6pk',
            'code' => 'EGGS-6PK',
            'price' => 48.00,
            'tax_percent' => 0.00,
        ]);

        // Order 1
        $order1 = Order::factory()->create([
            'customer_id' => $customer->id,
            'subtotal' => 240.00,
            'tax_total' => 12.00,
            'grand_total' => 252.00,
            'created_at' => now()->subDay(),
        ]);

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 120.00,
            'tax_percent' => 5.00,
            'line_subtotal' => 240.00,
            'line_tax' => 12.00,
            'line_total' => 252.00,
        ]);

        // Order 2
        $order2 = Order::factory()->create([
            'customer_id' => $customer->id,
            'subtotal' => 48.00,
            'tax_total' => 0.00,
            'grand_total' => 48.00,
            'created_at' => now(),
        ]);

        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 48.00,
            'tax_percent' => 0.00,
            'line_subtotal' => 48.00,
            'line_tax' => 0.00,
            'line_total' => 48.00,
        ]);

        // Other customer order (should not be included)
        $otherCustomer = Customer::factory()->create(['email' => 'other@example.com']);
        Order::factory()->create(['customer_id' => $otherCustomer->id]);

        $response = $this->getJson('/api/customers/orders?email=' . urlencode(' RAHUL.TEST@EXAMPLE.COM '));

        $response->assertStatus(200)
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonPath('customer.name', 'Rahul Sharma')
            ->assertJsonPath('customer.email', 'rahul.test@example.com')
            ->assertJsonPath('orders_count', 2)
            ->assertJsonCount(2, 'orders')
            ->assertJsonPath('orders.0.id', $order2->id) // latest first
            ->assertJsonPath('orders.0.grand_total', '48.00')
            ->assertJsonPath('orders.0.items.0.product_name', 'Eggs 6pk')
            ->assertJsonPath('orders.0.items.0.product_code', 'EGGS-6PK')
            ->assertJsonPath('orders.1.id', $order1->id)
            ->assertJsonPath('orders.1.grand_total', '252.00')
            ->assertJsonPath('orders.1.items.0.product_name', 'Parle-G 1kg');
    }

    public function test_customer_order_history_returns_404_when_customer_not_found(): void
    {
        $response = $this->getJson('/api/customers/orders?email=nonexistent@example.com');

        $response->assertStatus(404)
            ->assertJsonPath('error', 'customer_not_found');
    }

    public function test_customer_order_history_returns_422_when_email_missing_or_invalid(): void
    {
        $responseMissing = $this->getJson('/api/customers/orders');
        $responseMissing->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $responseInvalid = $this->getJson('/api/customers/orders?email=invalid-email');
        $responseInvalid->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
