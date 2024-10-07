<?php

namespace Modules\Student\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Quiz\Models\Answer;
use Modules\Quiz\Models\Question;
use Modules\Quiz\Models\Topic;

class StudentQuizController extends Controller
{
    public function quizPage(): JsonResponse
    {
        $topics = Topic::all();
        $questions = Question::all();

        $data = [];
        $data['topics'] = $topics;
        $data['questions'] = $questions;
        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Quiz Page not found.'));
        }

        return $this->responseSuccess($data, 'Student Quiz Page fetch successfully.');
    }

    public function quizStart(int $id): JsonResponse
    {
        $topic = Topic::where('id', $id)->first();
        $answers = Answer::where('topic_id', '=', $topic->topic_id)->first();

        $data = [];
        $data['topic'] = $topic;
        $data['answers'] = $answers;
        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Quiz Start not found.'));
        }

        return $this->responseSuccess($data, 'Student Quiz Start fetch successfully.');
    }

    public function quizFinish(int $id): JsonResponse
    {
        $auth = Auth::user();
        $topic = Topic::where('id', $id)->first();
        $questions = Question::where('topic_id', $id)->get();
        $count_questions = $questions->count();
        $answers = Answer::where('user_id', $auth->id)
            ->where('topic_id', $id)->get();

        if ($count_questions != $answers->count()) {
            foreach ($questions as $que) {
                $a = false;
                foreach ($answers as $ans) {
                    if ($que->id == $ans->question_id) {
                        $a = true;
                    }
                }
                if ($a == false) {
                    Answer::create([
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'topic_id' => $id,
                        'user_id' => $auth->id,
                        'question_id' => $que->id,
                        'user_answer' => 0,
                        'answer' => $que->answer,
                    ]);
                }
            }
        }

        $ans = Answer::all();
        $q = Question::all();

        $data = [];
        $data['ans'] = $ans;
        $data['q'] = $q;
        $data['topic'] = $topic;
        $data['answers'] = $answers;
        $data['count_questions'] = $count_questions;
        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Quiz Finish not found.'));
        }

        return $this->responseSuccess($data, 'Student Quiz Finish fetch successfully.');
    }
}
