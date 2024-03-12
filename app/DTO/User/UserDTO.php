<?php

namespace App\DTO\User;

class UserDTO
{
    public function dataLoginUser($user)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }
    public function dataUser($user) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }
}
