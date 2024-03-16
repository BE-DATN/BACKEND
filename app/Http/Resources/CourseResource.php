<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $course->id,
            'name' => $course->name,
            'description' => $course->description,
            'thumbnail' => asset($course->thumbnail),
            'video_demo_url' => $course->video_demo_url,
            'views' => $course->views,
            'price' => $course->price,
            'status' => $course->status,
            'created_at' => date('Y-m-d H:i:s', strtotime($course->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($course->updated_at)),
        ];
    }
}
