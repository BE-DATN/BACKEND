<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post_Comment extends Model
{
    use HasFactory;
    protected $table = 'post_comments';
    protected $fillable = ['user_id', 'post_id', 'content', 'status', 'reply_to'];
}
