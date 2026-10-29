<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order with concurrency-safe stock validation and deduction.
     *
     * Concurrency & Locking Strategy:
     * 1. Deterministic Lock Ordering: Product IDs are sorted in ascending numerical order
     *    before acquiring locks. This prevents cyclic cross-resource deadlock scenarios (AB-BA)
     *    when concurrent transactions request the same set of products in different order.
     * 2. Row-Level Exclusive Locks: Uses SELECT ... FOR UPDATE via Eloquent's lockForUpdate()
     *    inside an atomic DB::transaction() with automatic deadlock retry (3 attempts).
     * 3. Stock Availability Check: Re-reads locked stock directly from the database and verifies
     *    availability. If stock is insufficient, throws InsufficientStockException (HTTP 409)
     *    which instantly aborts the transaction, reverting any partial modifications.
     * 4. Atomic Stock Deduction: Stock is decremented within the exact same transaction that
     *    creates the order and order items.
     * 5. Post-Commit Actions: Queued confirmations or external dispatches are scheduled using
     *    DB::afterCommit() so they only fire after successful persistence.
     *
     * Database Engine Differences:
     * - MySQL (InnoDB): lockForUpdate() acquires true exclusive row-level index locks (X locks).
     * - SQLite: Concurrency is managed via file-level transactions (lockForUpdate is a SQL no-op
     *   at row level; SQLite locks the entire database during write transactions).
     *
     * @throws InsufficientStockException
     */
    public function createOrder(array $data): Order
    {
        // 3 retry attempts for database-level deadlocks
        return DB::transaction(function () use ($data) {
            $email = strtolower(trim($data['customer_email']));

            // 1. Resolve or create customer
            $customer = Customer::where('email', $email)->first();

            if (! $customer) {
                $customer = Customer::create([
                    'name' => trim($data['customer_name']),
                    'email' => $email,
                ]);
            }

            // 2. Aggregate quantities per product ID to handle potential multi-line entries safely
            $aggregatedQuantities = [];
            foreach ($data['products'] as $item) {
                $pid = (int) $item['product_id'];
                $aggregatedQuantities[$pid] = ($aggregatedQuantities[$pid] ?? 0) + (int) $item['quantity'];
            }

            // 3. Sort product IDs ascending to eliminate deadlock risk
            $productIds = array_keys($aggregatedQuantities);
            sort($productIds, SORT_NUMERIC);

            // 4. Lock products deterministically using SELECT ... FOR UPDATE
            $products = Product::whereIn('id', $productIds)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 5. Re-read and validate stock availability for each product
            foreach ($productIds as $productId) {
                /** @var Product $product */
                $product = $products->get($productId);
                $requiredQty = $aggregatedQuantities[$productId];

                if ($product->stock < $requiredQty) {
                    throw new InsufficientStockException(
                        productId: $product->id,
                        productName: $product->name,
                        availableStock: $product->stock,
                        requestedQuantity: $requiredQty
                    );
                }
            }

            // 6. Deduct stock atomically within the transaction
            foreach ($productIds as $productId) {
                /** @var Product $product */
                $product = $products->get($productId);
                $deductQty = $aggregatedQuantities[$productId];

                $product->decrement('stock', $deductQty);
            }

            // 7. Calculate line totals and order totals using database prices and tax rates
            $lineItems = [];
            $orderSubtotal = 0.00;
            $orderTaxTotal = 0.00;

            foreach ($data['products'] as $item) {
                /** @var Product $product */
                $product = $products->get($item['product_id']);

                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price;
                $taxPercent = (float) $product->tax_percent;

                $lineSubtotal = round($quantity * $unitPrice, 2);
                $lineTax = round($lineSubtotal * ($taxPercent / 100), 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $orderSubtotal += $lineSubtotal;
                $orderTaxTotal += $lineTax;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percent' => $taxPercent,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,
                ];
            }

            $orderSubtotal = round($orderSubtotal, 2);
            $orderTaxTotal = round($orderTaxTotal, 2);
            $orderGrandTotal = round($orderSubtotal + $orderTaxTotal, 2);

            // 8. Create the order
            /** @var Order $order */
            $order = $customer->orders()->create([
                'subtotal' => $orderSubtotal,
                'tax_total' => $orderTaxTotal,
                'grand_total' => $orderGrandTotal,
            ]);

            // 9. Create order items snapshot
            $order->orderItems()->createMany($lineItems);

            // 10. Register post-commit callbacks for confirmation jobs/events
            DB::afterCommit(function () use ($order) {
                SendOrderConfirmation::dispatch($order->id);
            });

            return $order->load(['customer', 'orderItems.product']);
        }, 3);
    }
}
