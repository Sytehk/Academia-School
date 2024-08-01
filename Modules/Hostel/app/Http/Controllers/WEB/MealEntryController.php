<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Hostel\Models\Meal;
use Modules\Hostel\Models\MealEntry;

class MealEntryController extends Controller
{
    public function index(Request $request)
    {

        $query = MealEntry::with([
            'student.user',
            'meal',
        ])->orderByDesc('id');

        if ($search = $request->get('search')) {

            $query->where(function ($q) use ($search) {

                $q->where('meal_price', 'like', "%{$search}%")
                    ->orWhere('date', 'like', "%{$search}%")
                    ->orWhereHas('student.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('meal', function ($q3) use ($search) {
                        $q3->where('meal_name', 'like', "%{$search}%");
                    });
            });
        }

        $mealEntries = $query->paginate(20);

        return view(
            'hostel::meal_entries.index',
            compact('mealEntries')
        );
    }

    public function create()
    {

        $students = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->with('user')
            ->get();
        $meals = Meal::all();

        return view(
            'hostel::meal_entries.create',
            compact('students', 'meals')
        );
    }

    public function store(Request $request)
    {

        $request->validate([

            'student_id' => 'required|exists:students,id',
            'meal_id' => 'required|exists:meals,id',
            'date' => 'required|date',
            'meal_price' => 'required|numeric',

        ]);

        MealEntry::create([

            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'student_id' => $request->student_id,
            'meal_id' => $request->meal_id,
            'date' => $request->date,
            'meal_price' => $request->meal_price,

        ]);

        return redirect()
            ->route('meal-entries.index')
            ->with('success', 'Meal entry created successfully.');
    }

    public function edit($id)
    {

        $mealEntry = MealEntry::findOrFail($id);

        $students = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->with('user')
            ->get();
        $meals = Meal::all();

        return view(
            'hostel::meal_entries.edit',
            compact('mealEntry', 'students', 'meals')
        );
    }

    public function update(Request $request, $id)
    {

        $request->validate([

            'student_id' => 'required|exists:students,id',
            'meal_id' => 'required|exists:meals,id',
            'date' => 'required|date',
            'meal_price' => 'required|numeric',

        ]);

        $mealEntry = MealEntry::findOrFail($id);

        $mealEntry->update([

            'student_id' => $request->student_id,
            'meal_id' => $request->meal_id,
            'date' => $request->date,
            'meal_price' => $request->meal_price,

        ]);

        return redirect()
            ->route('meal-entries.index')
            ->with('success', 'Meal entry updated successfully.');
    }

    public function destroy($id)
    {

        MealEntry::findOrFail($id)->delete();

        return redirect()
            ->route('meal-entries.index')
            ->with('success', 'Meal entry deleted successfully.');
    }
}
