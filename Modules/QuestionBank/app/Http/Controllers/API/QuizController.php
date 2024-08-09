<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\QuestionBank\Http\Requests\Quiz\QuizStoreRequest;
use Modules\QuestionBank\Http\Requests\Quiz\QuizUpdateRequest;
use Modules\QuestionBank\Models\Question;
use Modules\QuestionBank\Models\Quiz;
use Modules\QuestionBank\Repositories\QuizRepository;

class QuizController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuizRepository $quizRepository) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->quizRepository->getAll(request()->all()),
                'Quiz has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuizStoreRequest $request): JsonResponse
    {
        try {
            // Only fill question_ids if provided
            if (! isset($data['question_ids'])) {
                $data['question_ids'] = null;
            }

            $quiz = $this->quizRepository->create($request->all());

            return $this->responseSuccess(
                $quiz,
                'Quiz has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $quiz = Quiz::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (! $quiz) {
                return response()->json([
                    'status' => false,
                    'message' => 'Quiz not found.',
                    'data' => null,
                ], 404);
            }

            // Retrieve questions related to this quiz
            $questionIds = $quiz->question_ids ?? [];
            $questions = Question::whereIn('id', $questionIds)->get(); // Fetch questions as an array

            return $this->responseSuccess(
                [
                    'quiz' => $quiz,
                    'questions' => $questions, // Ensuring questions are included as an array
                ],
                'Quiz has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(QuizUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $data = $this->getUpdateRequest($request);

            // Convert ISO 8601 to MySQL datetime format
            $data['start_time'] = Carbon::parse($request->input('start_time'))->format('Y-m-d H:i:s');
            $data['end_time'] = Carbon::parse($request->input('end_time'))->format('Y-m-d H:i:s');

            return $this->responseSuccess(
                $this->quizRepository->update($id, $data),
                'Quiz has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->quizRepository->delete($id),
                'Quiz has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function updateQuestions(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'question_ids' => ['required', 'array'],
            'question_ids.*' => ['integer', 'tenant_exists:questions,id'],
        ]);

        $quiz = Quiz::where('id', $id)->first();
        if (! $quiz) {
            return $this->responseError([], 'Quiz not found.', 404);
        }
        $quiz->update([
            'question_ids' => $request->question_ids,
        ]);

        return $this->responseSuccess($quiz, 'Question IDs updated successfully.');
    }
}
