<?php

namespace App\Http\Resources;

use App\Models\answer;
use App\Models\quizzProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $answers = answer::where('question_id', $this->question_id)->get();
        $done = quizzProgress::where('question_id', $this->question_id)->get();
        $done = $done->pluck('question_id')->toArray();
        $correct_answer = '';

        foreach ($done as $key => $id_done) {
            foreach ($answers as $key => $value) {
                // dd(($value));
                if ($id_done == $value->question_id) {
                    if ($value->is_correct) {
                        $correct_answer = $value->answer;
                        break;
                    }
                }
            }
        }
        return [
            'quiz_id' => $this->quizz_id,
            'question_id' => $this->question_id,
            'question' => $this->question,
            'anwsers' => AnswersResource::collection($answers),
            'correct_answer' => $correct_answer
        ];
    }
}
