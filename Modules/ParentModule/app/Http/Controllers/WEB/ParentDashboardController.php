<?php

namespace Modules\ParentModule\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\ParentModule\Services\ParentService;
use RealRashid\SweetAlert\Facades\Alert;

/**
 * Blade (web) parent panel.
 *
 * Every page is scoped to the parent's *active child* via {@see ParentService},
 * which enforces the ownership boundary — a parent can never reach another
 * family's data by URL or session tampering.
 */
class ParentDashboardController extends Controller
{
    public function __construct(private readonly ParentService $parents) {}

    public function dashboard(): View
    {
        return view('parentmodule::dashboard', [
            'data' => $this->parents->dashboard(),
            'children' => $this->parents->children(),
            'activeChildId' => $this->parents->activeChildId(),
        ]);
    }

    /**
     * Switch the active child. Ownership is enforced inside the service.
     */
    public function switchChild(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
        ]);

        $this->parents->setActiveChild((int) $validated['student_id']);

        Alert::success(_lang('Success'), _lang('Active child updated.'));

        return redirect()->back();
    }

    public function profile(): View
    {
        return view('parentmodule::profile', [
            'parent' => $this->parents->parent(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function attendance(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);

        return view('parentmodule::attendance', [
            'attendance' => $this->parents->attendance($month, $year),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function classRoutine(): View
    {
        return view('parentmodule::class_routine', [
            'data' => $this->parents->classRoutine(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function examRoutine(): View
    {
        return view('parentmodule::exam_routine', [
            'exams' => $this->parents->examRoutine(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function subjects(): View
    {
        return view('parentmodule::subjects', [
            'subjects' => $this->parents->subjects(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function assignments(Request $request): View
    {
        return view('parentmodule::assignments', [
            'assignments' => $this->parents->assignments((int) ($request->per_page ?: 10)),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function examResults(Request $request): View
    {
        $examId = (int) $request->input('exam_id');

        return view('parentmodule::exam_results', [
            'exams' => $this->parents->examList(),
            'result' => $examId ? $this->parents->examResult($examId) : null,
            'selectedExamId' => $examId,
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function feesUnpaid(): View
    {
        return view('parentmodule::fees.unpaid', [
            'fees' => $this->parents->unpaidFees(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function paymentHistory(): View
    {
        return view('parentmodule::fees.history', [
            'payment' => $this->parents->paymentHistory(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function attendanceFine(): View
    {
        return view('parentmodule::fees.attendance_fine', [
            'report' => $this->parents->attendanceFineReport(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function notices(): View
    {
        return view('parentmodule::notices', [
            'notices' => $this->parents->notices(),
        ]);
    }

    public function events(): View
    {
        return view('parentmodule::events', [
            'events' => $this->parents->events(),
        ]);
    }

    public function behaviors(): View
    {
        return view('parentmodule::behaviors', [
            'behaviors' => $this->parents->behaviors(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }

    public function library(): View
    {
        return view('parentmodule::library', [
            'library' => $this->parents->libraryHistory(),
            'child' => $this->parents->activeChildSession(),
        ]);
    }
}
