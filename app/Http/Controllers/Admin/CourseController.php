<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\CourseResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\DTO\Course\CourseDTO;
use App\Http\Controllers\Admin\Action\CourseAction;
use App\Http\Resources\AdminLessonResource;
use App\Http\Resources\PurchasedSessionList;
use App\Http\Resources\RatingCourse;
use App\Http\Resources\SessionList;
use App\Models\lesson;
use App\Models\order;
use App\Models\quizzProgress;
use App\Models\rating_course;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CourseAction $courseAction)
    {
        // if ($user = getCurrentUser()) {
        //     $courses = $courseAction->search($this->getPurchasedCourses($user));
        // } else {
        //     $courses = $courseAction->search();
        // }
        $courses = $courseAction->search();
        $data = [
            'title' => 'Danh sách Khóa học',
            'courses' => CourseResource::collection($courses),
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        $courseDTO = new CourseDTO();
        try {
            $request->validate([
                'thumbnail' => 'file|image|mimes:jpeg,png,jpg,gif,svg', // max:2048 means max file size is 2MB
                'video_demo_url' => 'file|mimes:mp4,mov,ogg,qt', // max:2048 means max file size is 2MB
            ]);
            $valid = Validator::make($request->input(), [
                'name' => 'required|max:500',
                'status' => 'required',
                'price' => 'required|numeric|between:0,99999999999.99',
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $valid->errors()
                ], 404);
                die;
            }

            $input = $request->input();

            if ($request->hasFile('thumbnail')) {
                $imageName = 'thumbnail_' . uniqid() . '_.' . $request->file('thumbnail')->extension();
                $request->file('thumbnail')->move(public_path('file/uploads/courses/thumbnails'), $imageName);
                $input['thumbnail'] = "file/uploads/courses/thumbnails/$imageName";
            }
            if ($request->hasFile('video_demo_url')) {
                $video_demo_name = 'video_' . uniqid() . '_.' . $request->file('video_demo_url')->extension();
                $request->file('video_demo_url')->move(public_path('file/uploads/courses/videos'), $video_demo_name);
                $input['video_demo_url'] = "file/uploads/courses/videos/$video_demo_name";
            }

            $user = getCurrentUser();
            $input['created_by'] = array_get($user, 'id');
            // $request->request->add(['created_by' => array_get($user, 'id')]);
            // $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);
            // $request->request->add(['video_demo_url' => "file/uploads/courses/videos/$video_demo_name"]);

            // $course = Course::create($request->input());
            $course = Course::create($input);

            // return response()->json([
            //     'status' => false,
            //     'message' => 'Khóa học đã được tạo thành công',
            //     'course' => $input
            // ]);
            return response()->json([
                'status' => true,
                'message' => 'Khóa học đã được tạo thành công',
                'course' => $courseDTO->courseDetail($course)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi tạo khóa học',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function addSession(Request $request)
    {
        try {
            // $request = new Request();
            // removeNullOrEmptyString($request->input());
            // dd($request->input());
            // dd($request->hasFile('thumbnail'));
            $valid = Validator::make($request->input(), [
                'name' => 'required|max:500',
                'course_id' => 'required'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $valid->errors()
                ], 404);
                die;
            }

            if ($request->hasFile('thumbnail') && $request->input('thumbnail')) {
                $request->validate([
                    'thumbnail' => 'file|image|mimes:jpeg,png,jpg,gif,svg',
                ]);
                $imageName = 'thumbnail_Session_' . uniqid() . '_.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('file/uploads/courses/thumbnails/'), $imageName);
                $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);
            } else {
                $request->request->remove('thumbnail');
            }
            // $request->request->add(['course_id' => $course_id]);

            // $session = Session::create([
            //     'course_id' => $course_id,
            //     'name' => $request->input('name'),
            //     'description' => $request->input('description'),
            //     'arrange' => $request->input('arrange'),
            //     'thumbnail' => $request->input('thumbnail'),
            // ]);
            $session = Session::create($request->input());
            return response()->json([
                'status' => true,
                'message' => 'Session đã được thêm vào khóa học.',
                'Session' => $session
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi tạo session này hãy thử lại sau.',
                'error' => $th->getMessage(),
            ]);
        }
    }
    public function addLession(Request $request)
    {
        try {
            $request->validate([
                'video_url' => 'required|file|mimes:mp4,mov,ogg,qt'
            ]);
            $valid = Validator::make($request->input(), [
                'name' => 'required|max:500',
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $valid->errors()
                ], 404);
                die;
            }

            if ($request->hasFile('video_url')) {
                $videoName = 'video_Lesson_' . uniqid() . '_.' . $request->video_url->extension();
                $request->video_url->move(public_path('file/uploads/courses/videos/'), $videoName);
                $request->request->add(['video_url' => "file/uploads/courses/videos/$videoName"]);
            }

            // $request->request->add(['session_id' => $session_id]);

            if ($lesson = Lesson::create($request->input())) {
                return response()->json([
                    'status' => true,
                    'message' => 'Lesson đã được thêm vào khóa học.',
                    'Lesson' => $lesson
                ]);
            };
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi tạo lesson này hãy thử lại sau.',
                'error' => $th->getMessage(),
            ]);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($id, CourseDTO $courseDTO)
    {
        //
        $data = Course::find($id);
        if ($data) {
            if (!$data->status) {
                return response()->json([
                    'status' => false,
                    'message' => 'Khóa học này không được kích hoạt',
                ], 200);
            }
            $data->views += random_int(1, 10);
            $data->save();
            $data = $courseDTO->courseDetail($data);
            $sessions = Session::where('course_id', array_get($data, 'id'))->get();
            // dd(array_get($data, 'id'));
            $ratings = rating_course::where('course_id', $id)->get();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy khóa học này',
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => $data,
            'sessions' => SessionList::collection($sessions),
            'ratings' => RatingCourse::collection($ratings)
        ], 200);
    }
    public function adminShow($id, CourseDTO $courseDTO)
    {
        //
        $data = Course::find($id);
        if ($data) {
            $data->views += random_int(1, 10);
            $data->save();
            $data = $courseDTO->courseDetail($data);
            $sessions = Session::where('course_id', array_get($data, 'id'))->get();
            // dd(array_get($data, 'id'));
            $ratings = rating_course::where('course_id', $id)->get();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy khóa học này',
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => $data,
            'sessions' => SessionList::collection($sessions),
            'ratings' => RatingCourse::collection($ratings)
        ], 200);
    }
    public function showPurchasedCourse($id, CourseDTO $courseDTO)
    {
        $user = getCurrentUser();
        // Purchased
        $purchasedCourses = $this->getPurchasedCourses($user);
        // dd($purchasedCourses);

        $data = Course::find($id);

        if ($data) {
            $result = collect($purchasedCourses)->map(function ($data) {
                return $data->course_id;
            })->contains($data->id) ? true : false;
            if ($result) {
                $data = $courseDTO->courseDetail($data);
                $sessions = Session::where('course_id', array_get($data, 'id'))->get();
                // $ratings = rating_course::where('course_id', $id)->get();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Khóa học này không nằm trong danh sách khóa học đã mua',
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy khóa học này',
            ], 200);
        }

        //
        $total_quiz_of_course = Course::find($id)
            ->join('sessions', 'courses.id', '=', 'sessions.course_id')
            ->join('lessons', 'sessions.id', '=', 'lessons.session_id')
            ->join('quizzes', 'lessons.id', '=', 'quizzes.lesson_id')
            ->join('questions', 'quizzes.id', '=', 'questions.quizz_id')->count();

        $quiz_done = quizzProgress::where('course_id', $id)
            ->where('user_id', array_get($user, 'id'))
            ->count();
        // dd($total_quiz_of_course);
        // dd($quiz_done);
        $quizProgress = round(($quiz_done / $total_quiz_of_course) * 100);
        // dd($quizProngủgress);
        return response()->json([
            'status' => true,
            'data' => $data,
            'quiz_progress' => $quizProgress ? $quizProgress : 0,
            'learned_progress' => 69,
            'sessions' => PurchasedSessionList::collection($sessions),
            // 'ratings' => RatingCourse::collection($ratings)
        ], 200);
    }

    /**
     * Submit dữ liệu từ form edit post vào đây
     */
    public function update(Request $request, string $id) //id bài viết
    {
        //
        $courseDTO = new CourseDTO();
        try {

            $valid = Validator::make($request->input(), [
                'name' => 'max:500',
                'status' => 'required',
                'price' => 'numeric|between:0,99999999999.99',
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $valid->errors()
                ], 404);
                die;
            }

            $course = Course::find($id);
            if ($course == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy khóa học này.',
                ]);
            }

            if ($request->hasFile('thumbnail')) {
                $request->validate([
                    'thumbnail' => 'file|image|mimes:jpeg,png,jpg,gif,svg',
                ]);
                // Xóa ảnh thumbnail cũ
                if (File::exists($course->thumbnail)) {
                    File::delete($course->thumbnail);
                }
                // return response()->json('thumbnail_' . uniqid() . '_.' . $request->thumbnail->extension());
                $imageName = 'thumbnail_' . uniqid() . '_.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('file/uploads/courses/thumbnails/'), $imageName);
                $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);
            } else {
                $request->request->remove('thumbnail');
            }

            if ($request->hasFile('video_demo_url')) {
                $request->validate([
                    'video_demo_url' => 'mimes:mp4,mov,ogg,qt',
                ]);
                // Xóa ảnh video_demo cũ

                if (File::exists($course->video_demo_url)) {
                    File::delete($course->video_demo_url);
                }
                $imageName = 'video' . uniqid() . '_.' . $request->video_demo_url->extension();
                $request->video_demo_url->move(public_path('file/uploads/courses/videos/'), $imageName);
                $request->request->add(['video_demo_url' => "file/uploads/courses/videos/$imageName"]);
                // return response()->json($request->input('video_demo_url'), 200);
            } else {
                $request->request->remove('video_demo_url');
            }

            $course->update($request->input());
            return response()->json([
                'status' => true,
                'message' => 'Khóa học đã được cập nhật.',
                'course' => $courseDTO->courseDetail($course)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi cập nhật khóa học.',
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Trang có tất cả khoá học đã tạo của Teacher||Admin
     */

    public function list($id = null)
    {
        $id = array_get(getCurrentUser(), 'id');
        if (auth()->user()->profile->roles->first()->name == 'ADMIN') {
            $courses = DB::table('courses')->select([
                'courses.id',
                'courses.name',
                'username',
                'courses.description',
                'courses.thumbnail',
                'courses.video_demo_url',
                'courses.views',
                'courses.price',
                'courses.status',
                'courses.created_at',
                'courses.updated_at',
            ])
                ->join('users', 'courses.created_by', '=', 'users.id')
                // ->paginate(10);
                ->get();
        } else {

            $courses = DB::table('courses')->select([
                'courses.id',
                'courses.name',
                'username',
                'courses.description',
                'courses.thumbnail',
                'courses.video_demo_url',
                'courses.views',
                'courses.price',
                'courses.status',
                'courses.created_at',
                'courses.updated_at',
            ])
                ->where('created_by', $id)
                ->join('users', 'courses.created_by', '=', 'users.id')
                // ->paginate(10);
                ->get();
        }
        $data = [
            'page' => 'DS khóa học của User: ' . $courses[0]->username,
            'course' => CourseResource::collection($courses),
        ];
        return response()->json($data, 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Course::find($id)->update(['status' => 0]);
            return response()->json([
                'status' => true,
                'message' => "Khoá học đã được ẩn.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Có xẩy ra lỗi khi ẩn khoá học $id.",
                'error' => $th->getMessage()
            ]);
        }
    }


    public function getSession($courseId)
    {
        try {
            $sessions =  Session::where('course_id', $courseId)->orderBy('arrange', 'asc')->get();
            $data = [
                'status' => true,
                'sessions' => $sessions,
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'error' => $th->getMessage(),
            ], 200);
        }
        return response()->json($sessions, 200);
    }
    public function getLesson($sessonId)
    {
        try {
            $lessons =  lesson::where('session_id', $sessonId)->orderBy('arrange', 'asc')->get();
            $data = [
                'status' => true,
                'lessons' => AdminLessonResource::collection($lessons),
                // 'lessons' => $lessons,
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'error' => $th->getMessage(),
            ], 200);
        }
        // return response()->json($lessons, 200);
    }
    public function purchased_courses()
    {
        $user = getCurrentUser();
        $courses = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('courses', 'order_details.course_id', '=', 'courses.id')
            ->join('users', 'users.id', '=', 'courses.created_by')
            // ->where('courses.status', '1')
            ->where('orders.user_id', array_get($user, 'id'))
            ->where('orders.order_status', 1)
            ->select(
                'courses.id',
                'courses.name',
                'users.username',
                'courses.description',
                'courses.thumbnail',
                'courses.video_demo_url',
                'courses.price',
                'views',
                'courses.status',
                'courses.created_at',
                'courses.updated_at'
            )
            ->distinct()
            ->get();
        $data = [
            'status' => true,
            'title' => 'Danh sách Khóa học đã mua',
            'courses' => CourseResource::collection($courses),
        ];
        return response()->json($data, 200);
    }

    public function ratingCourse($course_id, Request $request)
    {
        try {
            $request->validate([
                // 'title' => 'required|max:500',
                // 'content' => 'required|max:1000',
                'rating' => 'required',
            ]);
            $input = $request->input();
            $user = getCurrentUser();

            $purchasedCourses = $this->getPurchasedCourses($user);

            if ($courses = Course::find($course_id)) {
                $result = collect($purchasedCourses)->map(function ($courses) {
                    return $courses->course_id;
                })->contains($courses->id) ? true : false;
                if ($result) {
                    $input['course_id'] = $course_id;
                    $input['user_id'] =  array_get($user, 'id');
                    $input['rating'] =  $request->input('rating');
                    $input['content'] =  $request->input('content') ? $request->input('content') : '';
                    $rating = rating_course::create($input);
                    return response()->json([
                        'status' => true,
                        'message' => 'Đã tạo đánh giá khóa học',
                        'rating' => $rating
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Bạn chưa đăng ký khóa học này. Hãy đăng ký trước khi đánh giá',
                    ]);
                }
            }
            // dd($user->id);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Có lỗi xảy ra khi tạo đánh giá khóa học.",
                'error' => $th->getMessage()
            ]);
        }
    }

    public function destroySession($id, Request $request)
    {
        try {
            if ($session = Session::find($id)) {
                // dd(($id));
                $lessions = lesson::where('session_id', $session->id);
                foreach ($lessions->get() as $key => $value) {
                    if (File::exists($value->video_url)) {
                        File::delete($value->video_url);
                    }
                }
                if ($lessions->delete() || $session->delete()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Session này đã được xóa cùng với các bài học trong Session.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy Session này.',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Có lỗi xảy ra khi xoá Session khóa học.",
                'error' => $th->getMessage()
            ]);
        }
    }
    public function destroyLesson($id, Request $request)
    {
        try {
            if ($lession = lesson::find($id)) {
                if (File::exists($lession->video_url)) {
                    File::delete($lession->video_url);
                }
                if ($lession->delete()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Lesson này đã được xóa.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy Lesson này.',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Có lỗi xảy ra khi xoá Lesson khóa học.",
                'error' => $th->getMessage()
            ]);
        }
    }
    public function getPurchasedCourses($user)
    {
        $purchasedCourses = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('courses', 'order_details.course_id', '=', 'courses.id')
            ->join('users', 'users.id', '=', 'courses.created_by')
            // ->where('courses.status', '1')
            ->where('orders.user_id', array_get($user, 'id'))
            ->where('orders.order_status', 1)
            ->select(
                'courses.id as course_id',
                'orders.user_id',
            )
            ->distinct()
            ->get();
        return $purchasedCourses;
    }
}
