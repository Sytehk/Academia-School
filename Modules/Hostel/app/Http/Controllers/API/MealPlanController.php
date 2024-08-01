<?php

namespace Modules\Hostel\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Hostel\Models\MealPlan;

class MealPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MealPlan::with(['student:id,first_name,last_name,user_id', 'meal:id,meal_name'])
            ->select('id', 'student_id', 'meal_id', 'date')
            ->orderByDesc('id');

        // 🔍 Optional search (by student name, meal name, or date)
        if ($search = trim($request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('date', 'like', "{$search}%")
                    ->orWhereHas('student', function ($q2) use ($search) {
                        $q2->where('name', 'like', "{$search}%");
                    })
                    ->orWhereHas('meal', function ($q3) use ($search) {
                        $q3->where('meal_name', 'like', "{$search}%");
                    });
            });
        }

        // 🎯 Optional filters
        if ($studentId = $request->get('student_id')) {
            $query->where('student_id', $studentId);
        }

        if ($mealId = $request->get('meal_id')) {
            $query->where('meal_id', $mealId);
        }

        if ($from = $request->get('from_date')) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->get('to_date')) {
            $query->whereDate('date', '<=', $to);
        }

        // 📄 Pagination (default 20 per page)
        $perPage = (int) $request->get('per_page', 20);
        $mealPlans = $query->paginate($perPage);

        return $this->responseSuccess($mealPlans, 'Meal plans fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'meal_id' => 'required|exists:meals,id',
            'date' => 'required|date',
        ]);

        $mealPlan = new MealPlan;
        $mealPlan->institute_id = get_institute_id();
        $mealPlan->branch_id = get_branch_id();
        $mealPlan->student_id = $request->student_id;
        $mealPlan->meal_id = $request->meal_id;
        $mealPlan->date = $request->date;
        $mealPlan->save();

        return $this->responseSuccess($mealPlan, 'MealPlan create successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $mealPlan = MealPlan::find($id);
        if (! $mealPlan) {
            return $this->responseError([], _lang('Something went wrong. MealPlan can not be found.'));
        }

        return $this->responseSuccess($mealPlan, 'MealPlan create successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'meal_id' => 'required|exists:meals,id',
            'date' => 'required|date',
        ]);

        $mealPlan = MealPlan::find($id);
        if ($request->student_id) {
            $mealPlan->student_id = $request->student_id;
        }
        $mealPlan->meal_id = $request->meal_id;
        $mealPlan->date = $request->date;
        $mealPlan->save();

        return $this->responseSuccess($mealPlan, 'MealPlan update successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $mealPlan = MealPlan::find($id);
        if (! $mealPlan) {
            return $this->responseError([], _lang('Something went wrong. MealPlan can not be found.'));
        }
        $mealPlan->delete();

        return $this->responseSuccess([], 'MealPlan delete successfully.');
    }
}
