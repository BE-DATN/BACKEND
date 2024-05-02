<?php

use App\DTO\Post\PostDTO;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Resources\LogsResource;
use App\Http\Resources\QuizResource;
use App\Models\Course;
use App\Models\log;
use App\Models\order;
use App\Models\Post;
use App\Models\quizzProgress;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['cors'],
], function () {
    Route::get('/', function () {
        return response()->json([
            // "Project" => env('APP_NAME'),
            // "Status" => "ok!",
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
        Route::get('login', [AccountController::class, 'login'])->name('user.login');
        // Route::get('register', [AccountController::class, 'register'])->name('user.register');

        Route::post('loginp', [AccountController::class, 'userLogin'])->name('user.post.login');
        Route::post('registerp', [AccountController::class, 'userRegister'])->name('user.post.register');

        Route::group([
            'middleware' => ['auth'], //, 'jwt.auth',
        ], function () {
            Route::post('change-pass', [AccountController::class, 'changePass']);
            Route::get('refresh', [AccountController::class, 'refresh']);
            Route::get('me', [AccountController::class, 'me']);
            Route::get('myOrder', [AccountController::class, 'myOrder']);
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
            Route::get('/show-edit/{id}', [PostController::class, 'showAdmin'])->whereNumber('id');
            Route::get('/list-owned-posts/{userId?}', [PostController::class, 'list'])->whereNumber('userId');
            Route::post('/create', [PostController::class, 'store']);
            
            Route::post('/edit/{id}', [PostController::class, 'update'])->whereNumber('id')->middleware('action.auth');
            Route::post('/delete/{id}', [PostController::class, 'destroy'])->whereNumber('id')->middleware('action.auth');
        });
        Route::group([
            'middleware' => ['auth'],
        ], function () {
            Route::post('/comment/{id}', [PostController::class, 'comment'])->whereNumber('id');

            // Route::post('/delete/{id}', [PostController::class, 'destroy'])->whereNumber('id')->middleware('action.auth');
        });
    });

    // Course Route
    Route::group([
        'prefix' => 'course',
    ], function () {
        Route::get('/', [CourseController::class, 'index']);
        Route::get('/{id}', [CourseController::class, 'show'])->whereNumber('id');


        Route::group([
            'middleware' => ['auth', 'author.auth'],
        ], function () {
            Route::get('/list-owned-courses/{userId?}', [CourseController::class, 'list'])->whereNumber('userId');
            Route::post('/create', [CourseController::class, 'store']);
            Route::get('/show-edit/{id}', [CourseController::class, 'adminShow'])->whereNumber('id');
            Route::post('/edit/{id}', [CourseController::class, 'update'])->whereNumber('id');
            Route::post('/delete/{id}', [CourseController::class, 'destroy'])->whereNumber('id');
            Route::post('/delete/session/{id}', [CourseController::class, 'destroySession'])->whereNumber('id');
            Route::post('/delete/lesson/{id}', [CourseController::class, 'destroyLesson'])->whereNumber('id');
            Route::post('/create-session', [CourseController::class, 'addSession']);
            Route::post('/create-lesson', [CourseController::class, 'addLession']);
            // quiz
            Route::post('/create-quiz', [QuizController::class, 'createQuiz']);
        });

        Route::group([
            'middleware' => ['auth'],
        ], function () {
            Route::get('/getSession/{courseId}', [CourseController::class, 'getSession']);
            Route::get('/getLesson/{sesssionId}', [CourseController::class, 'getLesson']);
            Route::get('/purchased_courses', [CourseController::class, 'purchased_courses']);
            Route::post('/rating-course/{id}', [CourseController::class, 'ratingCourse'])->whereNumber('id');
            Route::get('/purchased_courses/{id}', [CourseController::class, 'showPurchasedCourse']);
            Route::post('/do-quiz', [QuizController::class, 'doQuiz']);
        });
    });

    // Admin Route
    Route::group([
        'prefix' => 'admin',
    ], function () {
        Route::group([
            // 'middleware' => ['auth', 'author.auth'],
            'middleware' => ['auth'],
        ], function () {
            Route::get('/', [AdminController::class, 'index']);
            Route::get('/users', [UserController::class, 'getUser'])->whereNumber('id');
            Route::get('/user/{id}', [UserController::class, 'getUserById']);
            Route::post('/user/create', [UserController::class, 'store']);
            Route::get('/get-role', [UserController::class, 'getRole']);
            Route::post('/set-role', [UserController::class, 'setRole']);
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
        Route::get('/vnp-redirect', [OrderController::class, 'vnp_return']);
        Route::get('/vnp-ipn', [OrderController::class, 'vmp_apn']);
        Route::group([
            'middleware' => ['auth'],
        ], function () {
            Route::get('/{id}', [OrderController::class, 'viewOrder'])->whereNumber('id');
            Route::get('/detail/{id}', [OrderController::class, 'viewOrderDetail'])->whereNumber('id');
            Route::get('/pay', [OrderController::class, 'order']);
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
        $logs = log::select('*')->orderBy('id', 'desc')->limit(5)->get();
        return response()->json(LogsResource::collection($logs), 200);
    });
})->middleware('cors');
Route::get('query', function () {
    $user = getCurrentUser();
    // dd($user);
    // $user = User::find(array_get($user, 'id'));

    // $data = Order::join('order_details', 'orders.id', '=', 'order_details.order_id')
    //     ->where('orders.user_id', array_get($user, 'id'))
    //     ->where('orders.order_status', 1)
    //     ->select('order_details.course_id', 'order_details.course_name')
    //     ->distinct()
    //     ->get();
    // dd()
    // $data = Order::select([
    //     'orders.id',
    //     'users.username',
    //     'orders.order_id',
    //     'voucher',
    //     'orders.order_status',
    //     'total_amount',
    //     'payment_method',
    //     'orders.created_at',
    // ])
    // ->join('order_details', 'orders.id', '=', 'order_details.order_id')
    // ->join('users', 'orders.user_id', '=', 'users.id')
    // ->join('courses', 'order_details.course_id', '=', 'courses.id')
    // ->where('courses.created_by', array_get($user, 'id'))
    // ->get();

    // $data = DB::table('order_details')
    // ->join('orders', 'order_details.order_id', '=', 'orders.id')
    // ->join('courses', 'order_details.course_id', '=', 'courses.id')
    // ->where('orders.user_id', array_get($user, 'id'))
    // ->where('orders.order_status', 1)
    // ->select('courses.*')
    // ->distinct()
    // ->get();
    // $data = DB::table('quizzes')
    // ->join('questions', 'quizzes.id', '=', 'questions.quizz_id')
    // ->select('questions.id as question_id', 'quizz_id', 'question')
    // ->where('lesson_id', 4)
    // ->get();
    // $data = Course::find(1)
    // ->join('sessions', 'courses.id', '=', 'sessions.course_id')
    // ->join('lessons', 'sessions.id', '=', 'lessons.session_id')
    // ->join('quizzes', 'lessons.id', '=', 'quizzes.lesson_id')
    // ->join('questions', 'quizzes.id', '=', 'questions.quizz_id')->count();
    // ->join('answers', 'answers.question_id', '=', 'questions.id')->get();
    // $data = quizzProgress::where('course_id', 1)
    // ->where('user_id', 1)
    // ->get();

        // dd($data );
    // return response()->json($data, 200);
});

