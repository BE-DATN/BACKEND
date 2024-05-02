<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rating_course extends Model
{
    use HasFactory;
    protected $fillable = [
        'rating',
        'title',
        'content',
        'user_id',
        'course_id',
    ];
}
