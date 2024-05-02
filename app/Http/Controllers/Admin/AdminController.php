<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\AdminOrdersExport;
use App\Http\Resources\AdminResource;
use App\Models\order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
                    ->orderBy('created_at', 'desc')
                    ->get();

                // sold30d data chart
                $sold30d = order::getSold30Day();
                $revenue30d = order::getRevenue30Day();
                // dd($revenue30d);
            } else if ($user->profile->roles->first()->name == "TEACHER") {
                $user = getCurrentUser();
                $overview['numCourses'] = DB::table('courses')->where('created_by', array_get($user, 'id'))->count();
                $overview['numPosts'] = DB::table('posts')->where('created_by', array_get($user, 'id'))->count();
                $overview['numUsers'] = DB::table('users')->count();
                $overview['numCertificates'] = DB::table('certificates')->count();

                $orders = Order::select([
                    'orders.id',
                    'users.username',
                    'orders.order_id',
                    'voucher',
                    'orders.order_status',
                    'total_amount',
                    'payment_method',
                    'orders.created_at',
                ])
                    ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->join('courses', 'order_details.course_id', '=', 'courses.id')
                    ->where('courses.created_by', array_get($user, 'id'))
                    ->orderBy('created_at', 'desc')
                    ->get();

                $sold30d = order::getSold30Day();
                // dd($sold30d);
                $revenue30d = order::getRevenue30Day();
            }

            // dd($overview);

            if ($request->input('export') && $request->input('export') == 1) {
                return $this->order_export($orders);
            }
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
    // public function login(Request $request, LoginAdminAction $adminAction, UserDTO $userDTO)
    // {
    //     // dd($request->all());
    //     $remember_me = $request->input('remember_token');
    //     $firstCredentialValueType = isEmail($request->input('account')) ? 'email' : 'username';
    //     // Tạo thông tin đăng nhập tk / mk
    //     $credentials = [
    //         $firstCredentialValueType => $request->input('account'),
    //         'password' => $request->input('password')
    //     ];

    //     // Set hsd token
    //     $adminAction->setTokenLifeTime($remember_me);

    //     // Login & get token
    //     $token = $adminAction->login($credentials, $remember_me);
    //     $adminAction->createCookie($token, $remember_me);

    //     if (!$token) {
    //         return response()->json([
    //             'action' => "Login",
    //             'status' => false,
    //             'message' => 'TK hay MK sai rồi kìa má',
    //         ], 401);
    //     }

    //     return $adminAction->respondWithToken($token);
    // }


    /**
     * Store a newly created resource in storage.
     */
    public function order_export($data)
    {
        // dd($data[0]);
        if (!$data || count($data) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Không có đơn hàng nào để in ra'
            ]);
        }
        $titles = array_keys($data[0]->toArray());
        unset($titles[0]);
        // dd($data);
        // $user = User::find(array_get(getCurrentUser(), 'id'));
        $user = getCurrentUser();
        // dd($user);
        $fileName = "Order_Report_" . time() . "_" . array_get($user, 'username') . ".xlsx";
        $data = $data->toArray();
        return Excel::download(new AdminOrdersExport($data, $titles), $fileName, ExcelExcel::XLSX);
    }
}
