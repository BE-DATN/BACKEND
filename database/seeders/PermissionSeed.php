<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $id = 1;
        Permission::insert(
            [
                [
                    'id' => $id++,
                    'name' => "ADMIN" ,
                    'description' => 'BOSS'
                ],
                // COURSE

                [
                    'id' => $id++,
                    'name' => "AUTHOR_COURSE",
                    'description' => 'CREATE_EDIT_DELETE'
                ],
                // [
                //     'id' => $id++,
                //     'name' => "CREATE_COURSE",
                //     'description' => ''
                // ],
                // [
                //     'id' => $id++,
                //     'name' => "EDIT_COURSE",
                //     'description' => ''
                // ],
                // [
                //     'id' => $id++,
                //     'name' => "DELETE_COURSE",
                //     'description' => ''
                // ],
                // POST

                [
                    'id' => $id++,
                    'name' => "AUTHOR_POST",
                    'description' => 'CREATE_EDIT_DELETE'
                ],
                // [
                //     'id' => $id++,
                //     'name' => "CREATE_POST",
                //     'description' => ''
                // ],
                // [
                //     'id' => $id++,
                //     'name' => "EDIT_POST",
                //     'description' => ''
                // ],
                // [
                //     'id' => $id++,
                //     'name' => "DELETE_POST",
                //     'description' => ''
                // ],
                // 
                [
                    'id' => $id++,
                    'name' => "COMMENT",
                    'description' => ''
                ],
                [
                    'id' => $id++,
                    'name' => "RATING_COURSE",
                    'description' => ''
                ],
                // 
                [
                    'id' => $id++,
                    'name' => "LEARN_COURSE",
                    'description' => ''
                ],
                // [
                //     'id' => $id++,
                //     'name' => "QUIZ",
                //     'description' => ''
                // ],
            ]
        );
    }
}
