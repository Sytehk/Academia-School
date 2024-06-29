<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Http\Requests\AcademicYearCreateRequest;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Services\AcademicYearService;

class APIAcademicYearsController extends Controller
{
    public function __construct(private readonly AcademicYearService $academicYearService) {}

    public function index(Request $request): JsonResponse
    {
        // Extracting filters and pagination from the request (defaults can be set)
        $filter = $request->only(['session', 'year']); // Add other filter fields as needed
        $perPage = $request->get('per_page', 100); // Default perPage is 100 if not provided

        // Passing filters and pagination to the service
        $academicYears = $this->academicYearService->getAcademicYears($filter, $perPage);

        return $this->responseSuccess($academicYears, 'Academic sessions fetched successfully.');
    }

    public function store(AcademicYearCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Check if academic year already exists
        $exists = AcademicYear::where('session', $data['session'])
            ->orWhere('year', $data['year'])
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('This academic year already exists.'));
        }

        $academicYear = $this->academicYearService->createAcademicYear($data);

        if (! $academicYear) {
            return $this->responseError([], _lang('Something went wrong. Academic year cannot be created.'));
        }

        return $this->responseSuccess($academicYear, _lang('Academic year has been created.'));
    }

    public function show(int $id): JsonResponse
    {
        $academicYear = $this->academicYearService->findAcademicYearById($id);

        if (! $academicYear) {
            return $this->responseError([], _lang('Something went wrong. Academic year can not be found.'), 404);
        }

        return $this->responseSuccess($academicYear, _lang('Academic year has been show.'));
    }

    public function update(AcademicYearCreateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        // Check if academic year already exists excluding current record
        $exists = AcademicYear::where(function ($query) use ($data) {
            $query->where('session', $data['session'])
                ->orWhere('year', $data['year']);
        })
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('This academic year already exists.'));
        }

        $academicYear = $this->academicYearService->updateAcademicYear($data, $id);

        if (! $academicYear) {
            return $this->responseError([], _lang('Something went wrong. Academic year cannot be found.'), 404);
        }

        return $this->responseSuccess($academicYear, _lang('Academic year has been updated.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $current_session = get_option('academic_year');

        if ($id == $current_session) {
            return $this->responseError([], _lang('Sorry, You cannot delete current Academic Session !'));
        }

        $academicYear = $this->academicYearService->deleteAcademicYearById($id);

        if (! $academicYear) {
            return $this->responseError([], _lang('Something went wrong. Academic year can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Academic year has been deleted.'));
    }
}
