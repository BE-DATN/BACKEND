<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'duration' => 'required|integer',
            'price' => 'required|string',
            'views' => 'required|string',
            'status' => 'required|string',
            'thumbnail' => 'required|string',
            'video_demo_url' => 'required',
            'timestamp' => 'required',
        ]);

        // Di chuyển lệnh dd() sau khi xác thực dữ liệu
        // dd($request->input());

        $course = course::create($data);

        return response()->json(['message' => 'Course created successfully', 'course' => $course]);
    }

    public function show($id)
    {
        $course = Course::find($id);

        // Kiểm tra nếu khóa học không tồn tại
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json(['course' => $course]);
    }

    public function update(Request $request, $id)
    {
        $course = Course::find($id);

        // Kiểm tra nếu khóa học không tồn tại
        if (!$course) {
            return response()->json(['message' => 'Course not found']);
        }

        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'duration' => 'required|integer',
            'price' => 'required|string',
            'views' => 'required|string',
            'status' => 'required|string',
            'thumbnail' => 'required|string',
            'video_demo_url' => 'required',
            'timestamp' => 'required',
        ]);


        // dd($request->input());

        $course->update($data);

        return response()->json(['message' => 'Course updated successfully', 'course' => $course]);
    }

    public function delete($id)
    {
        // xóa khóa học
        $deleted = Course::destroy($id);

        if ($deleted) {
            return response()->json([
                'status' => true,
                'message' => "Xóa khóa học thành công.",
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy khóa học để xóa.",
            ]);
        }
    }
    // tim kiem khoa hoc
    public function search(Request $request)
    {

        $input = $request->all();
        $query = Course::query();
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        $courses = $query->get();
        return response()->json($courses);
        // return view('courses.index', compact('courses'));
    }
}
