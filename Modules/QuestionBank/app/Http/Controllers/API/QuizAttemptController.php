<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use App\Traits\RequestSanitizerTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\QuestionBank\Models\Question;
use Modules\QuestionBank\Models\Quiz;
use Modules\QuestionBank\Models\QuizAttempt;
use Modules\QuestionBank\Models\QuizResult;

class QuizAttemptController extends Controller
{
    use Authenticatable, RequestSanitizerTrait;

    public function __construct() {}

    public function startQuiz(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => 'required|tenant_exists:quizzes,id',
        ]);

        $userId = getUserId();
        $quiz = Quiz::where('id', $validated['quiz_id'])
            ->where('status', 'active') // Ensure quiz is active
            ->first();
        if (! $quiz) {
            return $this->responseError([], 'Quiz not found.', 404);
        }
        // ✅ Check allowed attempts
        if ($quiz->attempts_allowed !== null) {
            $previousAttempts = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $userId)
                ->where('status', 'submitted')
                ->count();

            if ($previousAttempts >= $quiz->attempts_allowed) {
                return $this->responseError([], 'You have already reached the maximum number of attempts.', 403);
            }
        }

        // Get latest attempt for this user & quiz
        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', getUserId())
            ->where('status', 'started') // Ensure quiz is active
            ->orderByDesc('started_at')
            ->first();

        if ($existingAttempt) {
            $limit = $quiz->timeLimitSeconds();
            // An untimed quiz's open attempt never expires; a timed one is resumed
            // only while it is still inside its limit.
            $expireTime = $limit === null ? null : Carbon::parse($existingAttempt->started_at)->addSeconds($limit);

            if ($expireTime === null || now()->lt($expireTime)) {
                // ⏱️ Still within time — return existing attempt
                return response()->json([
                    'message' => 'Existing quiz attempt still active.',
                    'attempt_id' => $existingAttempt->id,
                    'started_at' => $existingAttempt->started_at,
                    'expires_at' => $expireTime,
                ]);
            }
        }

        // 🆕 Create new attempt
        $newAttempt = QuizAttempt::create([
            'institute_id' => $this->getCurrentInstituteId(),
            'quiz_id' => $quiz->id,
            'user_id' => getUserId(),
            'started_at' => now(),
            'status' => 'started',
        ]);

        return response()->json([
            'message' => 'New quiz attempt started.',
            'attempt_id' => $newAttempt->id,
            'started_at' => $newAttempt->started_at,
            'expires_at' => $quiz->timeLimitSeconds() !== null ? now()->addSeconds($quiz->timeLimitSeconds()) : null,
        ]);
    }

    public function quizDetails(int $id): JsonResponse
    {
        try {
            $quiz = Quiz::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (! $quiz) {
                return $this->responseError([], 'Quiz not found.', 404);
            }

            // Answer keys and explanations are only ever returned by quizResults()
            // after submission — sending them here would let a student read the
            // answers while the attempt is still running.
            $questionColumns = ['id', 'type', 'question', 'options'];

            // Retrieve quiz questions
            $questionIds = is_array($quiz->question_ids) ? $quiz->question_ids : [];
            $questions = Question::whereIn('id', $questionIds)
                ->select($questionColumns)
                ->get();

            return $this->responseSuccess([
                'quiz' => $quiz,
                'questions' => $questions,
            ], 'Quiz has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function quizSubmit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => 'required|tenant_exists:quizzes,id',
            'attempt_id' => 'required|tenant_exists:quiz_attempts,id',
            'answers' => 'nullable|array|min:1',
            'answers.*.question_id' => 'nullable|tenant_exists:questions,id',
            'answers.*.selected_options' => 'nullable|array',
        ]);

        $quiz = Quiz::where('id', (int) $validated['quiz_id'])->first();
        if (! $quiz) {
            return $this->responseError([], 'Quiz not found.', 404);
        }

        $userId = getUserId();
        if ($quiz->attempts_allowed !== null) {
            $previousAttempts = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $userId)
                ->where('status', 'submitted')
                ->count();

            if ($previousAttempts >= $quiz->attempts_allowed) {
                return $this->responseError([], 'You have already reached the maximum number of attempts.', 403);
            }
        }

        $attempt = QuizAttempt::where('id', $validated['attempt_id'])
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->first();

        if (! $attempt) {
            return $this->responseError([], 'No quiz attempt found. Please start the quiz first.', 404);
        }

        if ($attempt->status === 'submitted') {
            return $this->responseError([], 'Quiz has already been submitted.', 422);
        }

        if ($quiz->submissionWindowClosed($attempt->started_at)) {
            return $this->responseError([], 'The time limit for this quiz has expired.', 422);
        }

        $answers = collect($validated['answers']);
        $questions = Question::whereIn('id', $answers->pluck('question_id'))->get();
        $totalQuestions = $questions->count();

        $totalScore = 0;
        $negativeMarks = 0;
        $correctCount = 0;
        $incorrectCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($questions as $question) {
                $userAnswer = $answers->firstWhere('question_id', $question->id)['selected_options'] ?? [];
                $userAnswer = is_array($userAnswer) ? $userAnswer : [];

                if ($question->type == 'multiple_true_false') {
                    $correctAnswers = is_array($question->correct_answer)
                        ? $question->correct_answer
                        : json_decode($question->correct_answer, true);

                    if (! is_array($correctAnswers) || count($correctAnswers) === 0) {
                        $skippedCount++;

                        continue;
                    }

                    $optionCount = count($correctAnswers);
                    $markPerOption = $quiz->marks_per_question / $optionCount;
                    $negMarkPerOption = $quiz->negative_marks_per_wrong_answer / $optionCount;

                    for ($i = 0; $i < $optionCount; $i++) {
                        $userOption = $userAnswer[$i] ?? null;
                        $correctOption = $correctAnswers[$i] ?? null;

                        if ($userOption === null) {
                            $skippedCount++;

                            continue;
                        }

                        if ($userOption === $correctOption) {
                            $totalScore += $markPerOption;
                            $correctCount++;
                        } else {
                            $incorrectCount++;
                            $totalScore -= $negMarkPerOption;
                            $negativeMarks += $negMarkPerOption;
                        }
                    }
                } else {
                    $correctAnswers = is_array($question->correct_answer)
                        ? $question->correct_answer
                        : json_decode($question->correct_answer, true);
                    $userAnswer = is_array($userAnswer) ? $userAnswer : [$userAnswer];
                    // Handle skipped question (no answer given)
                    if (empty(array_filter($userAnswer, fn ($val) => $val !== null && $val !== ''))) {
                        $skippedCount++;
                    }
                    sort($correctAnswers);
                    sort($userAnswer);
                    if ($correctAnswers === $userAnswer) {
                        $totalScore += $quiz->marks_per_question;
                        $correctCount++;
                    } else {
                        $incorrectCount++;
                        $totalScore -= $quiz->negative_marks_per_wrong_answer;
                        $negativeMarks += $quiz->negative_marks_per_wrong_answer;
                    }
                }
            }

            $finalScore = round($totalScore, 2);
            $isPassed = $finalScore >= $quiz->pass_mark;
            $attempt->update([
                'answers' => json_encode($validated['answers']),
                'score' => $finalScore,
                'status' => 'submitted',
                'submitted_at' => now(),
                'is_passed' => $isPassed,
            ]);

            $result = QuizResult::create([
                'institute_id' => $this->getCurrentInstituteId(),
                'quiz_id' => $quiz->id,
                'quiz_attempt_id' => $attempt->id,
                'score' => (float) number_format($finalScore, 2),
                'negative_marks' => (float) number_format($negativeMarks, 2),
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'skipped_count' => $skippedCount,
                'question_count' => $totalQuestions,
                'is_passed' => $isPassed ? 'Pass' : 'Fail',
                'position' => 0, // placeholder
            ]);

            $submittedAttempts = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('status', 'submitted')
                ->orderByDesc('score')
                ->orderBy('submitted_at')
                ->pluck('id');

            foreach ($submittedAttempts as $index => $attemptId) {
                QuizResult::where('quiz_attempt_id', $attemptId)->update([
                    'position' => $index + 1,
                ]);
            }

            DB::commit();

            return $this->responseSuccess([
                'message' => 'Quiz submitted successfully!',
                'quiz_id' => $quiz->id,
                'result_visibility' => $quiz->result_visibility,
                'attempt_id' => $attempt->id,
                'total_score' => (float) number_format($finalScore, 2),
                'negative_marks' => (float) number_format($negativeMarks, 2),
                'correct_answers' => $correctCount,
                'incorrect_answers' => $incorrectCount,
                'skipped_questions' => $skippedCount,
                'question_count' => $totalQuestions,
                'pass_mark' => $quiz->pass_mark,
                'is_passed' => $isPassed ? 'Pass' : 'Fail',
                'position' => $result->position,
            ], 'Quiz Attempt recorded successfully!');
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], 'There was an error while submitting the quiz. Please try again.');
        }
    }

    public function quizResults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => 'required|tenant_exists:quizzes,id',
            'attempt_id' => 'required|tenant_exists:quiz_attempts,id',
        ]);

        $quiz = Quiz::findOrFail((int) $validated['quiz_id']);
        $userId = getUserId();

        if ($quiz->result_visibility === 'never') {
            // ✅ Check if quiz end time has not passed yet
            if (Carbon::parse($quiz->end_time)->greaterThan(Carbon::now())) {
                $formattedEndTime = Carbon::parse($quiz->end_time)->format('d F, Y h:i:s A');

                return $this->responseError([], 'Result not published yet. Please check back after: '.$formattedEndTime, 403);
            }
        }

        $attempt = QuizAttempt::where('id', $validated['attempt_id'])
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->first();
        if (! $attempt) {
            return $this->responseError([], 'Quiz attempt not found.', 404);
        }

        $quizResult = QuizResult::where('quiz_attempt_id', $attempt->id)->first();
        if (! $quizResult) {
            return $this->responseError([], 'Quiz result not found.', 404);
        }

        $studentAnswers = json_decode($attempt->answers, true);
        $questions = Question::whereIn('id', $quiz->question_ids)->get();

        $questionsData = [];
        foreach ($questions as $question) {
            $submittedAnswer = collect($studentAnswers)->firstWhere('question_id', $question->id)['selected_options'] ?? [];
            $submittedAnswer = is_array($submittedAnswer) ? $submittedAnswer : [$submittedAnswer];

            $correctAnswer = is_array($question->correct_answer)
                ? $question->correct_answer
                : json_decode($question->correct_answer, true);
            $correctAnswer = is_array($correctAnswer) ? $correctAnswer : [$correctAnswer];

            $optionResults = [];
            $awardedMark = 0;

            if ($question->type === 'multiple_true_false') {
                $totalOptions = count($correctAnswer);
                $markPerOption = $quiz->marks_per_question / $totalOptions;
                $negPerOption = $quiz->negative_marks_per_wrong_answer / $totalOptions ?? 0;

                foreach ($correctAnswer as $i => $correctVal) {
                    $studentVal = $submittedAnswer[$i] ?? null;
                    if ($studentVal === null || $studentVal === '') {
                        $optionResults[] = [
                            'index' => $i,
                            'status' => 'Skipped',
                            'marks' => 0,
                        ];
                    } elseif ($studentVal === $correctVal) {
                        $optionResults[] = [
                            'index' => $i,
                            'status' => 'Correct',
                            'marks' => $markPerOption,
                        ];
                        $awardedMark += $markPerOption;
                    } else {
                        $deduction = $negPerOption ?? 0;
                        $optionResults[] = [
                            'index' => $i,
                            'status' => 'Incorrect',
                            'marks' => -$deduction,
                        ];
                        $awardedMark -= $deduction;
                    }
                }
            } else {
                sort($correctAnswer);
                sort($submittedAnswer);

                if (empty(array_filter($submittedAnswer))) {
                    $optionResults[] = [
                        'status' => 'Skipped',
                        'marks' => 0,
                    ];
                } elseif ($correctAnswer === $submittedAnswer) {
                    $optionResults[] = [
                        'status' => 'Correct',
                        'marks' => $quiz->marks_per_question,
                    ];
                    $awardedMark = $quiz->marks_per_question;
                } else {
                    $deduction = $quiz->negative_marks_per_wrong_answer ?? 0;
                    $optionResults[] = [
                        'status' => 'Incorrect',
                        'marks' => -$deduction,
                    ];
                    $awardedMark = -$deduction;
                }
            }

            $questionsData[] = [
                'question_id' => $question->id,
                'question_text' => $question->question,
                'question_type' => $question->type,
                'question_options' => $question->options,
                'user_answer' => $submittedAnswer,
                'correct_answer' => $correctAnswer,
                'explanation' => $question->explanation ?? 'No explanation provided',
                'marks_awarded_per_question' => round($awardedMark, 2),
                'option_level_result' => $optionResults,
            ];
        }

        $totalQuestions = $quizResult->question_count;
        $correctCount = $quizResult->correct_count;
        $incorrectCount = $quizResult->incorrect_count;
        $skippedCount = $quizResult->skipped_count;
        $totalMarks = $totalQuestions * $quiz->marks_per_question;

        $obtainedMarks = $quizResult->score;
        $highestMarks = QuizResult::where('quiz_id', $quiz->id)->max('score');
        $data = [
            'quiz_info' => [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'quiz_total_questions' => $totalQuestions,
                'quiz_time_limit_value' => $quiz->time_limit_value,
                'quiz_time_limit_unit' => $quiz->time_limit_unit,
                'description' => $quiz->description,
                'type' => $quiz->type,
            ],

            // ✅ Renamed to reflect clear meaning
            'answer_breakdown' => [
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctCount,
                'wrong_answers' => $incorrectCount,
                'unanswered_questions' => $skippedCount,
            ],

            // ✅ Clear naming + fixed mark values
            'marks_summary' => [
                'marks_from_correct_answers' => $correctCount * $quiz->marks_per_question,
                'negative_marks' => $quizResult->negative_marks,
                'total_obtained_marks' => $obtainedMarks,
                'highest_marks_in_quiz' => $highestMarks,
                'full_marks' => $totalMarks,
            ],

            // ✅ Split into meaningful keys for positions
            'ranking_positions' => [
                'position' => $quizResult->position,
            ],
            'questions' => $questionsData,
        ];

        return $this->responseSuccess($data, 'Quiz result retrieved successfully.');
    }
}
