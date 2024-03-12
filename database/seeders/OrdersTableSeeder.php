<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('orders')->insert([
                'user_id' => rand(1, 20), 
                'total_amount' => rand(100, 1000),
                'payment_method' => "VNPAY",
                'voucher' => 'null',
                'order_status' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }   //
        
    }
}
