<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id',
        'name',
        'description',
        'thumbnail',
        'created_at',
        'updated_at',
        'arrange'
    ];
}
