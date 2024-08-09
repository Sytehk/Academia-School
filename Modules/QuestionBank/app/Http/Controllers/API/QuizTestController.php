<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\QuestionBank\Http\Requests\QuizTopic\QuizTopicStoreRequest;
use Modules\QuestionBank\Http\Requests\QuizTopic\QuizTopicUpdateRequest;
use Modules\QuestionBank\Models\Question;
use Modules\QuestionBank\Models\Quiz;
use Modules\QuestionBank\Models\QuizAttempt;
use Modules\QuestionBank\Models\QuizTopic;

class QuizTestController extends Controller
{
    use RequestSanitizerTrait;

    public function index(): JsonResponse
    {
        try {
            $quizTopic = QuizTopic::get();

            return $this->responseSuccess($quizTopic, 'QuizTopic has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuizTopicStoreRequest $request): JsonResponse
    {
        $user = getAuthUser();
        $data = $request->all();

        $result = DB::transaction(function () use ($data, $user) {
            // Step 1: Create quiz first
            $quiz = Quiz::create([
                'institute_id' => $user->institute_id,
                'branch_id' => $user->branch_id,
                'created_by' => $user->id,
                'title' => 'Quiz - '.now()->format('Y-m-d H:i:s'),
                'type' => $data['quiz_type'] ?? 'practice',
                'status' => 'active',
                'start_time' => now(),
                'end_time' => now()->addMinutes($data['time_limit'] ?? 30),
                'show_description_on_course_page' => 1,
                'has_time_limit' => 1,
                'time_limit_value' => $data['time_limit'],
                'time_limit_unit' => 'minutes',
            ]);

            $questions = collect();
            $topics = [];

            // Step 2: Create topics and pick questions
            foreach ($data['topics'] as $t) {
                $topic = QuizTopic::create([
                    'quiz_id' => $quiz->id,
                    'institute_id' => $user->institute_id,
                    'branch_id' => $user->branch_id,
                    'question_bank_subject_id' => $t['question_bank_subject_id'] ?? null,
                    'question_bank_chapter_id' => $t['question_bank_chapter_id'] ?? null,
                    'question_category_id' => $t['question_category_id'] ?? null,
                    'question_limit' => $t['limit'] ?? 10,
                    'status' => 1,
                ]);

                $topics[] = $topic;

                // Pick random questions for this topic
                $query = Question::query();
                if (! empty($t['question_bank_subject_id'])) {
                    $query->where('question_bank_subject_id', $t['question_bank_subject_id']);
                }
                if (! empty($t['question_bank_chapter_id'])) {
                    $query->where('question_bank_chapter_id', $t['question_bank_chapter_id']);
                }
                if (! empty($t['question_category_id'])) {
                    $query->where('question_category_id', $t['question_category_id']);
                }

                $questions = $questions->merge(
                    $query->inRandomOrder()->limit($t['limit'] ?? 10)->get()
                );
            }

            // Step 3: Shuffle all questions and save to quiz
            $questions = $questions->shuffle();
            $quiz->update(['question_ids' => $questions->pluck('id')->toArray()]);

            // Step 4: Create attempt (without storing answers)
            $attempt = QuizAttempt::create([
                'institute_id' => $user->institute_id,
                'branch_id' => $user->branch_id,
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'status' => 'started',
                'started_at' => now(),
            ]);

            return [
                'quiz' => $quiz,
                'topics' => $topics,
                'attempt' => $attempt,
                'questions' => $questions,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Quiz created successfully',
            'data' => [
                'quiz_id' => $result['quiz']->id,
                'attempt_id' => $result['attempt']->id,
                'quiz_type' => $result['quiz']->type,
                'time_limit' => $request->time_limit ?? 30,
                'total_questions' => $result['questions']->count(),
                'questions' => $result['questions'],
                'topics' => $result['topics'],
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess([], 'QuizTopic has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(QuizTopicUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess([], 'QuizTopic has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess([], 'QuizTopic has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
