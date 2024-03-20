<?php

use App\DTO\Post\PostDTO;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Post\PostController;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
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

    Route::get('loginp', [AccountController::class, 'userLogin'])->name('user.post.login');
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
// Route::group([
//     'prefix' => 'admin',
// ], function () {
//     Route::post('login', [AdminController::class, 'login']);

//     Route::group([
//         'middleware' => ['auth:ad', 'admin.auth', 'author.auth'],
//     ], function () {
//         Route::get('/', [AdminController::class, 'index']);
//         Route::get('/checklogin', function() {
//             $admin = auth('ad')->user();
//             return response()->json($admin, 200);
//         });
//         // Route::get
//     });
// });



// Route Cart
Route::group([
    'prefix' => 'cart',
], function () {
    Route::group([
        'middleware' => ['auth'],
    ], function () {
        Route::get('/', [CartController::class, 'index']);
        Route::get('/remove-cart', [CartController::class, 'remoteCart']);
        Route::get('/add-item/{id}', [CartController::class, 'addCart']);
        Route::get('/delete-item/{id}', [CartController::class, 'deleteCart']);
    });
});

// Route Order
Route::group([
    'prefix' => 'order',
], function () {
    Route::group([
        'middleware' => ['auth'],
    ], function () {
        Route::get('/pay', [OrderController::class, 'order']);
        Route::get('/result', [OrderController::class, 'result']);
        Route::get('/apn', [OrderController::class, 'apn']);
    });
});

// Route::get('form', function() {
//     return view('form');
// });

Route::get('view-post/{id}', function ($id, PostDTO $postDTO)
{
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
