<?php

namespace App\DTO\Course;

class CourseDTO
{
    public function tranformRequest($request)
    {
        return [
            // 'post_id' => $user->id,
        ];
    }

    public function courseDetail($course) {
        return [
            'id' => $course->id,
            'name' => $course->name,
            'description' => $course->description,
            'thumbnail' => asset($course->thumbnail),
            'video_demo_url' => asset($course->video_demo_url),
            'views' => $course->views,
            'price' => $course->price,
            'status' => $course->status,
            'created_at' => date('Y-m-d H:i:s', strtotime($course->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($course->updated_at)),
        ];
    }
}
