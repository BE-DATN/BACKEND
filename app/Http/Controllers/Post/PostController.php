<?php

namespace App\Http\Controllers\Post;

use App\DTO\Post\PostDTO;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Post\Action\PostAction;
use App\Http\Resources\PostCommentResource;
use App\Http\Resources\PostsResource;
use App\Models\Post;
use App\Models\Post_Comment;
use App\Models\User;
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
        if (auth()->user()->profile->roles->first()->name === 'ADMIN') {
            $posts = DB::table('posts')->select([
                'posts.id',
                'posts.title',
                'username',
                'posts.content',
                'posts.thumbnail',
                'posts.views',
                'posts.likes',
                'posts.created_at',
                'posts.updated_at',
            ])
                ->join('users', 'posts.created_by', '=', 'users.id')
                // ->where('created_by', $id)
                ->get();
        } else {

            $posts = DB::table('posts')->select([
                'posts.id',
                'posts.title',
                'username',
                'posts.content',
                'posts.thumbnail',
                'posts.views',
                'posts.likes',
                'posts.created_at',
                'posts.updated_at',
            ])
                ->join('users', 'posts.created_by', '=', 'users.id')
                ->where('created_by', $id)
                ->get();
        }
        // ->paginate(Post::Limit);
        // dd($posts);
        $data = [
            'page' => 'DS Bài viết của: ' . $posts[0]->username,
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
                'thumbnail' => 'image|mimes:jpeg,png,jpg,gif,svg',
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
            // return response()->json($request->input('content'));
            $input = removeNullOrEmptyString($request->input());
            $post = Post::find($id);
            if ($post == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy bài viết này.',
                ]);
            }


            if ($request->hasFile('thumbnail')) {
                $img = $request->validate([
                    'thumbnail' => 'image|mimes:jpeg,png,jpg,gif',
                ]);
                // Xóa ảnh thumbnail cũ
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

            Post_Comment::where('post_id', $id)->delete();


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
        // $post = Post::find($id);

        $post = DB::table('posts')->select([
            'posts.id',
            'posts.title',
            'username',
            'posts.content',
            'posts.thumbnail',
            'posts.views',
            'posts.likes',
            'posts.status',
            'posts.created_at',
            'posts.updated_at',
        ])
            ->join('users', 'posts.created_by', '=', 'users.id')
            ->where('posts.id', $id)
            // ->where('posts.status', 1)
            ->first();
        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy bài viết này.',
            ]);
        }
        if ($post) {
            if (!$post->status) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bài viết này đã bị ẩn.',
                ]);
            }
            $view = post::find($post->id);
            $view->views += random_int(1, 10);
            $view->save();
            $comment = Post_Comment::where('post_id', $post->id)->get();
            $post = $postDTO->postsDetail($post);
            return response()->json([
                'status' => true,
                'data' => $post,
                'comments' => PostCommentResource::collection($comment)
            ], 200);
        }
        // dd($data);
        // return view('edit', $data);
    }
    public function showAdmin($id, PostDTO $postDTO)
    {
        //
        // $post = Post::find($id);

        $post = DB::table('posts')->select([
            'posts.id',
            'posts.title',
            'username',
            'posts.content',
            'posts.thumbnail',
            'posts.views',
            'posts.likes',
            'posts.status',
            'posts.created_at',
            'posts.updated_at',
        ])
            ->join('users', 'posts.created_by', '=', 'users.id')
            ->where('posts.id', $id)
            // ->where('posts.status', 1)
            ->first();
        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy bài viết này.',
            ]);
        }
        if ($post) {
            // if (!$post->status) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Bài viết này đã bị ẩn.',
            //     ]);
            // }
            $view = post::find($post->id);
            $view->views += random_int(1, 10);
            $view->save();
            $comment = Post_Comment::where('post_id', $post->id)->get();
            $post = $postDTO->postsDetail($post);
            return response()->json([
                'status' => true,
                'data' => $post,
                'comments' => PostCommentResource::collection($comment)
            ], 200);
        }
        // dd($data);
        // return view('edit', $data);
    }
    public function comment($id, Request $request, PostDTO $postDTO)
    {
        try {
            $request->validate([
                // 'title' => 'required|max:500',
                'content' => 'required|max:1000',
            ]);
            if ($post = Post::where('id', $id)->where('status', 1)->first()) {
                $input = $request->input();
                $user = getCurrentUser();
                // dd($user->id);
                $input['post_id'] = $id;
                $input['user_id'] =  $user->id;
                $comment = Post_Comment::create($input);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Bài viết này không tồn tại hoặc đã bị ẩn',
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Tạo comment thành công ',
                'comment' => $postDTO->postComment($comment, $user)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Có lỗi xảy ra khi tạo Comment.",
                'error' => $th->getMessage()
            ]);
        }
    }
}
