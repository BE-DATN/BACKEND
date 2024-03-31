<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Action\LoginAdminAction;
use Illuminate\Http\Request;
use App\DTO\User\UserDTO;
use App\Http\Resources\AdminResource;
use App\Models\order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        try {
            $overview = [];
            $orders = [];
            $sold30d = [];
            $revenue30d = [];
            $user = getCurrentUser();
            $user = User::find(array_get($user, 'id'));
            // dd($user);

            if ($user->profile->roles->first()->name == "ADMIN") {
                // Overview data
                $overview['numCourses'] = DB::table('courses')->count();
                $overview['numPosts'] = DB::table('posts')->count();
                $overview['numUsers'] = DB::table('users')->count();
                $overview['numCertificates'] = DB::table('certificates')->count();

                // Orders data table
                $orders = order::select([
                    'orders.id',
                    'users.username',
                    'order_id',
                    'voucher',
                    'orders.order_status',
                    'total_amount',
                    'payment_method',
                    'orders.created_at',
                ])
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->get();
                // sold30d data chart
                $sold30d = order::getSold30Day();
                // dd($sold30d);
                $revenue30d = order::getRevenue30Day();
            } else if ($user->profile->roles->first()->name == "TEACHER") {
            }

            // dd($overview);


            $data = [
                'status' => true,
                'data' => [
                    'overview' => $overview,
                    'orders' => AdminResource::collection($orders),
                    'sold30d' => $sold30d,
                    'revenue' => $revenue30d,
                ]
            ];
            // dd($data);
            return response()->json($data, 200,);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }

        // $data = ['dashboard'];
    }

    /**
     * adminlogin
     */
    public function login(Request $request, LoginAdminAction $adminAction, UserDTO $userDTO)
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
        $adminAction->setTokenLifeTime($remember_me);

        // Login & get token
        $token = $adminAction->login($credentials, $remember_me);
        $adminAction->createCookie($token, $remember_me);

        if (!$token) {
            return response()->json([
                'action' => "Login",
                'status' => false,
                'message' => 'TK hay MK sai rồi kìa má',
            ], 401);
        }

        return $adminAction->respondWithToken($token);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
