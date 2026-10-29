<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConcurrencyStockTest extends TestCase
{
    protected ?int $createdProductId = null;

    protected function tearDown(): void
    {
        if ($this->createdProductId) {
            $orderIds = OrderItem::where('product_id', $this->createdProductId)->pluck('order_id');
            OrderItem::where('product_id', $this->createdProductId)->delete();
            Order::whereIn('id', $orderIds)->delete();
            Product::where('id', $this->createdProductId)->delete();
        }

        Customer::whereIn('email', [
            'concurrent1@example.com',
            'concurrent2@example.com',
            'buyer1@example.com',
            'buyer2@example.com',
        ])->delete();

        parent::tearDown();
    }

    /**
     * Requirement 10: Multi-process concurrency test using the actual MySQL/InnoDB engine
     * and separate database connections/processes competing for the last unit.
     */
    public function test_two_concurrent_processes_competing_for_last_unit_on_database_engine(): void
    {
        // 1. Verify the database engine
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            $engine = DB::selectOne(
                "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products'"
            );
            $this->assertEquals('InnoDB', $engine->ENGINE ?? null, 'MySQL table must use InnoDB for row-level locking');
        }

        // 2. Create product with exactly 1 unit of stock committed to the database
        $product = Product::create([
            'name' => 'Limited Edition Concurrency Item',
            'code' => 'RACE-' . uniqid(),
            'price' => 500.00,
            'tax_percent' => 18.00,
            'stock' => 1,
        ]);

        $this->createdProductId = $product->id;
        $productId = $product->id;

        $phpBinary = PHP_BINARY;
        $autoloadPath = base_path('vendor/autoload.php');
        $bootstrapPath = base_path('bootstrap/app.php');
        $activeDb = config('database.connections.mysql.database');

        // 3. Worker script executed by each separate process
        $workerScript = <<<PHP
<?php
require '{$autoloadPath}';
\$app = require_once '{$bootstrapPath}';
\$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class);
\$kernel->bootstrap();

config(['database.connections.mysql.database' => '{$activeDb}']);
Illuminate\Support\Facades\DB::purge('mysql');

use App\\Services\\OrderService;
use App\\Exceptions\\InsufficientStockException;

\$service = app(OrderService::class);
\$email = \$argv[1];
\$productId = (int) \$argv[2];

try {
    \$order = \$service->createOrder([
        'customer_email' => \$email,
        'customer_name' => 'Concurrent Buyer',
        'products' => [
            ['product_id' => \$productId, 'quantity' => 1],
        ],
    ]);
    echo "SUCCESS: Order #" . \$order->id . PHP_EOL;
} catch (InsufficientStockException \$e) {
    echo "INSUFFICIENT_STOCK: " . \$e->getMessage() . PHP_EOL;
} catch (Throwable \$e) {
    echo "ERROR: " . \$e->getMessage() . PHP_EOL;
}
PHP;

        $tempWorkerFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'worker_concurrency_' . uniqid() . '.php';
        file_put_contents($tempWorkerFile, $workerScript);

        // 4. Launch two independent processes concurrently
        $cmd1 = "\"{$phpBinary}\" \"{$tempWorkerFile}\" \"concurrent1@example.com\" {$productId}";
        $cmd2 = "\"{$phpBinary}\" \"{$tempWorkerFile}\" \"concurrent2@example.com\" {$productId}";

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc1 = proc_open($cmd1, $descriptors, $pipes1);
        $proc2 = proc_open($cmd2, $descriptors, $pipes2);

        $out1 = stream_get_contents($pipes1[1]);
        fclose($pipes1[1]);
        proc_close($proc1);

        $out2 = stream_get_contents($pipes2[1]);
        fclose($pipes2[1]);
        proc_close($proc2);

        @unlink($tempWorkerFile);

        $outputs = [trim($out1), trim($out2)];
        $successCount = 0;
        $conflictCount = 0;

        foreach ($outputs as $out) {
            if (str_starts_with($out, 'SUCCESS:')) {
                $successCount++;
            } elseif (str_starts_with($out, 'INSUFFICIENT_STOCK:')) {
                $conflictCount++;
            }
        }

        // 5. Assertions: exactly one succeeded, exactly one received 409 conflict
        $this->assertEquals(1, $successCount, 'Exactly one concurrent process must succeed in claiming the last unit');
        $this->assertEquals(1, $conflictCount, 'Exactly one concurrent process must receive insufficient stock exception');

        // 6. Final stock in database must be exactly 0 (no overselling, no negative stock)
        $this->assertEquals(0, $product->fresh()->stock);

        // 7. Exactly one order exists in database for this product
        $this->assertEquals(1, Order::whereHas('orderItems', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        })->count());
    }

    /**
     * In-process transactional validation verifying lock-reading logic.
     */
    public function test_in_process_stock_validation_prevents_oversell(): void
    {
        $product = Product::create([
            'name' => 'Single Unit Item',
            'code' => 'SINGLE-' . uniqid(),
            'price' => 100.00,
            'tax_percent' => 18.00,
            'stock' => 1,
        ]);

        $this->createdProductId = $product->id;
        $service = app(OrderService::class);

        // Order 1 claims the last unit
        $order1 = $service->createOrder([
            'customer_email' => 'buyer1@example.com',
            'customer_name' => 'Buyer One',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $this->assertNotNull($order1);
        $this->assertEquals(0, $product->fresh()->stock);

        // Order 2 attempts to claim the same unit
        $this->expectException(InsufficientStockException::class);

        $service->createOrder([
            'customer_email' => 'buyer2@example.com',
            'customer_name' => 'Buyer Two',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);
    }
}
