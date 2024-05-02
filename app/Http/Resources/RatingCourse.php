<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingCourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->user_id);
        return [
            'id' => $this->id,
            'user' => $user ? $user->username : 'Ẩn danh',
            'avata' => asset($user->profile->avata_img),
            'video' => $this->video_url ? asset($this->video_url) : null,
            'content' => $this->content ? $this->content : '',
            'rating' => $this->rating,
            'title' => $this->title ? $this->title : null,
            'created_at' => $this->created_at ? date('Y-m-d H:i:s', strtotime($this->created_at)) : '',
            'updated_at' => $this->updated_at ? date('Y-m-d H:i:s', strtotime($this->updated_at)) : '',
        ];
    }
}
