<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Admin\AdminUserDTO;
use App\DTO\User\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Role_Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function getUser()
    {
        try {
            $users = User::all();
            $data = [
                'name' => 'Danh sách người dùng',
                'status' => true,
                'users' => AdminUserResource::collection($users)
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            //throw $th;
            $data = [
                'status' => false,
                'message' => 'Có xẩy ra lỗi vui lòng thử lại sau',
                'errors' => $th->getMessage()
            ];
            return response()->json($data, 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getUserById($id, AdminUserDTO $userDTO)
    {
        $data = User::find($id);
        if ($data) {
            $data = $userDTO->userDetail($data);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy tài khoản này',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);
    }

    public function store()
    {
        $errors = array();
        try {
            DB::beginTransaction();

            $userDTO = new UserDTO();

            $input = Validator::make(
                $this->request->input(),
                [
                    'username' => 'required|unique:users|max:255',
                    'email' => 'required|email|unique:users|max:255',
                    'password' => 'required|min:6',
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'gender' => 'required',
                    'phone' => 'required|regex:/^(0[1-9])+([0-9]{8,9})\b$/',
                    'address' => 'required',
                    'avata_img' => 'image|mimes:jpg,png,jpeg,gif,svg',
                ]
            );
            if ($input->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $input->errors()
                ], 404);
            } else {
                $user = User::create([
                    'username' => $this->request->username,
                    'email' => $this->request->email,
                    'password' => Hash::make($this->request->password),
                ]);

                $return = $this->createProfileForUser($user->id);
                if ($return->original['status']) {
                    DB::commit();
                    return response()->json([
                        'status' => true,
                        'data' => $userDTO->dataUser($user)
                    ], 200);
                } else {
                    DB::rollBack();
                    $errors[] = array_push($errors, $return->original['errors']);
                    return response()->json([
                        'status' => false,
                        'message' => 'Có xẩy ra lỗi khi tạo tài khoản',
                        'errors' => $this->createProfileForUser($user->id)->original['errors'],
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            $errors[] = array_push($errors, $th->getMessage());
            return response()->json([
                'status' => false,
                'errors' => $errors
            ]);
        }
    }


    public function createProfileForUser($id)
    {
        $errors = array();

        try {
            $profile = Profile::create([
                "user_id" => $id,
                "firstname" => $this->request->firstname,
                "lastname" => $this->request->lastname,
                "gender" => $this->request->gender,
                "phone" => $this->request->phone,
                "address" => $this->request->address,
                "avata_img" => $this->request->avata_img ? $this->request->avata_img : null,
            ]);

            // dd($this->createRoleForUser($profile->id)->original['status']);
            $return = $this->createRoleForUser($profile->id);
            if ($return->original['status']) {
                return response()->json([
                    'status' => true
                ]);
            } else {
                DB::rollBack();
                $errors[] = array_push($errors, $return->original['errors']);
                return response()->json([
                    'status' => false,
                    'errors' => $errors
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $errors[] = array_push($errors, $th->getMessage());
            return response()->json([
                'status' => false,
                'errors' => $errors
            ]);
        }
    }

    public function createRoleForUser($profileId, $role = 'STUDENT')
    {
        try {
            $role = $this->request->input('role') ? $this->request->input('role') : $role;
            $role = strtoupper($role);
            $roleId = Role::select('id')->where('name', 'like', "%{$role}%")->first()->id;

            $role = Role_Profile::insert([
                'profile_id' => $profileId,
                'role_id' => $roleId
            ]);

            if ($role) {
                return response()->json([
                    'status' => true
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false
                ]);
            }
        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ]);
        }
        // return true;
    }
    public function getRole()
    {
        try {
            $roles = Role::all();
            $data = [
                'status' => true,
                'roles' => $roles
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            $data = [
                'status' => false,
                'error' => $th->getMessage()
            ];
            return response()->json($data, 400);
        }
    }
}
