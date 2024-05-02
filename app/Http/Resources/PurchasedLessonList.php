<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class PurchasedLessonList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // DB::table('quizzes')->select('question');
        $quizs = DB::table('quizzes')
        ->join('questions', 'quizzes.id', '=', 'questions.quizz_id')
        ->select('questions.id as question_id', 'quizz_id', 'question')
        ->where('lesson_id', $this->id)
        ->get();
        return [
            'lession_id' => $this->id,
            'session_id' => $this->session_id,
            'lession_name' => $this->name,
            'lession_arrange' => $this->arrange,
            'lession_learned' => $this->learned,
            'lession_video_url' => asset($this->video_url),
            'lession_created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
            'lession_updated_at' => date('Y-m-d H:i:s', strtotime($this->updated_at)),
            'quizs' => QuizResource::collection($quizs)
        ];
    }
}
