<?php

namespace App\DTO\Post;

class PostDTO
{
    public function tranformRequest($request)
    {
        return [
            // 'post_id' => $user->id,
        ];
    }

    public function postsDetail($posts) {
        return [
            'id' => $posts->id,
            'title' => $posts->title,
            'creator' => $posts->username ? $posts->username : null,
            'content' => $posts->content,
            'thumbnail' => asset($posts->thumbnail),
            'likes' => $posts->likes,
            'views' => $posts->views,
            'status' => $posts->status,
            'created_at' => date('Y-m-d H:i:s', strtotime($posts->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($posts->updated_at)),
        ];
    }
    public function postComment($cm, $user) {
        return [
            'id' => $cm->id,
            'creator' => $user->username,
            'content' => $cm->content,
            'status' => $cm->status,
            'created_at' => date('Y-m-d H:i:s', strtotime($cm->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($cm->updated_at)),
        ];
    }
}
