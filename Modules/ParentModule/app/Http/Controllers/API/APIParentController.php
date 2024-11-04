<?php

namespace Modules\ParentModule\Http\Controllers\API;

use App\Enums\UserLogAction;
use App\Http\Controllers\Controller;
use App\Traits\Trackable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;

class APIParentController extends Controller
{
    use Trackable;

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $parent_models = ParentModel::select(
            'parent_models.id AS id',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.phone',
            'users.image',
            'users.role_id',
            'users.status',
            'users.user_status',
            'users.user_type',
            'parent_models.parent_name',
            'parent_models.parent_profession',
            'parent_models.parent_phone',
            'parent_models.present_address',
            'parent_models.permanent_address',
        )
            ->join('users', 'users.id', '=', 'parent_models.user_id')
            ->orderBy('parent_models.id', 'DESC')->paginate($perPage);

        return $this->responseSuccess($parent_models, 'Parent have been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|numeric|unique:users',
            'email' => 'nullable|string|max:50|unique:users',
            'parent_profession' => 'nullable',
            'present_address' => 'nullable|string|max:50',
            'permanent_address' => 'nullable|string|max:50',
            'password' => 'required|string|min:6|confirmed',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = fileUploader('users/', 'png', $request->file('image'));
        }

        DB::beginTransaction();
        try {
            $user = new User;
            $user->institute_id = get_institute_id();
            $user->branch_id = get_branch_id();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->user_type = 'Parent';
            $user->phone = $request->phone;
            $user->image = $imagePath ?? null; // default image
            $user->save();

            $userId = $user->id;
            $detail1 = 'Parent Create';
            $this->trackAction(UserLogAction::CREATE, User::class, $userId, $detail1);

            $parent = new ParentModel;
            $parent->institute_id = get_institute_id();
            $parent->branch_id = get_branch_id();
            $parent->user_id = $user->id;
            $parent->parent_name = $request->name;
            $parent->parent_profession = $request->parent_profession;
            $parent->parent_phone = $request->phone;
            $parent->present_address = $request->present_address;
            $parent->permanent_address = $request->permanent_address;
            $parent->access_key = $request->password;
            $parent->save();

            $parentId = $parent->id;
            $detail2 = 'Parent Create';
            $this->trackAction(UserLogAction::CREATE, ParentModel::class, $parentId, $detail2);

            DB::commit();

            return $this->responseSuccess($parent, 'Parent have been create successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $parent = ParentModel::select(
            'parent_models.id AS id',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.phone',
            'users.image',
            'users.role_id',
            'users.status',
            'users.user_status',
            'users.user_type',
            'parent_models.parent_name',
            'parent_models.parent_profession',
            'parent_models.parent_phone',
            'parent_models.present_address',
            'parent_models.permanent_address',
        )
            ->join('users', 'users.id', '=', 'parent_models.user_id')
            ->where('parent_models.id', intval($id))
            ->first();

        if (! $parent) {
            return $this->responseError([], _lang('Parent not found.'), 404);
        }

        return $this->responseSuccess($parent, 'Parent have been fetched successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $parent = ParentModel::find($id);
        if (! $parent) {
            return $this->responseError([], _lang('Parent not found.'), 404);
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:50|unique:users,email,'.$parent->user_id,
            'parent_profession' => 'nullable',
            'present_address' => 'nullable|string|max:50',
            'permanent_address' => 'nullable|string|max:50',
            'password' => 'nullable|min:6|confirmed',
            'image' => 'nullable|image|max:5120',
        ]);

        try {
            $parent->parent_name = $request->name;
            $parent->parent_profession = $request->parent_profession;
            $parent->parent_phone = $request->phone;
            $parent->present_address = $request->present_address;
            $parent->permanent_address = $request->permanent_address;
            if ($request->password) {
                $parent->access_key = $request->password;
            }
            // Persist the profile changes regardless of whether the password changed
            // (previously save() only ran inside the password branch, so name /
            // profession / address edits silently did nothing).
            $parent->save();

            $model_id1 = $parent->id;
            $detail1 = 'Parent Update';
            $this->trackAction(UserLogAction::UPDATE, ParentModel::class, $model_id1, $detail1);

            $user = User::find($parent->user_id);
            if ($user) {
                $user->name = $request->name;
                $user->email = $request->email;
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }

                $imagePath = null;
                if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
                    $imagePath = fileUploader('users/', 'png', $request['image'], $parent->user?->image);
                    $user->image = $imagePath;
                }
                $user->save();

                $model_id2 = $user->id;
                $detail2 = 'Parent Update';
                $this->trackAction(UserLogAction::UPDATE, User::class, $model_id2, $detail2);
            }

            return $this->responseSuccess($parent, 'Parent have been update successfully.');
        } catch (\Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        // Find teacher by ID
        $parent = ParentModel::find($id);
        if (! $parent) {
            return $this->responseError([], _lang('Parent not found.'), 404);
        }

        // Find associated user
        $user = $parent->user_id ? User::find($parent->user_id) : null;
        if (! $user) {
            return $this->responseError([], _lang('Parent not found.'), 404);
        }

        $this->trackAction(UserLogAction::DELETE, ParentModel::class, $parent->id, 'Parent Deleted');
        $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'Parent Credentials Deleted');

        DB::transaction(function () use ($parent, $user) {
            // Detach children so students.parent_id does not dangle at a
            // soft-deleted parent row (it would still resolve in joins).
            Student::where('parent_id', $parent->id)
                ->update(['parent_id' => null]);

            $user->delete();
            $parent->delete();
        });

        return $this->responseSuccess([], 'Parent has been deleted successfully.');
    }
}
