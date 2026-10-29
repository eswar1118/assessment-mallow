<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetLowStockProductsRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Retrieve all active products for the retail counter catalog.
     */
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->select(['id', 'name', 'code', 'price', 'tax_percent', 'stock'])
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Rule on Zero Stock:
     * - The filter condition is `stock < effective_threshold`.
     * - Because `products.stock` is an unsigned integer (>= 0), products with stock = 0
     *   (completely out of stock) ARE included whenever threshold > 0 (since 0 < threshold).
     * - When threshold is set to 0, no products are returned since stock cannot be < 0.
     *
     * Threshold Resolution:
     * - Query parameter `threshold` if provided and valid (>= 0).
     * - Fallback: `config('inventory.low_stock_threshold')` (default: 5, env: LOW_STOCK_THRESHOLD).
     *
     * Unbounded Output Protection:
     * - Clamped by query parameter `limit` or `config('inventory.max_query_limit')` (default: 100).
     */
    public function lowStock(GetLowStockProductsRequest $request): JsonResponse
    {
        // 1. Resolve effective threshold (query parameter takes precedence over config default)
        $defaultThreshold = (int) config('inventory.low_stock_threshold', 5);
        $effectiveThreshold = $request->filled('threshold')
            ? (int) $request->validated('threshold')
            : $defaultThreshold;

        // 2. Resolve safe query limit
        $defaultLimit = (int) config('inventory.max_query_limit', 100);
        $effectiveLimit = $request->filled('limit')
            ? (int) $request->validated('limit')
            : $defaultLimit;

        // 3. Query products strictly below threshold
        $products = Product::query()
            ->select(['id', 'name', 'code', 'price', 'stock'])
            ->where('stock', '<', $effectiveThreshold)
            ->orderBy('stock', 'asc')
            ->orderBy('name', 'asc')
            ->limit($effectiveLimit)
            ->get();

        return response()->json([
            'effective_threshold' => $effectiveThreshold,
            'is_custom_threshold' => $request->filled('threshold'),
            'rule' => 'stock < effective_threshold (zero stock included)',
            'count' => $products->count(),
            'products' => $products->map(function ($product) use ($effectiveThreshold) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'price' => $product->price,
                    'current_stock' => $product->stock,
                    'threshold' => $effectiveThreshold,
                    'is_out_of_stock' => $product->stock === 0,
                ];
            }),
        ], Response::HTTP_OK);
    }
}
