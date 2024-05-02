<?php

namespace App\Http\Controllers\Account;

use App\DTO\User\UserDTO;
use App\Http\Controllers\Account\Action\LoginUserAction;
use App\Http\Controllers\Account\Action\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Models\order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
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


    public function userRegister(RegisterUserAction $userAction, Request $request) {
        // return response()->json($request->input());

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
    public function myOrder()
    {
        try {
            $user = getCurrentUser();
            $orders = order::select([
                'orders.id',
                'orders.user_id',
                'users.username',
                'order_id',
                'voucher',
                'orders.order_status',
                'total_amount',
                'payment_method',
                'orders.created_at',
                'orders.checkoutUrl',
            ])
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->where('orders.user_id', array_get($user, 'id'))
                ->get();
            $data = [
                'status' => true,
                'orders' => AdminResource::collection($orders)
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            //throw $th;
            $data = [
                'status' => false,
                'error' => $th->getMessage()
            ];
            return response()->json($data, 400);
        }
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

    public function changePass(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = auth()->user();
        // dd($user);
        if (!Hash::check($request->current_password, $user->password)) {
            // return redirect()->back()->with('error', 'Current password is incorrect.');
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu hiện tại không chính xác',
            ], 200);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json([
            'status' => true,
            'message' => 'Mật khẩu đã được thay đổi',
        ], 200);


    }

}
