<?php

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

if (!function_exists('isEmail')) {
    function isEmail($email)
    {
        $pattern = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        // return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? true : false;
        return preg_match($pattern, $email) == 1;
    }
}

if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (!function_exists('getCookie')) {
    function getCookie()
    {
        $cookieValue = request()->cookie('user');
        return json_decode($cookieValue, true);
    }
}
if (!function_exists('getCurrentUser')) {
    function getCurrentUser()
    {
        $request = new Request();
        $user = null;
        // return $request->bearerToken();
        if ($user == null) {
            $user = JWTAuth::parseToken()->authenticate();
        }
        if ($user == null && getCookie()) {
           $user = json_decode(getCookie())['data'];
        }
        return $user;
    }
}
if (!function_exists('ccc')) {
    function ccc($data)
    {
        return response()->json($data, 200);
    }
}

if (!function_exists('checkAcccess')) {
    function checkAcccess(array $arrAccess, $input, $keyCheck)
    {
        foreach ($input as $value) {
            if ($value->name == 'ADMIN') {
                return true;
            }
            foreach ($arrAccess as $access) {
                if (strtoupper($access) == strtoupper($value->name)) {
                    // return response()->json($value->name, 200);
                    return true;
                }
            }
        }
        // foreach ($input as $role) {
        //     // dd($role->$keyCheck);
        //     $roleName = strtoupper($role->$keyCheck);
        //     if ($roleName == "ADMIN") {
        //         return true;
        //     }
        //     foreach ($arrAccess as $access) {
        //         // dd($role->permissions);
        //         foreach ($role->permissions as $key => $value) {
        //             /***
        //              * Post ? $access='Author_Post'
        //              * Course ? $access='Author_Course'
        //              *
        //              * $value->name = permission_name (ex: Author_Post)
        //              */
        //             if (strtoupper($access) == strtoupper($value->name)) {
        //                 return true;
        //             }
        //         }
        //     }
        // }
        return false;
    }
}
if (!function_exists('removeNullOrEmptyString')) {
    function removeNullOrEmptyString(array $input)
    {
        return array_filter($input, function ($v) {
            return $v !== null && $v !== '';
        }, ARRAY_FILTER_USE_BOTH);
    }
}
