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
            'content' => $posts->content,
            'thumbnail' => $posts->thumbnail,
            'likes' => $posts->likes,
            'views' => $posts->views,
            'created_at' => date('Y-m-d H:i:s', strtotime($posts->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($posts->updated_at)),
        ];
    }
}
