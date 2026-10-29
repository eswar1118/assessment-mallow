<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmation;
use App\Mail\OrderConfirmationMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderConfirmationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_after_successful_order_creation(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_percent' => 18.00,
            'stock' => 10,
        ]);

        $service = app(OrderService::class);

        $order = $service->createOrder([
            'customer_email' => 'buyer@example.com',
            'customer_name' => 'Buyer Test',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        Queue::assertPushed(SendOrderConfirmation::class, function ($job) use ($order) {
            return $job->orderId === $order->id;
        });
    }

    public function test_job_is_not_dispatched_when_order_creation_rolls_back(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_percent' => 18.00,
            'stock' => 1,
        ]);

        $service = app(OrderService::class);

        try {
            $service->createOrder([
                'customer_email' => 'buyer@example.com',
                'customer_name' => 'Buyer Test',
                'products' => [
                    ['product_id' => $product->id, 'quantity' => 5], // Exceeds available stock
                ],
            ]);
            $this->fail('Expected InsufficientStockException was not thrown.');
        } catch (InsufficientStockException $e) {
            // Expected exception
        }

        Queue::assertNotPushed(SendOrderConfirmation::class);
        $this->assertEquals(0, Order::count());
    }

    public function test_job_execution_sends_simulated_email_safely(): void
    {
        Mail::fake();

        $customer = Customer::factory()->create([
            'email' => 'recipient@example.com',
            'name' => 'Recipient Test',
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'subtotal' => 200.00,
            'tax_total' => 36.00,
            'grand_total' => 236.00,
        ]);

        $job = new SendOrderConfirmation($order->id);
        $job->handle();

        Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_job_handles_missing_order_gracefully_without_exception(): void
    {
        Mail::fake();

        $job = new SendOrderConfirmation(999999);
        $job->handle();

        Mail::assertNothingSent();
    }
}
