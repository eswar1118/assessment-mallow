<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetCustomerOrdersRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CustomerOrderController extends Controller
{
    /**
     * Retrieve order history for a specific customer by email.
     *
     * - Normalized email lookup.
     * - Eager loading of orders, order items, and products to eliminate N+1 queries.
     * - Returns HTTP 404 if the customer does not exist.
     * - Filters internal fields and exposes only order IDs, dates, totals, and line item details.
     */
    public function index(GetCustomerOrdersRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        $customer = Customer::where('email', $email)->first();

        if (! $customer) {
            return response()->json([
                'message' => "Customer with email '{$email}' was not found.",
                'error' => 'customer_not_found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Eager load customer orders with nested orderItems and product information
        $orders = $customer->orders()
            ->with(['orderItems.product:id,name,code'])
            ->latest()
            ->get();

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'orders_count' => $orders->count(),
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'created_at' => $order->created_at?->toISOString(),
                    'subtotal' => $order->subtotal,
                    'tax_total' => $order->tax_total,
                    'grand_total' => $order->grand_total,
                    'items_count' => $order->orderItems->count(),
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product?->name,
                            'product_code' => $item->product?->code,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'tax_percent' => $item->tax_percent,
                            'line_subtotal' => $item->line_subtotal,
                            'line_tax' => $item->line_tax,
                            'line_total' => $item->line_total,
                        ];
                    }),
                ];
            }),
        ], Response::HTTP_OK);
    }
}
