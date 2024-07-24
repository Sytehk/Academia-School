<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Exports\MarkSheetExport;
use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Services\MarkSheetService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExamMarkInputController extends Controller
{
    public function __construct(
        private readonly MarkSheetService $markSheetService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function examResultView(): Factory|View
    {
        $sessionId = get_option('academic_year');
        $results = ExamMark::with('student', 'class', 'group', 'subject', 'exam')
            ->when($sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->limit(500)
            ->get();

        return view('examination::mark_config.mark_result_view', compact('results'));
    }

    public function excelFileDownload(Request $request): BinaryFileResponse
    {
        return $this->export($request);
    }

    /**
     * Mark-entry template for one class/subject/exam, pre-filled with any marks
     * already stored. Same format MarkSheetService::import() reads back.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $data = $this->validatedSheetContext($request);
        $sheet = $this->markSheetService->sheet(
            $data['exam_id'], $data['class_id'], $data['subject_id'], $data['section_id'], $data['group_id']
        );
        $extension = $request->input('format') === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(
            new MarkSheetExport($sheet['students'], $sheet['components']->keys()->all()),
            "mark-sheet-exam{$data['exam_id']}-subject{$data['subject_id']}.{$extension}"
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx|max:5120']);
        $data = $this->validatedSheetContext($request);

        $rows = Excel::toArray(new class implements WithHeadingRow {}, $request->file('file'))[0] ?? [];

        try {
            $result = $this->markSheetService->import(
                $rows, $data['exam_id'], $data['class_id'], $data['subject_id'], $data['section_id'], $data['group_id']
            );
        } catch (ValidationException $e) {
            return back()->with('error', _lang('Import failed').': '.collect($e->errors())->flatten()->implode(' '));
        }

        return back()->with('success', _lang('Marks imported for').' '.$result['saved'].' '._lang('students.'));
    }

    /**
     * @return array{exam_id: int, class_id: int, subject_id: int, section_id: ?int, group_id: ?int}
     */
    private function validatedSheetContext(Request $request): array
    {
        abort_unless(can_access_system('exam_module_mark_input'), 403);

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
