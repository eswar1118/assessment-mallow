<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly int $availableStock,
        public readonly int $requestedQuantity,
        string $message = ''
    ) {
        $msg = $message ?: "Insufficient stock for product '{$productName}'. Available: {$availableStock}, requested: {$requestedQuantity}.";
        parent::__construct($msg, Response::HTTP_CONFLICT);
    }

    /**
     * Render the exception as an HTTP JSON response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => 'insufficient_stock',
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'available_stock' => $this->availableStock,
            'requested_quantity' => $this->requestedQuantity,
        ], Response::HTTP_CONFLICT);
    }
}
