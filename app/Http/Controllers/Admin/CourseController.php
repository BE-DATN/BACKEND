<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\CourseResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\DTO\Course\CourseDTO;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        try {
            $imageName = 'thumbnail_' . uniqid() . '_.' . $request->file('thumbnail')->extension();
            $video_demo_name = 'video_' . uniqid() . '_.' . $request->file('video_demo_url')->extension();

            $request->file('thumbnail')->move(public_path('file/uploads/courses/thumbnails'), $imageName);
            $request->file('video_demo_url')->move(public_path('file/uploads/courses/videos'), $video_demo_name);

            $user = getCurrentUser();
            $request->request->add(['created_by' => array_get($user, 'id')]);
            $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);
            $request->request->add(['video_demo_url' => "file/uploads/courses/videos/$video_demo_name"]);

            $course = Course::create($request->input());

            return response()->json([
                'status' => true,
                'message' => 'Khóa học đã được tạo thành công',
                'post' => $course
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi tạo khóa học',
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id, CourseDTO $courseDTO)
    {
        //
        $data = Course::where('id', $id)->first();
        // $data = $courseDTO->courseDetail($data);
        return response()->json($data, 200);
    }

   /**
     * Submit dữ liệu từ form edit post vào đây
     */
    public function update(Request $request, string $id) //id bài viết
    {
        //
        try {
            $course = Course::findOrFail($id);
            if ($course == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy khóa học này.',
                ]);
            }
            // return response()->json($course, 200);
            // Xóa ảnh thumbnail cũ
            if ($request->hasFile('thumbnail')) {
                // $request->validate([
                //     'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                // ]);
                if (File::exists($course->thumbnail)) {
                    if (File::delete($course->thumbnail)) {
                        $imageName = 'thumbnail_' . uniqid() . '_.' . $request->thumbnail->extension();
                        $request->thumbnail->move(public_path('file/uploads/courses/thumbnails/'), $imageName);
                        $request->request->add(['thumbnail' => "file/uploads/courses/thumbnails/$imageName"]);
                    };
                }
            } else {
                $request->request->remove('thumbnail');
            }
            // Xóa ảnh thumbnail cũ
            if ($request->hasFile('video_demo_url')) {
                // $request->validate([
                //     'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                // ]);
                // dd($request->input());
                if (File::exists($course->thumbnail)) {
                    if (File::delete($course->thumbnail)) {
                        $imageName = 'video' . uniqid() . '_.' . $request->thumbnail->extension();
                        $request->thumbnail->move(public_path('file/uploads/courses/videos/'), $imageName);
                        $request->request->add(['video_demo_url' => "file/uploads/courses/videos/$imageName"]);
                    };
                }
            } else {
                $request->request->remove('video_demo_url');
            }
            // return response()->json(removeNullOrEmptyString($request->input()), 200);
            $course->update($request->input());

            return response()->json([
                'status' => true,
                'message' => 'Khóa học đã được cập nhật.',
                'post' => $course
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
