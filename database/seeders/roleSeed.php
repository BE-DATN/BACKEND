<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class roleSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert(
            [
                [
                    'id' => 1,
                    'name' => "GUEST",
                    'level' => 0,
                    'description' => ''
                ],
                [
                    'id' => 2,
                    'name' => "STUDENT",
                    'level' => 1,
                    'description' => ''
                ],
                [
                    'id' => 3,
                    'name' => "TEACHER",
                    'level' => 2,
                    'description' => ''
                ],
                [
                    'id' => 4,
                    'name' => "ADMIN" ,
                    'level' => 3,
                    'description' => ''
                ]
            ]
        );
    }
}
