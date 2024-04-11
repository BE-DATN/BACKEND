<?php

namespace App\DTO\Admin;

class AdminUserDTO
{
    public function tranformRequest($request)
    {
        return [
            // 'post_id' => $user->id,
        ];
    }

    public function userDetail($user) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'description' => $user->description,
            'email' => $user->email,
            'status' => $user->status,
            'created_at' => date('Y-m-d H:i:s', strtotime($user->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($user->updated_at)),
            'permission' => $user->profile->roles->first()->name,
            'profile' => [
                'firstname' => $user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'gender' => $user->profile->gender,
                'phone' => $user->profile->phone,
                'address' => $user->profile->address,
                'avata_img' => $user->profile->avata_img,
            ]

        ];
    }
}
