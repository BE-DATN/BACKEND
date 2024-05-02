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
            // 'video_demo_url' => asset($course->video_demo_url),
            'video_demo_url' => $course->video_demo_url ? asset($course->video_demo_url) : null,
            'views' => $course->views,
            'price' => $course->price,
            'status' => $course->status,
            'created_at' => date('Y-m-d H:i:s', strtotime($course->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($course->updated_at)),
        ];
    }

    public function lesson($lession) {
        return [
            'lession_id' => $lession->id,
            'session_id' => $lession->session_id,
            'lession_name' => $lession->name,
            'lession_arrange' => $lession->arrange,
            'lession_learned' => $lession->learned,
            'lession_video_url' => $lession->video_url,
            'lession_created_at' => date('Y-m-d H:i:s', strtotime($lession->created_at)),
            'lession_updated_at' => date('Y-m-d H:i:s', strtotime($lession->updated_at)),
        ];
    }
}
