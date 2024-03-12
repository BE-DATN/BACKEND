<?php

namespace Database\Seeders;

use App\Models\Permission_role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Permission_role_seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Permission_role::insert([
            // [
            //     'role_id' => 1, //GUEST
            //     // 'permission_id' => 1,
            //     // Không có quyền gì hết
            // ],
            [
                'role_id' => 2, //STUDENT
                'permission_id' => 5, //RATING_COURSE
            ],
            [
                'role_id' => 2, //STUDENT
                'permission_id' => 6, //LEARN_COURSE
            ],
            [
                'role_id' => 3, //TEACHER
                'permission_id' => 2, //AUTHOR_COURSE
            ],
            [
                'role_id' => 3, //TEACHER
                'permission_id' => 3, //AUTHOR_POST
            ],
            [
                'role_id' => 4, //ADMIN
                'permission_id' => 1, //ADMIN
            ]
        ]);
    }
}
