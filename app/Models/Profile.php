<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 
        'firstname', 
        'lastname', 
        'gender', 
        'phone', 
        'address', 
        'avata_img',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    // public function role() {
    //     return $this->hasMany(Role_Profile::class, 'profile_id','id');
    // }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_profiles', 'profile_id', 'role_id');
    }
}
