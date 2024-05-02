<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role_Profile extends Model
{
    use HasFactory;
    protected $table = 'role_profiles';
    protected $fillable = [
        'role_id',
        'name',
        'level',
        'decription',
        'user_id',
    ];
    // public function profile() {
    //     return $this->be
    // }
}
