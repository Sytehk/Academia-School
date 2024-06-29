<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Imports\TeachersImport;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Services\DepartmentService;
use Modules\Academic\Services\TeacherBulkImportService;
use RealRashid\SweetAlert\Facades\Alert;

class TeacherBulkUploadController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departmentService,
        private readonly TeacherBulkImportService $teacherBulkImportService
    ) {}

    public function index(): Factory|View
    {
        $departments = $this->departmentService->getDepartments();

        return view('academic::teachers.teacher-bulk-file-upload', compact('departments'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'xlsx_file' => 'required|mimes:xlsx,csv|max:5120',
            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'designation' => 'required|string|max:50',
        ]);

        try {
            $import = new TeachersImport;
            Excel::import($import, $request->file('xlsx_file'));
        } catch (Exception $e) {
            Log::error('Teacher bulk import file could not be parsed: '.$e->getMessage());
            Alert::error('Error', _lang('The uploaded file could not be read. Please use the provided template.'));

            return redirect()->back();
        }

        if (empty($import->data)) {
            Alert::error('Error', _lang('No rows were found in the uploaded file.'));

            return redirect()->back();
        }

        $summary = $this->teacherBulkImportService->import(
            $import->data,
            $request->department_id ? (int) $request->department_id : null,
            (string) $request->designation
        );

        session()->flash('bulk_import_summary', $summary);

        if ($summary['imported'] > 0) {
            Alert::toast(
                _lang('Imported').": {$summary['imported']}, "._lang('Skipped').": {$summary['skipped']}, "._lang('Failed').": {$summary['failed']}",
                $summary['failed'] > 0 || $summary['skipped'] > 0 ? 'warning' : 'success'
            );
        } else {
            Alert::error('Error', _lang('No teachers were imported.').' ('.$summary['skipped'].' '._lang('skipped').', '.$summary['failed'].' '._lang('failed').')');
        }

        return redirect()->route('teachers.bulk-file-upload');
    }

    public function downloadDemoFile()
    {
        $filePath = public_path('uploads/files/teacher_demo.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'teacher_demo.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        abort(404, 'File not found');
    }
}
