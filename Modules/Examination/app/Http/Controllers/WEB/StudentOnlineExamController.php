<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Examination\Exceptions\OnlineExamException;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Services\OnlineExamScoringService;

class StudentOnlineExamController extends Controller
{
    public function __construct(
        private readonly OnlineExamScoringService $scoringService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(): Factory|View
    {
        $student = $this->currentStudent();
        $session = $student?->studentSession;
        $exams = collect();

        if ($session) {
            $exams = OnlineExam::with(['subject', 'exam'])
                ->where('is_active', true)
                ->where('status', '!=', OnlineExam::STATUS_DRAFT)
                ->where('class_id', $session->class_id)
                ->when($session->section_id, function ($query) use ($session) {
                    $query->where(function ($inner) use ($session) {
                        $inner->whereNull('section_id')
                            ->orWhere('section_id', $session->section_id);
                    });
                })
                ->orderByDesc('start_at')
                ->get();
        }

        $attempts = OnlineExamAttempt::where('student_id', $student?->id)
            ->get()
            ->groupBy('online_exam_id');

        return view('examination::online_exams.student_index', compact('exams', 'attempts', 'student'));
    }

    public function start(int $id): RedirectResponse
    {
        $student = $this->currentStudent();
        abort_if(! $student, 403);

        $session = $this->academicContext->studentSessionForStudent((int) $student->id);
        abort_if(! $session, 403);

        $onlineExam = OnlineExam::findOrFail($id);
        abort_unless($this->scoringService->isEligible($onlineExam, $session), 403);

        try {
            $attempt = $this->scoringService->startAttempt($onlineExam, (int) $student->id, auth()->id());
        } catch (OnlineExamException $e) {
            return redirect()->route('student.online-exams.index')->with('error', _lang($e->getMessage()));
        }

        return redirect()->route('student.online-exams.take', $attempt->id);
    }

    public function take(int $attemptId): Factory|View|RedirectResponse
    {
        $attempt = $this->ownedAttempt($attemptId);
        if ($attempt->isExpired() && ! $attempt->isSubmitted()) {
            $this->scoringService->submit($attempt, $attempt->answers ?? [], true);
            $attempt->refresh();
        }

        if ($attempt->isSubmitted()) {
            return redirect()->route('student.online-exams.result', $attempt->id);
        }

        $questions = $this->scoringService->questionsForAttempt($attempt);

        return view('examination::online_exams.student_take', compact('attempt', 'questions'));
    }

    public function save(Request $request, int $attemptId): JsonResponse
    {
        $attempt = $this->ownedAttempt($attemptId);
        $answers = $request->input('answers', []);
        try {
            $attempt = $this->scoringService->saveAnswers($attempt, $answers);
        } catch (OnlineExamException $e) {
            return response()->json(['status' => $attempt->status, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => $attempt->status,
            'expires_at' => optional($attempt->expires_at)->toIso8601String(),
            'server_now' => now()->toIso8601String(),
        ]);
    }

    public function submit(Request $request, int $attemptId): RedirectResponse
    {
        $attempt = $this->ownedAttempt($attemptId);
        try {
            $this->scoringService->submit($attempt, $request->input('answers', $attempt->answers ?? []), false);
        } catch (OnlineExamException $e) {
            return redirect()->route('student.online-exams.result', $attempt->id)->with('error', _lang($e->getMessage()));
        }

        return redirect()->route('student.online-exams.result', $attempt->id)
            ->with('success', _lang('Exam submitted successfully.'));
    }

    public function result(int $attemptId): Factory|View
    {
        $attempt = $this->ownedAttempt($attemptId);
        abort_unless($attempt->isSubmitted(), 403);

        return view('examination::online_exams.student_result', compact('attempt'));
    }

    private function currentStudent(): ?Student
    {
        return Student::where('user_id', auth()->id())->with('studentSession')->first();
    }

    private function ownedAttempt(int $attemptId): OnlineExamAttempt
    {
        $student = $this->currentStudent();
        abort_if(! $student, 403);

        $attempt = OnlineExamAttempt::with('onlineExam.questions')->findOrFail($attemptId);
        abort_unless((int) $attempt->student_id === (int) $student->id, 403);

        return $attempt;
    }
}
