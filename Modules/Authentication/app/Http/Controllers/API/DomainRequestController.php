<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Authentication\Models\DomainRequest;
use Modules\Authentication\Models\Institute;

class DomainRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $status = $request->query('status');
            $perPage = (int) $request->query('per_page', 10);

            $query = DomainRequest::with(['institute:id,name,domain', 'requester:id,name', 'reviewer:id,name'])
                ->latest();

            if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $query->where('status', $status);
            }

            return $this->responseSuccess(
                $query->paginate($perPage > 0 ? $perPage : 10),
                'Domain requests have been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $domainRequest = DomainRequest::find($id);

            if (! $domainRequest) {
                return $this->responseError([], 'Domain request not found.', 404);
            }

            if ($domainRequest->status !== 'pending') {
                return $this->responseError([], 'Only pending requests can be approved.', 422);
            }

            $institute = Institute::find($domainRequest->institute_id);

            if (! $institute) {
                return $this->responseError([], 'Institute not found.', 404);
            }

            $institute->forceFill(['domain' => $domainRequest->requested_domain])->save();

            $domainRequest->forceFill([
                'status' => 'approved',
                'review_note' => $request->input('review_note'),
                'reviewed_by' => $request->user()?->id,
                'reviewed_at' => now(),
            ])->save();

            return $this->responseSuccess($domainRequest->fresh(), 'Domain request approved.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $domainRequest = DomainRequest::find($id);

            if (! $domainRequest) {
                return $this->responseError([], 'Domain request not found.', 404);
            }

            if ($domainRequest->status !== 'pending') {
                return $this->responseError([], 'Only pending requests can be rejected.', 422);
            }

            $domainRequest->forceFill([
                'status' => 'rejected',
                'review_note' => $request->input('review_note'),
                'reviewed_by' => $request->user()?->id,
                'reviewed_at' => now(),
            ])->save();

            return $this->responseSuccess($domainRequest->fresh(), 'Domain request rejected.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
