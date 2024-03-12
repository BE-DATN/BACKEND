<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //courses
        for ($i = 1; $i <= 15; $i++) {
            DB::table('courses')->insert([
                'created_by' => rand(1, 8),
                'name' => "Course $i",
                'description' => 'Description for Course '.$i,
                'price' => rand(500000, 2000000),
                'views' => rand(500, 2000),
                'status' => 1,
                'thumbnail' => 'path/to/thumbnail1.jpg',
                'video_demo_url' => 'path/to/video_demo1.mp4',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //sessions
        for ($i = 1; $i <= 15; $i++) {
            DB::table('sessions')->insert([
                'course_id' => $i,
                'name' => 'Session '. $i,
                'decription' => 'Description for Session '.$i,
                'thumbnail' => 'path/to/session_thumbnail'.$i.'.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //lessions
        for ($i = 1; $i <= 15; $i++) {
            DB::table('lessions')->insert([
                'session_id' => $i,
                'name' => 'Lesson '.$i,
                'learned' => false,
                'video_url' => 'path/to/lesson_video'.$i.'.mp4',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //quizzes
        for ($i = 1; $i <= 15; $i++) {
            DB::table('quizzes')->insert([
                'session_id' => 1,
                'description' => 'Quiz for Session '.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //questions
        for ($i = 1; $i <= 15; $i++) {
            DB::table('questions')->insert([
                'quizz_id' => $i,
                'question' => 'Question '.$i.' for Quiz '.$i.'',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //answers
        for ($i = 1; $i <= 15; $i++) {
            DB::table('answers')->insert([
                'question_id' => $i,
                'answer' => 'Answer '.$i.' for Question '.$i.'',
                'is_correct' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
