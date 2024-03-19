<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Author
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

// Xem lai
        $acccess = ['ADMIN', 'TEACHER']; //Role asscess
        // return response()->json(getCurrentUser(), 200);
        if ($user = getCurrentUser()) {
            $user = User::find(array_get($user, 'id'));
            $input = $user->profile->roles;
            checkAcccess($acccess, $input, 'name');
            // return response()->json([checkAcccess($acccess, $input, 'name')], 200);
            if (checkAcccess($acccess, $input, 'name')) {
                return $next($request);
            } else {
                $message = [
                    'status' => false,
                    'message' => "Bạn không có quyền để thực hiện thao tác này."
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
