<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Action\LoginAdminAction;
use Illuminate\Http\Request;
use App\DTO\User\UserDTO;


class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $data = ['dashboard'];
        return response()->json($data, 200,);
    }

    /**
     * adminlogin
     */
    public function login(Request $request, LoginAdminAction $adminAction, UserDTO $userDTO)
    {
        // dd($request->all());
        $remember_me = $request->input('remember_token');
        $firstCredentialValueType = isEmail($request->input('account')) ? 'email' : 'username';
        // Tạo thông tin đăng nhập tk / mk
        $credentials = [
            $firstCredentialValueType => $request->input('account'),
            'password' => $request->input('password')
        ];

        // Set hsd token
        $adminAction->setTokenLifeTime($remember_me);

        // Login & get token
        $token = $adminAction->login($credentials, $remember_me);
        $adminAction->createCookie($token, $remember_me);

        if (!$token) {
            return response()->json([
                'action' => "Login",
                'status' => false,
                'message' => 'TK hay MK sai rồi kìa má',
            ], 401);
        }

        return $adminAction->respondWithToken($token);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
