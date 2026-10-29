<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job (exponential backoff).
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $orderId
    ) {
        $this->afterCommit();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Safely load the order with customer and item details
        $order = Order::with(['customer', 'orderItems.product'])->find($this->orderId);

        if (! $order) {
            Log::warning("SendOrderConfirmation: Order #{$this->orderId} not found in database. Skipping confirmation.");
            return;
        }

        if (! $order->customer) {
            Log::warning("SendOrderConfirmation: Customer for Order #{$this->orderId} not found. Skipping confirmation.");
            return;
        }

        $customerEmail = $order->customer->email;
        $customerName = $order->customer->name;
        $grandTotal = $order->grand_total;

        // 1. Log structured confirmation details
        Log::info("Simulated Order Confirmation Sent", [
            'order_id' => $order->id,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
            'grand_total' => $grandTotal,
            'subtotal' => $order->subtotal,
            'tax_total' => $order->tax_total,
            'items_count' => $order->orderItems->count(),
            'items' => $order->orderItems->map(fn ($item) => [
                'product_name' => $item->product?->name ?? 'Unknown Product',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->all(),
        ]);

        // 2. Dispatch simulated email via Mail facade (works with Mail::fake() in tests and logs via MAIL_MAILER=log)
        try {
            Mail::to($customerEmail)->send(new OrderConfirmationMail($order));
        } catch (Throwable $e) {
            Log::error("Failed sending order confirmation email via Mail transport: " . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
            throw $e;
        }
    }

    /**
     * Handle a permanent job failure without leaking sensitive customer credentials.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error("SendOrderConfirmation job failed permanently for Order #{$this->orderId}", [
            'order_id' => $this->orderId,
            'error_message' => $exception?->getMessage(),
        ]);
    }
}
