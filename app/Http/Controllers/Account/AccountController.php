<?php

namespace App\Http\Controllers\Account;

use App\DTO\User\UserDTO;
use App\Http\Controllers\Account\Action\LoginUserAction;
use App\Http\Controllers\Account\Action\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountController extends Controller
{
    public function __construct() {
        // method ngoai le
        $except = [
            'login',
            'loginp',
            'register',
            'registerp',
            'userLogin',
            'userRegister',
            // 'refresh',
        ];
        $this->middleware('auth:api', ['except' => $except]);
        // $this->middleware('auth:api', ['except' => $except]);
    }

    public function userLogin(Request $request, LoginUserAction $userAction)
    {
        // dd($request->all());
        $remember_me = $request->input('remember_token');
        $firstCredentialValueType = isEmail($request->input('account')) ? 'email' : 'username';
        // Tạo thông tin đăng nhập tk / mk
        $credentials = [
            $firstCredentialValueType => $request->input('account'),
            'password' => $request->input('password')
        ];

        // Set hsd token
        $userAction->setTokenLifeTime($remember_me);

        // Login & get token
        $token = $userAction->login($credentials, $remember_me);
        $userAction->createCookie($token, $remember_me);

        if (!$token) {
            return response()->json([
                'action' => "Login",
                'status' => false,
                'message' => 'TK hay MK sai rồi kìa má',
            ], 401);
        }

        return $userAction->respondWithToken($token);
    }


    public function userRegister(RegisterUserAction $userAction) {
        $user = $userAction->createUser();

        // dd(array_get($user->original, 'error'));

        if ($user->original && array_get($user->original, 'status')) {
            return response()->json([
                'status' => true,
                'message' => 'Tài khoản đã được tạo thành công!',
                'user' => array_get($user->original, 'data')
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi tạo tài khoản!',
                'errors' => array_get($user->original, 'errors')
            ]);

        }
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Cookie::forget('user');
        auth()->logout();
        // Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ])->withCookie('user', null, 0);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // try {
        //     $refresh_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI5IiwicmFuZG9tIjoiOTQ5MjAwNDcxNzA5MTkzMDEwIiwiZXhwIjoxNzA5MTkzMDEwMjE2MDB9.uPXbMWh-d8lODR6YGbnfTrrep5eI-uuFiY7ScijL4hU';
        //     $decoded = JWTAuth::getJWTProvider()->decode($refresh_token);
        //     $user = User::find($decoded['sub']);//find sub = user_id
        //     if (!$user) {
        //         return response()->json(['error' => "Không tìm thấy người dùng có  ID là {$decoded['sub']}"], 404);
        //     }
        //     return response()->json($user);
        // } catch (\Throwable $th) {
        //     return response()->json(['error' => 'Refresh Token không đúng hoặc đã hết hạn'], 500);
        //     //throw $th;

        // }
        // return $this->respondWithToken(auth()->refresh());
        return response()->json(['message' => 'method refresh token', 'status' => 'coming soon']);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */

     public function login()
    {
        $data = [
            "page" => "login",
        ];
        return response()->json($data);
    }

    public function register()
    {
        $data = [
            "page" => "register",
        ];
        return response()->json($data);
    }

}
