<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorPost
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $fullUrl = $request->url();
        $parsedUrl = parse_url($fullUrl);
        $baseURL = "{$parsedUrl['scheme']}://{$parsedUrl['host']}/new/api/post/";
        // Sử dụng str_replace để cắt bỏ phần http://exp.vn/
        $trimmedPath = str_replace($baseURL, '', $fullUrl);

        // array [ // app\Http\Middleware\AuthorPost.php:24
        //     0 => "list-owned-posts", //action
        //     1 => "3" //param
        // ]

        $action = explode('/', $trimmedPath)[0];

        switch ($action) {
            case 'edit':
                $action = 'chỉnh sửa';
                break;
            case 'delete':
                $action = 'xóa';
                break;
            case 'list-owned-posts':
                $action = 'xem danh sách';
                break;
            default:
                $action = 'tạo';
                break;
        }

        $acccess = ['AUTHOR_POST']; //Permission asscess
        if ($user = getCurrentUser()) {
            $user = User::find(array_get($user, 'id'));
            $input = $user->profile->roles;
            // dd($user->profile->roles[0]->permissions[1]);
            if (checkAcccess($acccess, $input, 'name')) {
                return $next($request);
            } else {
                $message = [
                    'status' => false,
                    'message' => "Bạn không có quyền để {$action} bài viết"
                ];
                return response()->json($message, 403);
            }
        }

        $message = [
            'status' => false,
            'message' => "Bạn phải đăng nhập để thực hiện chức năng này"
        ];
        return response()->json($message, 401);
    }
}
