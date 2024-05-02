<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class quizzProgress extends Model
{
    use HasFactory;
    protected $table = 'quizz_progress';
    protected $fillable = [
        'user_id',
        'course_id',
        'correct_answers_num',
        'question_id'
    ];
}
