<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Support\Utils;

class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        dd('verify token');
        // $token = null;
        $cookieValue = null;
        if (request()->hasCookie('user')) {
            $cookieValue = json_decode(request()->cookie('user'), true);
            $token = array_get($cookieValue, 'access_token');
        } else {
            return response()->json(['login now']);
        }

        // dd($token);
        if (!$token) {
            // throw new JWTException('A token is required', 400);
            return response()->json(['message' => 'A token is required'], 400);
        }

        try {
            $payload = JWTAuth::getJWTProvider()->decode($token);
        } catch (\Exception $e) {
            throw new TokenInvalidException('Could not decode token: ' . $e->getMessage());
        }
        // JWTAuth::payload($token)->veriry(config('jwt.secret'), config('jwt.algo'));
        // dd(JWTAuth::getJWTProvider()->encode());

        // if (! $payload->verify(config('jwt.secret'), config('jwt.algo'))) {
        //     throw new TokenInvalidException('Token Signature could not be verified.');
        // }
        // dd('s');
        if (Utils::timestamp($payload['exp'])->isPast()) {
            return response()->json(['message' => 'Token has expired'], 401);
        }
        return $next($request);
    }
}
