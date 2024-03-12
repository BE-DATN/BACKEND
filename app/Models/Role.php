<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = [
        'profile_id',
        'role_id',
        'description'
    ];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'role_profiles', 'role_id', 'profile_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_roles', 'role_id', 'permission_id');
    }
}
