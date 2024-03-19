<?php

namespace App\Http\Controllers\Post;

use App\DTO\Post\PostDTO;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Post\Action\PostAction;
use App\Http\Resources\PostsResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Trang có tất cả bài viết người dùng có thể xem
     */
    public function index(PostAction $postAction)
    {
        $posts = $postAction->search();
        $data = [
            'title' => 'Danh sách bài viết',
            // 'posts' => $posts,
            'posts' => PostsResource::collection($posts),
        ];

        return response()->json($data, 200);
    }

    /**
     * Trang có tất cả bài viết đã tạo của Teacher||Admin
     */

    public function list($id = null)
    {
        $id = array_get(getCurrentUser(), 'id');
        $posts = DB::table('posts')->select(['*'])->where('created_by', $id)->paginate(Post::Limit);
        $data = [
            'page' => 'DS Bài viết của User: ' . $id,
            'posts' => PostsResource::collection($posts),
        ];
        return response()->json($data, 200);
    }

    /**
     * Submit dữ liệu từ form create post vào đây
     */
    public function store(Request $request)
    {
        try {
            $input = Validator::make($request->input(), [
                'title' => 'required|max:500',
                'status' => 'required',
                'content' => 'required',
            ]);
            $img = $request->validate([
                'thumbnail' => 'file|image|mimes:jpeg,png,jpg,gif,svg', // max:2048 means max file size is 2MB
            ]);

            if ($input->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $input->errors()
                ], 404);
                die;
            } else {
                if ($request->hasFile('thumbnail')) {
                    $imageName = 'thumbnail_' . uniqid() . '_.' . $request->file('thumbnail')->extension();
                    $request->file('thumbnail')->move(public_path('file/uploads/posts/'), $imageName);
                    $request->request->add(['thumbnail' => "file/uploads/posts/$imageName"]);
                }
                $user = getCurrentUser();
                $request->request->add(['created_by' => array_get($user, 'id')]);

                $post = Post::create($request->input());
                return response()->json([
                    'status' => true,
                    'message' => 'Bài viết đã được tạo thành công',
                    'post' => $post
                ]);
            }
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
        try {
            $input = removeNullOrEmptyString($request->input());
            $post = Post::find($id);
            if ($post == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy bài viết này.',
                ]);
            }
            // Xóa ảnh thumbnail cũ
            $img = $request->validate([
                'thumbnail' => 'image|mimes:jpeg,png,jpg,gif', // max:2048 means max file size is 2MB
            ]);

            if ($request->hasFile('thumbnail')) {
                if (File::exists($post->thumbnail)) {
                    File::delete($post->thumbnail);
                }
                $imageName = 'thumbnail_' . uniqid() . '_.' . $request->thumbnail->extension();
                $request->thumbnail->move(public_path('file/uploads/posts/'), $imageName);
                // $request->request->add(['thumbnail' => "file/uploads/posts/$imageName"]);
                $input['thumbnail'] = "file/uploads/posts/$imageName";
            }
            
            // $post->update($request->input());
            $post->update($input);

            return response()->json([
                'status' => true,
                'message' => 'Bài viết đã được cập nhật.',
                'post' => $post
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Có xẩy ra lỗi khi cập nhật bài viết.',
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Delete bài viết theo id 
     */
    public function destroy(string $id) //id bài viết
    {
        try {
            Post::destroy($id);
            return response()->json([
                'status' => true,
                'message' => "Xóa bài viết $id thành công.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Có xẩy ra lỗi khi xóa bài viết $id.",
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Show post detail
     */
    public function show($id, PostDTO $postDTO)
    {
        //
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy bài viết này.',
            ]);
        }
        if ($post) {
            $post = $postDTO->postsDetail($post);
            return response()->json([
                'status' => true,
                'data' => $post
            ], 200);
        }
        // dd($data);
        // return view('edit', $data);
    }
}
