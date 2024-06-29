<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Repositories\SectionRepository;
use Modules\Academic\Services\StudentBulkImportService;
use RealRashid\SweetAlert\Facades\Alert;

class StudentBulkUploadController extends Controller
{
    public function __construct(
        private readonly SectionRepository $sectionRepository,
        private readonly StudentBulkImportService $studentBulkImportService
    ) {}

    public function index(): Factory|View
    {
        $sections = $this->sectionRepository->getSections();

        return view('academic::students.student-bulk-file-upload', compact('sections'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'xlsx_file' => 'required|mimes:xlsx,csv|max:5120',
            'class' => ['required', 'numeric', Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'academic_year' => 'required|numeric|exists:academic_years,id',
            'section' => ['required', 'numeric', Rule::exists('sections', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'group' => ['required', 'numeric', Rule::exists('student_groups', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
        ]);

        try {
            $import = new StudentsImport;
            Excel::import($import, $request->file('xlsx_file'));
        } catch (Exception $e) {
            Log::error('Student bulk import file could not be parsed: '.$e->getMessage());
            Alert::error('Error', _lang('The uploaded file could not be read. Please use the provided template.'));

            return redirect()->back();
        }

        if (empty($import->data)) {
            Alert::error('Error', _lang('No rows were found in the uploaded file.'));

            return redirect()->back();
        }

        $summary = $this->studentBulkImportService->import(
            $import->data,
            (int) $request->class,
            (int) $request->section,
            (int) $request->group,
            (int) $request->academic_year
        );

        session()->flash('bulk_import_summary', $summary);

        if ($summary['imported'] > 0) {
            Alert::toast(
                _lang('Imported').": {$summary['imported']}, "._lang('Skipped').": {$summary['skipped']}, "._lang('Failed').": {$summary['failed']}",
                $summary['failed'] > 0 || $summary['skipped'] > 0 ? 'warning' : 'success'
            );
        } else {
            Alert::error('Error', _lang('No students were imported.').' ('.$summary['skipped'].' '._lang('skipped').', '.$summary['failed'].' '._lang('failed').')');
        }

        return redirect()->route('students.bulk-file-upload');
    }

    public function downloadDemoFile()
    {
        $filePath = public_path('uploads/files/student_entry_template.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'student_entry_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        abort(404, 'File not found');
    }
}
