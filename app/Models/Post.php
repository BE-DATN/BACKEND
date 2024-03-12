<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['created_by', 'title', 'content', 'thumbnail', 'status', 'likes', 'views'];
    const Limit = 6;
}
