<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Examination\Http\Requests\RemarkConfigRequest;
use Modules\Examination\Models\RemarkConfig;

class RemarksConfigController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $remarks_configs = RemarkConfig::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $remarks_configs,
                _lang('Remark configs has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(RemarkConfigRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Automatically get institute_id and branch_id (if stored in session or authentication)
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        $remarkConfig = RemarkConfig::create($data);

        return $this->responseSuccess(
            $remarkConfig,
            _lang('Remark Config has been submitted.')
        );
    }

    public function show(int $id): JsonResponse
    {
        $remarkConfig = RemarkConfig::where('id', $id)->first();

        if (! $remarkConfig) {
            return $this->responseError([], _lang('Something went wrong. Remark Config can not be show.'));
        }

        return $this->responseSuccess(
            $remarkConfig,
            _lang('Remark Config has been show.')
        );
    }

    public function update(RemarkConfigRequest $request, int $id): JsonResponse
    {
        $remarkConfig = RemarkConfig::where('id', $id)->first();
        $remarkConfig->update($request->validated());

        return $this->responseSuccess(
            $remarkConfig,
            _lang('Remark Configuration has been updated successfully.')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $remarkConfig = RemarkConfig::where('id', $id)->first();
        if (! $remarkConfig) {
            return $this->responseError([], _lang('Something went wrong. Remark Config can not be delete.'));
        }

        $remarkConfig->delete();

        return $this->responseSuccess(
            $remarkConfig,
            _lang('Remark Config has been delete.')
        );
    }
}
