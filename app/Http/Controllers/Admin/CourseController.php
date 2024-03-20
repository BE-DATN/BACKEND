<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\CourseResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\DTO\Course\CourseDTO;
use App\Http\Controllers\Admin\Action\CourseAction;
use App\Models\lesson;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CourseAction $courseAction)
    {
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
            dd($request->hasFile('thumbnail'));
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

            if ($request->hasFile('thumbnail') || $request->input('thumbnail')) {
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
    public function addLesson(Request $request)
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

            $lesson = Lesson::create($request->input());
            return response()->json([
                'status' => true,
                'message' => 'Lesson đã được thêm vào khóa học.',
                'Lesson' => $lesson
            ]);
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
            $data = $courseDTO->courseDetail($data);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy khóa học này',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $data,
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
        $courses = DB::table('courses')->select(['*'])
            ->where('created_by', $id)
            ->join('users', 'courses.created_by', '=', 'users.id')
            ->paginate(10);
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


    public function getSession(Request $request) {
        $sessions =  Session::where('course_id', $request->input('course_id'))->orderBy('arrange', 'asc')->get();
        return response()->json($sessions, 200);
    }
}
