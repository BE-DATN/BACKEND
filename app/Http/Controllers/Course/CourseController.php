<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Post\Action\PostAction;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Trang có tất cả bài viết người dùng có thể xem
     */
    public function index(PostAction $postAction)
    {
        $posts = $postAction->search();
        $data = [
            'title' => 'Danh sách bài viết',
            'posts' => $posts,
        ];
        return response()->json($data, 200);
    }

    /**
     * Show trang có form tạo vài viết
     */
    public function create()
    {
        return response()->json(['page' => 'create-posts'], 201);
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
        return response()->json(['action' => 'create-posts']);
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
