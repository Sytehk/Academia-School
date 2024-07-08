<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\Currency\CurrencyStoreRequest;
use Modules\Authentication\Http\Requests\Currency\CurrencyUpdateRequest;
use Modules\Authentication\Models\Currency;
use Modules\Authentication\Repositories\CurrencyRepository;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'Currencies have been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(CurrencyStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->validated()), 'Currency has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById((int) $id), 'Currency has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function update(CurrencyUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->validated()), 'Currency has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'Currency has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function statusUpdate(int $id): JsonResponse
    {
        try {
            $currency = Currency::find($id);

            if (! $currency) {
                return $this->responseError([], 'Currency not found.', 404);
            }

            $currency->status = (int) $currency->getRawOriginal('status') === 1 ? 2 : 1;
            $currency->save();

            return $this->responseSuccess(['status' => $currency->status], 'Currency status updated successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode() ?: 500);
        }
    }
}
