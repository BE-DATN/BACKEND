<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'session_id',
        'name',
        'video_url',
        'learned',
        'created_at',
        'updated_at',
        'arrange'
    ];
}
