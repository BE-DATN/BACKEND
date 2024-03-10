<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role_Profile extends Model
{
    use HasFactory;
    protected $table = 'role_profiles';
    protected $fillable = [
        'name', 
        'level',
        'decription'
    ];
    // public function profile() {
    //     return $this->be
    // }
}
