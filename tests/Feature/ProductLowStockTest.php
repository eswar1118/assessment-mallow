<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLowStockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement 8: Low-stock endpoint behavior at, below, and above the threshold.
     */
    public function test_low_stock_filters_strictly_below_threshold_and_includes_zero_stock(): void
    {
        // 1. Below threshold (< 5)
        $productBelow = Product::factory()->create([
            'name' => 'Item Below Threshold',
            'code' => 'BELOW-01',
            'stock' => 4,
        ]);

        // 2. Zero stock (< 5)
        $productZero = Product::factory()->create([
            'name' => 'Item Zero Stock',
            'code' => 'ZERO-01',
            'stock' => 0,
        ]);

        // 3. Exactly at threshold (== 5) -> must NOT be included
        $productAt = Product::factory()->create([
            'name' => 'Item At Threshold',
            'code' => 'AT-05',
            'stock' => 5,
        ]);

        // 4. Above threshold (> 5) -> must NOT be included
        $productAbove = Product::factory()->create([
            'name' => 'Item Above Threshold',
            'code' => 'ABOVE-10',
            'stock' => 10,
        ]);

        // Default threshold is 5
        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200)
            ->assertJsonPath('effective_threshold', 5)
            ->assertJsonPath('count', 2);

        $returnedCodes = collect($response->json('products'))->pluck('code')->all();

        $this->assertContains('ZERO-01', $returnedCodes, 'Zero stock product must be included as it is below threshold');
        $this->assertContains('BELOW-01', $returnedCodes, 'Stock 4 product must be included as it is below threshold');
        $this->assertNotContains('AT-05', $returnedCodes, 'Stock 5 product must NOT be included (strictly below rule)');
        $this->assertNotContains('ABOVE-10', $returnedCodes, 'Stock 10 product must NOT be included');

        // Verify fields returned
        $zeroItem = collect($response->json('products'))->firstWhere('code', 'ZERO-01');
        $this->assertEquals(0, $zeroItem['current_stock']);
        $this->assertTrue($zeroItem['is_out_of_stock']);
        $this->assertEquals(5, $zeroItem['threshold']);
    }

    public function test_low_stock_supports_custom_threshold_query_parameter(): void
    {
        Product::factory()->create(['code' => 'ITEM-03', 'stock' => 3]);
        Product::factory()->create(['code' => 'ITEM-07', 'stock' => 7]);
        Product::factory()->create(['code' => 'ITEM-15', 'stock' => 15]);

        // Query with custom threshold = 10 (should return items with stock 3 and 7)
        $response = $this->getJson('/api/products/low-stock?threshold=10');

        $response->assertStatus(200)
            ->assertJsonPath('effective_threshold', 10)
            ->assertJsonPath('is_custom_threshold', true)
            ->assertJsonPath('count', 2);

        $returnedCodes = collect($response->json('products'))->pluck('code')->all();
        $this->assertContains('ITEM-03', $returnedCodes);
        $this->assertContains('ITEM-07', $returnedCodes);
        $this->assertNotContains('ITEM-15', $returnedCodes);
    }

    public function test_low_stock_validation_rejects_negative_threshold(): void
    {
        $response = $this->getJson('/api/products/low-stock?threshold=-1');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['threshold']);
    }
}
