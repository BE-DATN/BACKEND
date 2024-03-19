<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\CourseResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\DTO\Course\CourseDTO;
use App\Http\Controllers\Admin\Action\CourseAction;
use App\Models\lession;
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
        return response()->json($courses, 200);
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

    public function addSession($course_id)
    {
        $request = new Request();
        try {
            $request->validate([
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // max:2048 means max file size is 2MB
            ]);
            $imageName = 'thumbnail_' . uniqid() . '_.' . $request->file('thumbnail')->extension();
            $request->file('thumbnail')->move(public_path('file/uploads/courses/thumbnails'), $imageName);
            $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);
            $session = Session::insert([
                'course_id' => $course_id,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'arrange' => $request->input('arrange'),  
                'thumbnail' => $request->input('name'),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Session đã được thêm vào khóa học.',
                'Session' => $session
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => true,
                'message' => 'Có xẩy ra lỗi khi tạo session này hãy thử lại sau.',
                'error' => $th->getMessage(),
            ]);
        }
    }
    public function addLession($session_id)
    {
        $request = new Request();
        try {
            $request->validate([
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // max:2048 means max file size is 2MB
            ]);
            $imageName = 'thumbnail_' . uniqid() . '_.' . $request->file('thumbnail')->extension();
            $request->file('thumbnail')->move(public_path('file/uploads/courses/thumbnails'), $imageName);
            $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);

            $lession = lession::insert([
                'course_id' => $session_id,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'arrange' => $request->input('arrange'),  
                'thumbnail' => $request->input('name'),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Lession đã được thêm vào khóa học.',
                'Session' => $lession
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => true,
                'message' => 'Có xẩy ra lỗi khi tạo lession này hãy thử lại sau.',
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
            // return response()->json($course, 200);
            // Xóa ảnh thumbnail cũ
            if ($request->hasFile('thumbnail')) {
                $request->validate([
                    'thumbnail' => 'file|image|mimes:jpeg,png,jpg,gif,svg', // max:2048 means max file size is 2MB
                    // 'video_demo_url' => 'file|mimes:mp4,mov,ogg,qt', // max:2048 means max file size is 2MB
                ]);
                if (File::exists($course->thumbnail)) {
                    File::delete($course->thumbnail);
                }
                $imageName = 'thumbnail_' . uniqid() . '_.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('file/uploads/courses/thumbnails/'), $imageName);
                $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);
            } else {
                $request->request->remove('thumbnail');
            }
            // Xóa ảnh thumbnail cũ
            if ($request->hasFile('video_demo_url')) {
                $request->validate([
                    // 'thumbnail' => 'file|image|mimes:jpeg,png,jpg,gif,svg', // max:2048 means max file size is 2MB
                    'video_demo_url' => 'file|mimes:mp4,mov,ogg,qt', // max:2048 means max file size is 2MB
                ]);
                if (File::exists($course->video_demo_url)) {
                    File::delete($course->video_demo_url);
                }
                $imageName = 'video' . uniqid() . '_.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('file/uploads/courses/videos/'), $imageName);
                $request->request->add(['video_demo_url' => "file/uploads/courses/videos/$imageName"]);
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
        $courses = DB::table('courses')->select(['*'])->where('created_by', $id)->paginate(10);
        $data = [
            'page' => 'DS khóa học của User: ' . $id,
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
            Course::destroy($id);
            return response()->json([
                'status' => true,
                'message' => "Khoá học đã được xóa.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Có xẩy ra lỗi khi xóa khoá học $id.",
                'error' => $th->getMessage()
            ]);
        }
    }
}
