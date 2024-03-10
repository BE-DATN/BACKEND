<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionOwned
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
        $trimmedPath = str_replace($baseURL, '', $fullUrl);
        $action = explode('/', $trimmedPath)[0];

        switch ($action) {
            case 'edit':
                $action = 'chỉnh sửa';
                break;
            default:
                'delete';
                $action = 'xóa';
                break;
        }


        // Tác giả || admin cho qua sửa || xóa
        $user = getCurrentUser();
        $user = User::find(array_get($user, 'id'));
        $input = $user->profile->roles->toArray();

        // addmin qua luôn khỏi check tác giả
        foreach ($input as $role) {
            if (array_get($role, 'name') == "ADMIN" ) {
                return $next($request);
            }
        }

        if (array_get($user, 'id') == $request->route()->parameter('id')) {
            return $next($request);
        }

        $message = [
            'status' => false,
            'message' => "Chỉ có tác giả và admin mới có thể {$action} bài viết này."
        ];
        return response()->json($message, 403);
    }
}
