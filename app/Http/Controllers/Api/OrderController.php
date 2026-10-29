<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Store a newly created order with concurrency-safe stock deduction.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
        } catch (InsufficientStockException $e) {
            return $e->render($request);
        }

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => [
                'id' => $order->id,
                'customer' => [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                ],
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_percent' => $item->tax_percent,
                        'line_subtotal' => $item->line_subtotal,
                        'line_tax' => $item->line_tax,
                        'line_total' => $item->line_total,
                    ];
                }),
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total,
                'grand_total' => $order->grand_total,
                'created_at' => $order->created_at?->toISOString(),
            ],
        ], Response::HTTP_CREATED);
    }
}
