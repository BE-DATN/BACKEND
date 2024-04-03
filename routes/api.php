<?php

use App\DTO\Post\PostDTO;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Post\PostController;
use App\Http\Resources\LogsResource;
use App\Models\log;
use App\Models\order;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['cors'],
], function () {
    Route::get('/', function () {
        return response()->json([
            "Project" => env('APP_NAME'),
            "Status" => "ok!",
            "Php" => phpversion(),
            "Laravel" => app()->version(),
        ], 200);
    });
    Route::get('/get-cookie', function () {

        $cookieValue = request()->cookie('user');
        $dataArray = json_decode($cookieValue, true);

        return response()->json($dataArray);
    });

    Route::get('test-db', function () {
        try {
            $dbconnect = DB::connection()->getPDO();
            $DBname = DB::connection()->getDatabaseName();
            $message = [
                'Message' => "Connected successfully to the database.",
                "Database" => "Database name is: $DBname"
            ];
            return response()->json($message, 200);
        } catch (Exception $e) {
            $message = [
                'Message' => "Error in connecting to the database"
            ];
            return response()->json($message, 500);
        }
    });

    Route::group([
        'prefix' => 'account',
    ], function () {
        // Route::get('login', [AccountController::class, 'login']);
        Route::get('login', [AccountController::class, 'login'])->name('user.login');
        Route::get('register', [AccountController::class, 'register'])->name('user.register');

        Route::post('loginp', [AccountController::class, 'userLogin'])->name('user.post.login');
        Route::post('registerp', [AccountController::class, 'userRegister'])->name('user.post.register');

        Route::group([
            'middleware' => ['auth'], //, 'jwt.auth',
        ], function () {
            Route::get('refresh', [AccountController::class, 'refresh']);
            Route::get('me', [AccountController::class, 'me']);
            Route::post('logout', [AccountController::class, 'logout']);
        });
    });


    // Post Route
    Route::group([
        'prefix' => 'post',
    ], function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'show'])->whereNumber('id');

        Route::group([
            'middleware' => ['auth', 'author.auth'],
        ], function () {
            Route::get('/list-owned-posts/{userId?}', [PostController::class, 'list'])->whereNumber('userId');
            Route::post('/create', [PostController::class, 'store']);
            Route::post('/comment/{id}', [PostController::class, 'comment'])->whereNumber('id');

            Route::post('/edit/{id}', [PostController::class, 'update'])->whereNumber('id')->middleware('action.auth');
            Route::post('/delete/{id}', [PostController::class, 'destroy'])->whereNumber('id')->middleware('action.auth');
        });
    });

    // Course Route
    Route::group([
        'prefix' => 'course',
    ], function () {
        Route::get('/', [CourseController::class, 'index']);
        Route::get('/{id}', [CourseController::class, 'show'])->whereNumber('id');

        Route::get('/getSession', [CourseController::class, 'getSession']);

        Route::group([
            'middleware' => ['auth', 'author.auth'],
        ], function () {
            Route::get('/list-owned-courses/{userId?}', [CourseController::class, 'list'])->whereNumber('userId');
            Route::post('/create', [CourseController::class, 'store']);
            Route::post('/edit/{id}', [CourseController::class, 'update'])->whereNumber('id');
            Route::post('/delete/{id}', [CourseController::class, 'destroy'])->whereNumber('id');
            Route::get('/create-session', [CourseController::class, 'addSession']);
            Route::get('/create-lesson', [CourseController::class, 'addLession']);
        });
    });

    // Admin Route
    Route::group([
        'prefix' => 'admin',
    ], function () {
        Route::group([
            'middleware' => ['auth', 'author.auth'],
        ], function () {
            Route::get('/', [AdminController::class, 'index']);

            // Route::get
        });
    });



    // Route Cart
    Route::group([
        'prefix' => 'cart',
    ], function () {
        Route::group([
            'middleware' => ['auth'],
        ], function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add-item/{id}', [CartController::class, 'addCart'])->whereNumber('id');
            Route::post('/remove-cart', [CartController::class, 'remoteCart']);
            Route::post('/delete-item/{id}', [CartController::class, 'deleteCart'])->whereNumber('id');
        });
    });

    // Route Order
    Route::group([
        'prefix' => 'order',
    ], function () {
        Route::get('/redirect-notification', [OrderController::class, 'result']);
        Route::post('/payment-notification', [OrderController::class, 'apn']);
        Route::group([
            'middleware' => ['auth'],
        ], function () {
            Route::get('/{id}', [OrderController::class, 'viewOrder'])->whereNumber('id');
            Route::get('/detail/{id}', [OrderController::class, 'viewOrderDetail'])->whereNumber('id');
            Route::get('/pay', [OrderController::class, 'order']);
            Route::get('/vnp-redirect', [OrderController::class, 'vnp_return']);
            Route::get('/vnp-ipn', [OrderController::class, 'vmp_apn']);
        });
    });

    // Route::get('form', function() {
    //     return view('form');
    // });

    Route::get('view-post/{id}', function ($id, PostDTO $postDTO) {
        //
        $data = Post::where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy bài viết này.',
            ]);
        }
        $data = $postDTO->postsDetail($data);
        return view('edit', $data);
    });

    // Route::get('course', function() {
    //     $courses = DB::table('courses')->select('*')->limit(5)->get(5);
    //     // dd($course);
    //     return view('course', compact('courses'));
    // });

    Route::get('add-session', function () {
        return view('addSession');
    });
    Route::get('add-lesson', function () {
        return view('addLesson');
    });
    // Route::get('test-query', function() {
    //     order::where('order_id', "1711077087")->first()->update(['order_status' => 100]);
    // });
    Route::get('logs', function () {
        $logs = log::select('*')->orderBy('created_at', 'asc')->limit(5)->get();
        return response()->json(LogsResource::collection($logs), 200);
    });
})->middleware('cors');
Route::get('query', function () {
    $user = getCurrentUser();
    // dd($user);
    $data = Order::join('order_details', 'orders.id', '=', 'order_details.order_id')
        ->where('orders.user_id', array_get($user, 'id'))
        ->where('orders.order_status', 1)
        ->select('order_details.course_id', 'order_details.course_name')
        ->distinct()
        ->get();
    // $data = order::select($select)
    // ->join(
    //     'order_details',
    //     'orders.id',
    //     '=',
    //     'order_details.order_id'
    // )
    // ->where('order_status', 1)
    // ->where('user_id', array_get($user, 'id'))
    // ->distinct('order_details.course_id')
    // ->get();

    return response()->json($data, 200);
});
