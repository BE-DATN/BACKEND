<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\answer;
use App\Models\question;
use App\Models\quizze;
use App\Models\quizzProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function createQuiz(Request $request)
    {
        if (gettype($request->input('answers')) == 'string') {
            $request->merge(['answers' => json_decode($request->input('answers'), true)]);
        }
        // return response()->json([
        //     'type' => gettype($request->input('answers')),
        //     'request' => $request->input()
        // ], 200);
        try {
            $request->validate([
                'lesson_id' => 'required',
                'question' => 'required',
                'answers' => 'required|array',
                'correctAnswerIndex' => 'required',
            ]);
            $input = $request->input();
            // dd($input['lesson_id']);
            DB::beginTransaction();
            $quiz = quizze::create([
                'lesson_id' => $input['lesson_id']
            ]);
            // dd('error');
            if ($quiz) {
                return $this->createQuestion($quiz->id, $input);
            } else {
                DB::rollback();
                $data = [
                    'status' => false,
                    'message' => "Có xẩy ra lỗi khi tạo Quiz",
                ];
                return response()->json($data, 200);
            }
        } catch (\Throwable $th) {
            $data = [
                'status' => false,
                'where' => __METHOD__,
                'error' => $th->getMessage(),
            ];
            return response()->json($data, 200);
        }
    }
    public function createQuestion($quiz_id, $input)
    {
        try {
            $question = question::create([
                'quizz_id' => $quiz_id,
                'question' => $input['question'],
            ]);

            if ($question) {
                // dd($question->id);
                return $this->createAnswers($question->id, $input);
            } else {
                DB::rollback();
                $data = [
                    'status' => false,
                    'message' => "Có xẩy ra lỗi khi tạo Quiz",
                ];
                return response()->json($data, 200);
            }
        } catch (\Throwable $th) {
            $data = [
                'status' => false,
                'where' => __METHOD__,
                'error' => $th->getMessage(),
            ];
            return response()->json($data, 200);
        }
    }
    public function createAnswers($question_id, $input)
    {
        try {
            $status = true;
            $answers = removeNullOrEmptyString($input['answers']);
            // dd($answers);
            for ($i = 0; $i < count($answers); $i++) {
                $answer = answer::create([
                    'question_id' => $question_id,
                    'answer' => $input['answers'][$i],
                    'is_correct' => $i == $input['correctAnswerIndex'] ? true : false,
                ]);
                if (!$answer) {
                    $status = false;
                    break;
                }
            }
            if ($status) {
                DB::commit();
                $data = [
                    'status' => true,
                    'message' => "Quiz đã được tạo",
                ];
            } else {
                DB::rollback();
                $data = [
                    'status' => false,
                    'message' => "Có xẩy ra lỗi khi tạo Quiz",
                ];
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            $data = [
                'status' => false,
                'where' => __METHOD__,
                'error' => $th->getMessage(),
            ];
            return response()->json($data, 200);
        }
    }
    public function doQuiz(Request $request)
    {
        $user = getCurrentUser();
        $request->validate([
            'quiz_id' => 'required',
            'answer_id' => 'required',
            'course_id' => 'required',
            'question_id' => 'required',
        ]);

        if ($quiz = quizze::find($request->input('quiz_id'))) {
            // $answers = DB::table('questions')
            // ->join('answers', 'questions.id', '=', 'answers.question_id')
            // ->select('answers.*')
            // ->where('questions.quizz_id', $request->input('quiz_id'))
            // ->where('answers.id', $request->input('answer_id'))
            // ->get();
            if ($answer = answer::find($request->input('answer_id'))) {
                $data = [
                    'status' => true,
                    'message' => 'Câu trả lời chính xác'
                ];
                if ($answer->is_correct) {
                    $done = false;
                    $question_id_done = quizzProgress::where('course_id', $request->input('course_id'))
                    ->where('user_id', array_get($user, 'id'))
                    ->get();
                    $question_id_done = $question_id_done->pluck('question_id')->toArray();
                    foreach ($question_id_done as $key => $value) {
                        if ($value == $request->input('question_id')) {
                            $done = true;
                            break;
                        }
                    }
                    if (!$done) {
                        $quizz_progess = quizzProgress::create([
                            'user_id' => array_get($user, 'id'),
                            'course_id' => $request->input('course_id'),
                            'correct_answers_num' => 1,
                            'question_id' => $request->input('question_id'),
                        ]);
                    } else {
                        $data = [
                            'status' => false,
                            'message' => 'Quiz này đã hoàn thành'
                        ];
                    }



                    
                } else {
                    $data = [
                        'status' => $answer->is_correct,
                        'message' => 'Câu trả lời không chính xác'
                    ];
                }
                // return response()->json($answer);
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Không tìm thấy answer này',
                ];
            }
        } else {
            $data = [
                'status' => false,
                'message' => 'Không tìm thấy quiz này',
            ];
        }
        return response()->json($data, 200);
    }
}
