<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart_Detail extends Model
{
    use HasFactory;
    protected $fillable = ['cart_id', 'course_id'];
    protected $table = 'cart_details';

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // public function course()
    // {
    //     return $this->belongsTo(Course::class);
    // }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
}
