<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        for ($i = 1; $i <= 10; $i++) {
            DB::table('order_details')->insert([
                'order_id' => $i, 
                'course_id' => rand(1, 50), 
                'course_name' => "Tên Khóa học $i",
                'price' => rand(50, 200),
                'joined_course' => now(),
                'progess_learning' => rand(0, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
