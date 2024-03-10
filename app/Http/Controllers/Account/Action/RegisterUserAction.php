<?php

namespace App\Http\Controllers\Account\Action;

use App\DTO\User\UserDTO;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Role_Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class RegisterUserAction
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        // dd($request->username);
    }
    public function createUser()
    {
        try {
            DB::beginTransaction();
            $userDTO = new UserDTO();
            $user = User::create([
                'username' => $this->request->username,
                'email' => $this->request->email,
                'password' => Hash::make($this->request->password),
            ]);
            // dd($user->id);
            
            if ($this->createProfileForUser($user->id)) {
                DB::commit();
                return $userDTO->dataUser($user);
            } else {
                DB::rollBack();
                return response()->json('Có xẩy ra lỗi khi tạo tài khoản', 400);
            }
            // dd('done');
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            // dd($th->getMessage());
            return response()->json($th->getMessage());
        }
        dd('done create_user');
    }

    public function createProfileForUser($id)
    {
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

            if ($this->createRoleForUser($profile->id)) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            // dd($th->getMessage());
            return response()->json($th->getMessage());
            // return false;
        }
        // return true;
    }

    public function createRoleForUser($profileId, $role = 'GUEST')
    {
        try {
            $role = $this->request->role ? $this->request->role : $role;
            $roleId = Role::select('id')->where('name', 'like', "%{$role}%")->first()->id;

            $role = Role_Profile::insert([
                'profile_id' => $profileId,
                'role_id' => $roleId
            ]);

            if ($role) {
                return true;
            } else {
                return false;
            }

        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();
            return response()->json($th->getMessage());
        }
        // return true;
    }
}
