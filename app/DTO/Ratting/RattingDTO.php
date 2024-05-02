<?php

namespace App\DTO\Ratting;

class RattingDTO
{
    public function dataLoginUser($user)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }
    
}
