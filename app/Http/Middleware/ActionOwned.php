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
        // Tác giả || admin cho qua sửa || xóa
        // return $next($request);
        $user = getCurrentUser();
        $user = User::find(array_get($user, 'id'));
        $input = $user->profile->roles->toArray();

        // addmin qua luôn khỏi check tác giả
        // return response()->json([$input]);
        foreach ($input as $role) {
            if (array_get($role, 'name') == "ADMIN" || array_get($role, 'name') == "TEACHER") {
                return $next($request);
            }
        }

        // if (array_get($user, 'id') == $request->route()->parameter('id')) {
        //     return response()->json(['actionOwned'], 403);
        //     return $next($request);
        // }

        $message = [
            'status' => false,
            'message' => "Chỉ có tác giả và Admin mới có thể thực hiện thao tác này."
        ];
        return response()->json($message, 403);
    }
}
