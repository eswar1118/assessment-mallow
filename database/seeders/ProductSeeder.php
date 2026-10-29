<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Colgate Strong Teeth Toothpaste 200g',
                'code' => 'PROD-COLG-200',
                'price' => 110.00,
                'tax_percent' => 18.00,
                'stock' => 45,
            ],
            [
                'name' => 'Parle-G Gold Biscuits 1kg',
                'code' => 'PROD-PARL-1KG',
                'price' => 120.00,
                'tax_percent' => 5.00,
                'stock' => 60,
            ],
            [
                'name' => 'Amul Taaza Homogenised Toned Milk 1L',
                'code' => 'PROD-MILK-1L',
                'price' => 74.00,
                'tax_percent' => 0.00,
                'stock' => 3, // Intentionally low stock
            ],
            [
                'name' => 'Farm Fresh White Eggs (Pack of 6)',
                'code' => 'PROD-EGGS-6PK',
                'price' => 48.00,
                'tax_percent' => 0.00,
                'stock' => 30,
            ],
            [
                'name' => 'Modern Classic White Bread 400g',
                'code' => 'PROD-BRED-400',
                'price' => 45.00,
                'tax_percent' => 0.00,
                'stock' => 4, // Intentionally low stock
            ],
            [
                'name' => 'Tata Salt Vacuum Evaporated 1kg',
                'code' => 'PROD-SALT-1KG',
                'price' => 28.00,
                'tax_percent' => 0.00,
                'stock' => 85,
            ],
            [
                'name' => 'Aashirvaad Superior MP Atta 5kg',
                'code' => 'PROD-ATTA-5KG',
                'price' => 265.00,
                'tax_percent' => 5.00,
                'stock' => 25,
            ],
            [
                'name' => 'Fortune Sunlite Refined Sunflower Oil 1L',
                'code' => 'PROD-FOIL-1L',
                'price' => 145.00,
                'tax_percent' => 5.00,
                'stock' => 35,
            ],
            [
                'name' => 'Maggi 2-Minute Masala Instant Noodles (Pack of 4)',
                'code' => 'PROD-MAGG-4PK',
                'price' => 56.00,
                'tax_percent' => 12.00,
                'stock' => 50,
            ],
            [
                'name' => 'Tata Tea Premium Leaf Tea 500g',
                'code' => 'PROD-TTEA-500',
                'price' => 240.00,
                'tax_percent' => 5.00,
                'stock' => 2, // Intentionally low stock
            ],
            [
                'name' => 'Dettol Original Bathing Soap (Pack of 4 x 125g)',
                'code' => 'PROD-DETT-4PK',
                'price' => 195.00,
                'tax_percent' => 18.00,
                'stock' => 22,
            ],
            [
                'name' => 'Surf Excel Easy Wash Detergent Powder 1kg',
                'code' => 'PROD-SURF-1KG',
                'price' => 140.00,
                'tax_percent' => 18.00,
                'stock' => 40,
            ],
            [
                'name' => 'Daawat Rozana Super Basmati Rice 5kg',
                'code' => 'PROD-RICE-5KG',
                'price' => 385.00,
                'tax_percent' => 5.00,
                'stock' => 18,
            ],
            [
                'name' => 'Haldiram\'s Nagpur Bhujia Sev 400g',
                'code' => 'PROD-HALD-400',
                'price' => 115.00,
                'tax_percent' => 12.00,
                'stock' => 30,
            ],
            [
                'name' => 'Bru Instant Coffee 100g',
                'code' => 'PROD-BRUC-100',
                'price' => 210.00,
                'tax_percent' => 18.00,
                'stock' => 15,
            ],
            [
                'name' => 'Amul Salted Butter 500g',
                'code' => 'PROD-BUTR-500',
                'price' => 275.00,
                'tax_percent' => 12.00,
                'stock' => 5, // Intentionally low stock
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['code' => $product['code']],
                $product
            );
        }
    }
}
