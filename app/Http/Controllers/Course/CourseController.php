<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Course\Action\CourseAction;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Trang có tất cả bài viết người dùng có thể xem
     */
    public function index(CourseAction $courseAction)
    {
        $posts = $courseAction->search();
        $data = [
            'title' => 'Danh sách khóa học',
            'posts' => $posts,
        ];
        return response()->json($data, 200);
    }

    /**
     * Trang có tất cả bài viết đã tạo của Teacher||Admin
     */

    public function list($id)
    {
        return response()->json(['page' => 'DS Bài viết của User: ' . $id], 200);
    }

    /**
     * Submit dữ liệu từ form create post vào đây
     */
    public function store(Request $request)
    {
        try {
            // dd('create course');

            $imageName = 'thumbnail_' . uniqid() . '_.' . $request->file('thumbnail')->extension();
            $video_demo_name = 'video_' . uniqid() . '_.' . $request->file('video_demo_url')->extension();
            // return response()->json([$imageName]);

            // return response()->json($request->input());
            // dd($request->input());

            $request->file('thumbnail')->move(public_path('file/uploads/posts/'), $imageName);
            $request->file('video_demo_url')->move(public_path('file/uploads/course/'), $imageName);

            $user = getCurrentUser();
            $request->request->add(['created_by' => array_get($user, 'id')]);
            $request->request->add(['thumbnail' => "file/uploads/posts/$imageName"]);
            // dd($request->input());
            // dd($request->input());
            $post = Post::create($request->input());

            return response()->json([
                'status' => true,
                'message' => 'Bài viết đã được tạo thành công',
                'post' => $post
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi tạo bài viết',
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Submit dữ liệu từ form edit post vào đây
     */
    public function update(Request $request, string $id) //id bài viết
    {
        //
        return response()->json(['action' => "Chỉnh sửa bài viết {$id}"]);
    }

    /**
     * Delete bài viết theo id
     */
    public function destroy(string $id) //id bài viết
    {
        return response()->json(['action' => "Xóa bài viết {$id}"]);
    }






    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        return response()->json(['page' => "posts $id detail"]);
    }
}
