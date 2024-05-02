<?php

namespace App\Http\Resources;

use App\Models\quizzProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $done = quizzProgress::where('question_id', $this->question_id)->get();
        $done = $done->pluck('question_id')->toArray();
        // dd($done);
        $status = '';
        $correct_answer = '';
        foreach ($done as $key => $value) {
            $status = $value == $this->question_id ? 'done' : 'todo';
        }
        return [
            'question_id' => $this->question_id,
            'id' => $this->id,
            'answer' => $this->answer,
            // 'is_correct' => $this->is_correct,
            'status' => $status,

        ];
    }
}
