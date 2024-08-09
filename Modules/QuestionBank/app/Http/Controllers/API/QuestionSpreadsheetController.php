<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Modules\QuestionBank\Services\QuestionSpreadsheetService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuestionSpreadsheetController extends Controller
{
    public function __construct(private readonly QuestionSpreadsheetService $spreadsheet) {}

    /** The bank as CSV/XLSX (?format=xlsx), optionally filtered. */
    public function export(Request $request): BinaryFileResponse
    {
        $request->validate([
            'question_bank_subject_id' => 'nullable|integer',
            'question_bank_chapter_id' => 'nullable|integer',
            'question_category_id' => 'nullable|integer',
            'question_bank_difficulty_level_id' => 'nullable|integer',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $extension = $request->input('format') === 'xlsx' ? 'xlsx' : 'csv';

        return Excel::download(
            new ArrayExport($this->spreadsheet->exportRows($request->all()), $this->spreadsheet->headings()),
            "question-bank.{$extension}"
        );
    }

    /** An empty file with the import headings. */
    public function template(Request $request): BinaryFileResponse
    {
        $extension = $request->input('format') === 'xlsx' ? 'xlsx' : 'csv';

        return Excel::download(new ArrayExport(collect(), $this->spreadsheet->headings()), "question-import-template.{$extension}");
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx|max:10240']);

        $rows = Excel::toArray(new class implements WithHeadingRow {}, $request->file('file'))[0] ?? [];

        return $this->responseSuccess($this->spreadsheet->import($rows), 'Questions imported successfully.');
    }
}
