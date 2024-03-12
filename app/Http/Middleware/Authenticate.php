<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('user.login');
    }
    // handle
    public function handle($request, Closure $next, ...$guards)
    {
        if (request()->hasCookie('user')) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            $cookieValue = json_decode(request()->cookie('user'), true);
            $token = array_get($cookieValue, 'access_token');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        // dd($request->headers);
        $this->authenticate($request, $guards);
        // dd('roi');
        return $next($request);
    }
}
