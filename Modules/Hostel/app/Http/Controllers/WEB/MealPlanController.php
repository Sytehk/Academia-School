<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Hostel\Models\Meal;
use Modules\Hostel\Models\MealPlan;

class MealPlanController extends Controller
{
    public function index(Request $request)
    {

        $query = MealPlan::with([
            'student.user',
            'meal',
        ])
            ->orderByDesc('id');

        if ($search = $request->get('search')) {

            $query->where(function ($q) use ($search) {

                $q->where('date', 'like', "%{$search}%")

                    ->orWhereHas(
                        'student.user',
                        function ($q2) use ($search) {

                            $q2->where('name', 'like', "%{$search}%");
                        }
                    )

                    ->orWhereHas(
                        'meal',
                        function ($q3) use ($search) {

                            $q3->where('meal_name', 'like', "%{$search}%");
                        }
                    );
            });
        }

        $mealPlans = $query->paginate(20);

        return view(
            'hostel::meal_plans.index',
            compact('mealPlans')
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
            'hostel::meal_plans.create',
            compact(
                'students',
                'meals'
            )
        );
    }

    public function store(Request $request)
    {

        $request->validate([

            'student_id' => 'required|exists:students,id',

            'meal_id' => 'required|exists:meals,id',

            'date' => 'required|date',

        ]);

        MealPlan::create([

            'institute_id' => get_institute_id(),

            'branch_id' => get_branch_id(),

            'student_id' => $request->student_id,

            'meal_id' => $request->meal_id,

            'date' => $request->date,

        ]);

        return redirect()

            ->route('meal-plans.index')

            ->with(
                'success',
                'Meal plan created successfully.'
            );
    }

    public function edit($id)
    {

        $mealPlan =
            MealPlan::findOrFail($id);

        $students =
            Student::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->with('user')
                ->get();

        $meals =
            Meal::all();

        return view(
            'hostel::meal_plans.edit',
            compact(
                'mealPlan',
                'students',
                'meals'
            )
        );
    }

    public function update(Request $request, $id)
    {

        $request->validate([

            'student_id' => 'required|exists:students,id',

            'meal_id' => 'required|exists:meals,id',

            'date' => 'required|date',

        ]);

        $mealPlan =
            MealPlan::findOrFail($id);

        $mealPlan->update([

            'student_id' => $request->student_id,

            'meal_id' => $request->meal_id,

            'date' => $request->date,

        ]);

        return redirect()

            ->route('meal-plans.index')

            ->with(
                'success',
                'Meal plan updated successfully.'
            );
    }

    public function destroy($id)
    {

        MealPlan::findOrFail($id)
            ->delete();

        return redirect()

            ->route('meal-plans.index')

            ->with(
                'success',
                'Meal plan deleted successfully.'
            );
    }
}
