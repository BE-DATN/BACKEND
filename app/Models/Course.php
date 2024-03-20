<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    const limit = 10;
    protected $fillable = [
        'created_by',
        'name',
        'description',
        'price',
        'views',
        'status',
        'thumbnail',
        'video_demo_url'
    ];
    public function cartDetails()
    {
        return $this->hasMany(Cart_Detail::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(Cart_Detail::class);
    }

}
