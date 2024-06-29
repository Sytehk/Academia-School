<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Academic\Http\Requests\CustomFieldValue\CustomFieldValueStoreRequest;
use Modules\Academic\Http\Requests\CustomFieldValue\CustomFieldValueUpdateRequest;
use Modules\Academic\Models\CustomFieldValue;

class CustomFieldValueController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $query = CustomFieldValue::with('field')
                ->where('institute_id', auth()->user()->institute_id)
                ->where('branch_id', auth()->user()->branch_id);

            // --- Search ---
            if ($search = request('search')) {
                $query->whereHas('field', function ($q) use ($search) {
                    $q->where('field_name', 'like', "%{$search}%")
                        ->orWhere('label', 'like', "%{$search}%");
                })
                    ->orWhere('value', 'like', "%{$search}%");
            }

            // --- Filtering ---
            if ($status = request('status')) {
                $query->where('status', $status);
            }

            if ($fieldId = request('custom_field_id')) {
                $query->where('custom_field_id', $fieldId);
            }

            if ($modelType = request('model_type')) {
                $query->where('model_type', $modelType);
            }

            // --- Sorting ---
            $sortBy = request('sort_by', 'id');
            $sortOrder = request('sort_order', 'desc'); // asc or desc

            $query->orderBy($sortBy, $sortOrder);

            // --- Pagination ---
            $perPage = request('per_page', 15); // default 15
            $data = $query->paginate($perPage);

            return $this->responseSuccess($data, 'CustomFieldValue has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(CustomFieldValueStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $results = [];
            foreach ($data['fields'] as $field) {
                $item = CustomFieldValue::updateOrCreate(
                    [
                        'model_type' => $data['model_type'],
                        'model_id' => $data['model_id'],
                        'custom_field_id' => $field['custom_field_id'],
                        'institute_id' => auth()->user()->institute_id,
                        'branch_id' => auth()->user()->branch_id,
                    ],
                    [
                        'value' => $field['value'] ?? null,
                        'status' => 1,
                    ]
                );

                $results[] = $item;
            }

            return $this->responseSuccess($results, 'CustomFieldValue has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {

            $data = CustomFieldValue::with('field')->findOrFail($id);

            return $this->responseSuccess($data, 'CustomFieldValue has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(CustomFieldValueUpdateRequest $request, int $id): JsonResponse
    {
        try {

            $data = $request->validated();
            $results = [];

            foreach ($data['fields'] as $field) {

                $item = CustomFieldValue::where('id', $id)
                    ->update([
                        'value' => $field['value'] ?? null,
                        'updated_at' => now(),
                    ]);

                $results[] = $item;
            }

            return $this->responseSuccess($results, 'CustomFieldValue has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {

            CustomFieldValue::findOrFail($id)->delete();

            return $this->responseSuccess([], 'CustomFieldValue has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
