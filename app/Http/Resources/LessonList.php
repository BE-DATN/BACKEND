<?php

namespace App\Http\Resources;

use App\Models\lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lession_id' => $this->id,
            'session_id' => $this->session_id,
            'lession_name' => $this->name,
            'lession_arrange' => $this->arrange,
            'lession_learned' => $this->learned,
            // 'lession_video_url' => asset($this->video_url),
            'lession_created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
            'lession_updated_at' => date('Y-m-d H:i:s', strtotime($this->updated_at)),
        ];
    }
}
