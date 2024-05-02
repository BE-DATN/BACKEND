<?php

namespace App\Http\Resources;

use App\Models\lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lessions = lesson::where('session_id', $this->id)->get();
        return [
            'session_id' => $this->id,
            'course_id' => $this->course_id,
            'session_name' => $this->name,
            'session_arrange' => $this->id,
            'session_description' => $this->id,
            'session_thumbnail' => $this->thumbnail ? $this->thumbnail : null,
            'session_created_at' => date('Y-m-d', strtotime($this->created_at)),
            'session_updated_at' => date('Y-m-d', strtotime($this->updated_at)),
            'lessons' => LessonList::collection($lessions),
        ];
    }
}
