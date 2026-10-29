<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | Products with a stock level strictly below this threshold will be
    | flagged as low stock in the low-stock API report.
    |
    */
    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Maximum Query Limit
    |--------------------------------------------------------------------------
    |
    | Prevents unbounded output on high-volume product catalogs.
    |
    */
    'max_query_limit' => (int) env('LOW_STOCK_MAX_LIMIT', 100),
];
