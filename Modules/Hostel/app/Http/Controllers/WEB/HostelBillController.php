<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Hostel\Models\HostelBill;

class HostelBillController extends Controller
{
    public function index(Request $request)
    {
        $query = HostelBill::with('student')
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('total_amount', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($student) use ($search) {
                        $student->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $bills = $query->paginate(10);

        return view('hostel::hostel_bills.index', compact('bills'));
    }

    public function create()
    {
        $students = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('first_name')
            ->get();

        return view('hostel::hostel_bills.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'hostel_fee' => 'required|numeric',
            'meal_fee' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        HostelBill::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'student_id' => $request->student_id,
            'hostel_fee' => $request->hostel_fee,
            'meal_fee' => $request->meal_fee,
            'total_amount' => $request->total_amount,
            'due_date' => $request->due_date,
        ]);

        return redirect()
            ->route('hostel-bills.index')
            ->with('success', _lang('Hostel bill created successfully.'));
    }

    public function edit($id)
    {
        $bill = HostelBill::findOrFail($id);

        $students = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('first_name')
            ->get();

        return view('hostel::hostel_bills.edit', compact(
            'bill',
            'students'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'hostel_fee' => 'required|numeric',
            'meal_fee' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $bill = HostelBill::findOrFail($id);

        $bill->update([
            'student_id' => $request->student_id,
            'hostel_fee' => $request->hostel_fee,
            'meal_fee' => $request->meal_fee,
            'total_amount' => $request->total_amount,
            'due_date' => $request->due_date,
        ]);

        return redirect()
            ->route('hostel-bills.index')
            ->with('success', _lang('Hostel bill updated successfully.'));
    }

    public function destroy($id)
    {
        $bill = HostelBill::findOrFail($id);

        $bill->delete();

        return redirect()
            ->route('hostel-bills.index')
            ->with('success', _lang('Hostel bill deleted successfully.'));
    }
}
