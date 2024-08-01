<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Hostel\Models\Meal;

class MealController extends Controller
{
    public function index(Request $request)
    {

        $query = Meal::orderByDesc('id');

        if ($search = $request->get('search')) {

            $query->where(function ($q) use ($search) {

                $q->where('meal_name', 'like', "%{$search}%")
                    ->orWhere('meal_type', 'like', "%{$search}%");
            });
        }

        $meals = $query->paginate(20);

        return view(
            'hostel::meal.index',
            compact('meals')
        );
    }

    public function create()
    {

        return view(
            'hostel::meal.create'
        );
    }

    public function store(Request $request)
    {

        $request->validate([

            'meal_name' => 'required',

            'meal_type' => 'required',

        ]);

        Meal::create([

            'institute_id' => get_institute_id(),

            'branch_id' => get_branch_id(),

            'meal_name' => $request->meal_name,

            'meal_type' => $request->meal_type,

        ]);

        return redirect()

            ->route('meals.index')

            ->with(
                'success',
                'Meal created successfully.'
            );
    }

    public function edit($id)
    {

        $meal = Meal::findOrFail($id);

        return view(

            'hostel::meal.edit',

            compact('meal')

        );
    }

    public function update(Request $request, $id)
    {

        $request->validate([

            'meal_name' => 'required',

            'meal_type' => 'required',

        ]);

        $meal = Meal::findOrFail($id);

        $meal->update([

            'meal_name' => $request->meal_name,

            'meal_type' => $request->meal_type,

        ]);

        return redirect()

            ->route('meals.index')

            ->with(
                'success',
                'Meal updated successfully.'
            );
    }

    public function destroy($id)
    {

        $meal = Meal::findOrFail($id);

        $meal->delete();

        return redirect()

            ->route('meals.index')

            ->with(
                'success',
                'Meal deleted successfully.'
            );
    }
}
