<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
            ],
            [
                'name' => 'Priya',
                'email' => 'priya@example.com',
            ],
            [
                'name' => 'Aravind',
                'email' => 'aravind@example.com',
            ],
            [
                'name' => 'Ravi',
                'email' => 'ravi@example.com',
            ],
            [
                'name' => 'Anitha',
                'email' => 'anitha@example.com',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }
    }
}
