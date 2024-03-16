<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Post\PostController;
use Illuminate\Http\Request;
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
    Route::get('registerp', [AccountController::class, 'userRegister'])->name('user.post.register');

    Route::group([
        'middleware' => ['api'], //, 'jwt.auth',
    ], function () {
        Route::get('refresh', [AccountController::class, 'refresh']);
        Route::get('me', [AccountController::class, 'me']);
        Route::get('logout', [AccountController::class, 'logout']);
    });
});

// Post Route
Route::group([
    'prefix' => 'post',
], function () {
    Route::get('/', [PostController::class, 'index'])->name('post.index');
    Route::get('/{id}', [PostController::class, 'show'])->whereNumber('id')->name('post.show');

    Route::group([
        'middleware' => ['api', 'post.auth'],
    ], function () {
        Route::get('/list-owned-posts/{userId}', [PostController::class, 'list'])->whereNumber('userId')->name('post.index');
        Route::get('/create', [PostController::class, 'store'])->name('post.create');
        Route::get('/edit/{id}', [PostController::class, 'update'])->whereNumber('id')->name('post.edit')->middleware('action.auth');
        Route::get('/delete/{id}', [PostController::class, 'destroy'])->whereNumber('id')->name('post.delete')->middleware('action.auth');
    });
});

// Course Route
// Route::group([
//     'prefix' => 'course',
// ], function () {
//     Route::get('/', [PostController::class, 'index'])->name('post.index');
//     Route::get('/{id}', [PostController::class, 'show'])->whereNumber('id')->name('post.show');

//     Route::group([
//         'middleware' => ['api', 'post.auth'],
//     ], function () {
//         Route::get('/list-owned-courses/{userId}', [PostController::class, 'list'])->whereNumber('userId')->name('post.index');
//         Route::get('/create', [PostController::class, 'store'])->name('post.create');
//         Route::get('/edit/{id}', [PostController::class, 'update'])->whereNumber('id')->name('post.edit')->middleware('action.auth');
//         Route::get('/delete/{id}', [PostController::class, 'destroy'])->whereNumber('id')->name('post.delete')->middleware('action.auth');
//     });
// });
// routes/api.php

//course
Route::group(['prefix' => 'courses'], function () {
    Route::post('/create', [CourseController::class, 'create'])->name('course.create');
    Route::get('/show/{id}', [CourseController::class, 'show'])->name('course.show');
    Route::put('/update/{id}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/delete/{id}', [CourseController::class, 'delete'])->name('course.delete');
    Route::get('/search', [CourseController::class, 'search'])->name('course.search');
});