<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake(); // Prevent actual queue execution during HTTP tests
    }

    /**
     * Requirement 1: Successful order creation with correct subtotal, tax, grand total, and stock deduction.
     */
    public function test_successful_order_creation_calculates_totals_and_deducts_stock(): void
    {
        $product = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'price' => 110.00,
            'tax_percent' => 18.00,
            'stock' => 20,
        ]);

        $payload = [
            'customer_email' => 'buyer@example.com',
            'customer_name' => 'John Doe',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Order created successfully.')
            ->assertJsonPath('order.customer.email', 'buyer@example.com')
            ->assertJsonPath('order.subtotal', '330.00') // 3 * 110.00
            ->assertJsonPath('order.tax_total', '59.40')  // 330.00 * 0.18
            ->assertJsonPath('order.grand_total', '389.40'); // 330.00 + 59.40

        // Assert database record persisted
        $this->assertDatabaseHas('orders', [
            'subtotal' => 330.00,
            'tax_total' => 59.40,
            'grand_total' => 389.40,
        ]);

        // Assert order item persisted with snapshot values
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 110.00,
            'tax_percent' => 18.00,
            'line_subtotal' => 330.00,
            'line_tax' => 59.40,
            'line_total' => 389.40,
        ]);

        // Assert stock deducted from 20 to 17
        $this->assertEquals(17, $product->fresh()->stock);
    }

    /**
     * Requirement 2: Creation of a new customer when email does not exist.
     */
    public function test_creates_new_customer_when_email_does_not_exist(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->assertEquals(0, Customer::where('email', 'newcustomer@example.com')->count());

        $payload = [
            'customer_email' => '  NEWCUSTOMER@EXAMPLE.COM  ',
            'customer_name' => 'Alice Wonder',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201);

        // Verify normalized customer was created
        $customer = Customer::where('email', 'newcustomer@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Alice Wonder', $customer->name);
        $this->assertEquals('newcustomer@example.com', $customer->email);
    }

    /**
     * Requirement 3: Reuse of an existing customer by email without creating a duplicate.
     */
    public function test_reuses_existing_customer_without_creating_duplicate_or_altering_name(): void
    {
        $existingCustomer = Customer::factory()->create([
            'name' => 'Original Customer Name',
            'email' => 'existing@example.com',
        ]);

        $product = Product::factory()->create(['stock' => 10]);

        $payload = [
            'customer_email' => 'EXISTING@EXAMPLE.COM',
            'customer_name' => 'Ignored Name Attempt',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('order.customer.id', $existingCustomer->id)
            ->assertJsonPath('order.customer.name', 'Original Customer Name');

        // Verify only 1 customer exists with this email
        $this->assertEquals(1, Customer::where('email', 'existing@example.com')->count());
        $this->assertEquals('Original Customer Name', $existingCustomer->fresh()->name);
    }

    /**
     * Requirement 4: Validation failures for invalid payloads.
     */
    public function test_validation_fails_for_missing_customer_name_when_customer_is_new(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/orders', [
            'customer_email' => 'nonexistent@example.com',
            // customer_name is missing
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name']);
    }

    public function test_validation_fails_for_invalid_email(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/orders', [
            'customer_email' => 'not-a-valid-email',
            'customer_name' => 'Test User',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_email']);
    }

    public function test_validation_fails_for_missing_and_empty_products(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_email' => 'user@example.com',
            'customer_name' => 'Test User',
            'products' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);
    }

    public function test_validation_fails_for_nonexistent_product_id(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_email' => 'user@example.com',
            'customer_name' => 'Test User',
            'products' => [
                ['product_id' => 999999, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_id']);
    }

    public function test_validation_fails_for_zero_and_negative_quantities(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/orders', [
            'customer_email' => 'user@example.com',
            'customer_name' => 'Test User',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 0],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.quantity']);

        $responseNegative = $this->postJson('/api/orders', [
            'customer_email' => 'user@example.com',
            'customer_name' => 'Test User',
            'products' => [
                ['product_id' => $product->id, 'quantity' => -3],
            ],
        ]);

        $responseNegative->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.quantity']);
    }

    /**
     * Requirement 5: Insufficient stock returns HTTP 409 and does not create an order or deduct stock.
     */
    public function test_insufficient_stock_returns_409_and_prevents_partial_deductions_or_order_creation(): void
    {
        $productInStock = Product::factory()->create([
            'name' => 'Product In Stock',
            'stock' => 5,
        ]);

        $productOutOfStock = Product::factory()->create([
            'name' => 'Product Low Stock',
            'stock' => 2,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Customer Name',
            'products' => [
                ['product_id' => $productInStock->id, 'quantity' => 2],
                ['product_id' => $productOutOfStock->id, 'quantity' => 5], // Exceeds stock (available: 2)
            ],
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'insufficient_stock')
            ->assertJsonPath('product_id', $productOutOfStock->id)
            ->assertJsonPath('available_stock', 2)
            ->assertJsonPath('requested_quantity', 5);

        // Assert NO order was created
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, OrderItem::count());

        // Assert NO partial deduction occurred on either product
        $this->assertEquals(5, $productInStock->fresh()->stock);
        $this->assertEquals(2, $productOutOfStock->fresh()->stock);
    }

    /**
     * Requirement 6: Multiple products with different prices and tax percentages.
     */
    public function test_multiple_products_with_different_prices_and_tax_percentages(): void
    {
        // Product 1: 0% Tax (e.g. Milk 1L)
        $p1 = Product::factory()->create([
            'name' => 'Milk 1L',
            'price' => 74.00,
            'tax_percent' => 0.00,
            'stock' => 10,
        ]);

        // Product 2: 5% Tax (e.g. Atta 5kg)
        $p2 = Product::factory()->create([
            'name' => 'Atta 5kg',
            'price' => 265.00,
            'tax_percent' => 5.00,
            'stock' => 10,
        ]);

        // Product 3: 18% Tax (e.g. Toothpaste)
        $p3 = Product::factory()->create([
            'name' => 'Toothpaste',
            'price' => 110.00,
            'tax_percent' => 18.00,
            'stock' => 10,
        ]);

        $payload = [
            'customer_email' => 'multi@example.com',
            'customer_name' => 'Multi Buyer',
            'products' => [
                ['product_id' => $p1->id, 'quantity' => 2], // 2 * 74 = 148.00, tax 0 = 0.00, total = 148.00
                ['product_id' => $p2->id, 'quantity' => 1], // 1 * 265 = 265.00, tax 5% = 13.25, total = 278.25
                ['product_id' => $p3->id, 'quantity' => 2], // 2 * 110 = 220.00, tax 18% = 39.60, total = 259.60
            ],
        ];

        // Subtotal = 148.00 + 265.00 + 220.00 = 633.00
        // Tax Total = 0.00 + 13.25 + 39.60 = 52.85
        // Grand Total = 633.00 + 52.85 = 685.85

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('order.subtotal', '633.00')
            ->assertJsonPath('order.tax_total', '52.85')
            ->assertJsonPath('order.grand_total', '685.85')
            ->assertJsonCount(3, 'order.items');

        $this->assertDatabaseHas('orders', [
            'subtotal' => 633.00,
            'tax_total' => 52.85,
            'grand_total' => 685.85,
        ]);

        // Verify stock deductions
        $this->assertEquals(8, $p1->fresh()->stock);
        $this->assertEquals(9, $p2->fresh()->stock);
        $this->assertEquals(8, $p3->fresh()->stock);
    }
}
