<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $products = Product::query()
        ->select(['id', 'name', 'code', 'price', 'tax_percent', 'stock'])
        ->orderBy('name', 'asc')
        ->get();

    return view('pos', compact('products'));
});
