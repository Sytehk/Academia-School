<?php

namespace Modules\Hostel\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Hostel\Models\MealEntry;

class MealEntryController extends Controller
{
    public function index(Request $request, $class = ''): JsonResponse
    {
        $query = MealEntry::with(['student:id,first_name,last_name,user_id', 'meal:id,meal_name'])
            ->select('id', 'student_id', 'meal_id', 'date', 'meal_price')
            ->orderByDesc('id');

        // 🔍 Optional fast search
        if ($search = trim($request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('meal_price', 'like', "{$search}%")
                    ->orWhere('date', 'like', "{$search}%")
                    ->orWhereHas('student', function ($q2) use ($search) {
                        $q2->where('name', 'like', "{$search}%");
                    })
                    ->orWhereHas('meal', function ($q3) use ($search) {
                        $q3->where('meal_name', 'like', "{$search}%");
                    });
            });
        }

        // 📄 Pagination (default 20 per page)
        $perPage = (int) $request->get('per_page', 20);
        $mealEntries = $query->paginate($perPage);

        return $this->responseSuccess($mealEntries, 'Meal entries fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'meal_id' => 'required|exists:meals,id',
            'date' => 'required|date',
            'meal_price' => 'required|numeric',
        ]);

        $mealEntry = new MealEntry;
        $mealEntry->institute_id = get_institute_id();
        $mealEntry->branch_id = get_branch_id();
        $mealEntry->student_id = $request->student_id;
        $mealEntry->meal_id = $request->meal_id;
        $mealEntry->date = $request->date;
        $mealEntry->meal_price = $request->meal_price;
        $mealEntry->save();

        return $this->responseSuccess($mealEntry, 'Meal Entry fetch successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $mealEntry = MealEntry::where('id', $id)->first();

        return $this->responseSuccess($mealEntry, 'Meal Entry fetch successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'meal_id' => 'required|exists:meals,id',
            'date' => 'required|date',
            'meal_price' => 'required|numeric',
        ]);

        $mealEntry = MealEntry::find($id);
        if (! $mealEntry) {
            return $this->responseError([], _lang('Something went wrong. Member can not be found.'));
        }

        if ($request->student_id) {
            $mealEntry->student_id = $request->student_id;
        }
        $mealEntry->meal_id = $request->meal_id;
        $mealEntry->date = $request->date;
        $mealEntry->meal_price = $request->meal_price;
        $mealEntry->save();

        return $this->responseSuccess($mealEntry, 'Meal Entry fetch successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $mealEntry = MealEntry::find($id);
        if (! $mealEntry) {
            return $this->responseError([], _lang('Something went wrong. Member can not be found.'));
        }

        $mealEntry->delete();

        return $this->responseSuccess([], 'Meal Entry fetch successfully.');
    }
}
