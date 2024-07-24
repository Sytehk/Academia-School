<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Exports\ExamResultExport;
use App\Exports\MarkSheetExport;
use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Services\MarkSheetService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MarkSheetController extends Controller
{
    public function __construct(
        private readonly MarkSheetService $markSheetService,
        private readonly AcademicContextService $academicContext,
    ) {}

    /**
     * Download the mark-entry template (pre-filled with existing marks), or
     * return it as JSON with ?format=json.
     */
    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        $data = $this->validateContext($request);
        $sheet = $this->markSheetService->sheet(
            $data['exam_id'], $data['class_id'], $data['subject_id'], $data['section_id'], $data['group_id']
        );

        if ($request->input('format') === 'json') {
            return $this->responseSuccess($sheet, 'Mark sheet fetched successfully.');
        }

        $extension = $request->input('format') === 'xlsx' ? 'xlsx' : 'csv';

        return Excel::download(
            new MarkSheetExport($sheet['students'], $sheet['components']->keys()->all()),
            "mark-sheet-exam{$data['exam_id']}-subject{$data['subject_id']}.{$extension}"
        );
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx|max:5120']);
        $data = $this->validateContext($request);

        $sheets = Excel::toArray(new class implements WithHeadingRow {}, $request->file('file'));
        $rows = $sheets[0] ?? [];

        $result = $this->markSheetService->import(
            $rows, $data['exam_id'], $data['class_id'], $data['subject_id'], $data['section_id'], $data['group_id']
        );

        return $this->responseSuccess($result, 'Marks imported successfully.');
    }

    /**
     * Stored results for an exam as a CSV (the previously unrouted
     * ExamResultExport), restricted to the caller's tenant and active session.
     */
    public function resultExport(Request $request): BinaryFileResponse
    {
        $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'subject_id' => ['nullable', 'integer', $this->academicContext->tenantExists('subjects')],
        ]);

        $classId = $request->filled('class_id') ? (int) $request->class_id : null;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $this->academicContext->assertClassSectionGroup($classId, $sectionId, $request->filled('group_id') ? (int) $request->group_id : null);

        $examMarks = ExamMark::with(['student.studentSession', 'class', 'group', 'subject', 'exam'])
            ->where('exam_id', (int) $request->exam_id)
            ->when($this->academicContext->currentSessionId(), fn ($q, $s) => $q->where('session_id', $s))
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($request->filled('group_id'), fn ($q) => $q->where('group_id', (int) $request->group_id))
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', (int) $request->subject_id))
            ->when($sectionId, fn ($q) => $q->whereHas('student.studentSession', fn ($s) => $s->where('section_id', $sectionId)))
            ->get();

        return Excel::download(new ExamResultExport($examMarks), 'results.csv');
    }

    /**
     * @return array{exam_id: int, class_id: int, subject_id: int, section_id: ?int, group_id: ?int}
     */
    private function validateContext(Request $request): array
    {
        $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'required|integer',
            'subject_id' => ['required', 'integer', $this->academicContext->tenantExists('subjects')],
            'section_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
        ]);

        return [
            'exam_id' => (int) $request->exam_id,
            'class_id' => (int) $request->class_id,
            'subject_id' => (int) $request->subject_id,
            'section_id' => $request->filled('section_id') ? (int) $request->section_id : null,
            'group_id' => $request->filled('group_id') ? (int) $request->group_id : null,
        ];
    }
}
