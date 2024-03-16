<?php

namespace App\Http\Controllers\Admin\Action;

use App\DTO\User\UserDTO;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

// use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginAdminAction
{
    protected $token = null;
    protected $cookie = null;
    public function login($credentials)
    {
        return $this->token = auth('ad')->attempt($credentials);
    }

    public function setTokenLifeTime($remember_me = false)
    {

        if ($remember_me) {
            JWTAuth::factory()->setTTL(env('JWT_REMEMBER_TTL'));
        } else {
            JWTAuth::factory()->setTTL(env('JWT_TTL'));
        }
    }

    public function createCookie($token, $remember_me = false)
    {
        $time = $remember_me ? env('JWT_REMEMBER_TTL') : env('JWT_TTL');
        $cookie = [
            'data' =>  $this->getUserLogin(),
            'token_type' => 'bearer',
            'access_token' => $token,
        ];
        $this->cookie = Cookie::make('user', json_encode($cookie), $time);
        return;
    }

    public function respondWithToken($token)
    {
        $user = $this->getUserLogin();

        return response()->json([
            'data' => $this->tranferLoginData($user),
            'token_type' => 'bearer',
            'access_token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL() * 60 . ' second',
            'permission' => $user->profile->roles->first()->name
            // 'refresh_token' => $refresh_token,
        ])->withCookie($this->cookie);
    }

    public function formatLifeTimeToken()
    {
    }

    public function getUserLogin()
    {
        return Auth::guard('ad')->user();
    }

    public function tranferLoginData($user)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }





    public function createRefreshToken($credentials, $remember_me)
    {
        if ($remember_me) {
            JWTAuth::factory()->setTTL(env('JWT_REMEMBER_TTL') + 10080);//+7Day
        } else {
            JWTAuth::factory()->setTTL(env('JWT_TTL') + 10080);
        }
        return auth('api')->attempt($credentials);
    }





    // public function RefreshCookie($token, $remember_me)
    // {
    //     if ($remember_me) {
    //         $time = JWTAuth::factory()->setTTL(env('JWT_REMEMBER_TTL') + 10080);//+7Day
    //     } else {
    //         $time = JWTAuth::factory()->setTTL(env('JWT_TTL') + 10080);
    //     }

    //     $refreshToken = JWTAuth::getJWTProvider()->decode($token);
    //     dd($refreshToken);
    //     $user = User::find($refreshToken['sub']);//find sub = user_id

    //     $cookie = [
    //         'data' =>  $this->getUserLogin(),
    //         'token_type' => 'bearer',
    //         'access_token' => $token,
    //     ];
    //     $this->cookie = Cookie::make('user', json_encode($cookie), $time);
    // }

    // public function readToken($token) {
    //     return JWTAuth::getJWTProvider()->decode($token);
    // }
}
