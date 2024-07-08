<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Authentication\Http\Requests\SAASSubscription\SAASContactStoreRequest;
use Modules\Authentication\Http\Requests\SAASSubscription\SAASSubscriptionStoreRequest;
use Modules\Authentication\Http\Requests\SAASSubscription\SAASSubscriptionUpdateRequest;
use Modules\Authentication\Models\SAASContact;
use Modules\Authentication\Models\SAASSubscription;
use Modules\Authentication\Repositories\SAASSubscriptionRepository;

class SAASSubscriptionController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private SAASSubscriptionRepository $sAASSubscriptionRepository) {}

    public function getSAASSubscriptions(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);

        $getSAASSubscriptions = SAASSubscription::paginate($perPage);

        return $this->responseSuccess($getSAASSubscriptions, 'Saas Subscriptions fetched successfully.');
    }

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->sAASSubscriptionRepository->getAll(request()->all()),
                'SAAS Subscription has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(SAASSubscriptionStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->sAASSubscriptionRepository->create($request->all()),
                'SAAS Subscription has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->sAASSubscriptionRepository->getById($id),
                'SAAS Subscription has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(SAASSubscriptionUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->sAASSubscriptionRepository->update($id, $this->getUpdateRequest($request)),
                'SAAS Subscription has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->sAASSubscriptionRepository->delete($id),
                'SAAS Subscription has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function publicStore(SAASSubscriptionStoreRequest $request): JsonResponse
    {
        $subscriptions = SAASSubscription::create([
            'email' => $request->email,
        ]);

        return $this->responseSuccess(
            $subscriptions,
            'SAAS Subscription has been created successfully.'
        );
    }

    public function saasContacts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 10);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'id');
            $sortOrder = strtolower($request->input('sort_order', 'desc'));

            // Allowed sortable columns
            $allowedSorts = [
                'id',
                'name',
                'email',
                'phone',
                'subject',
                'created_at',
            ];

            if (! in_array($sortBy, $allowedSorts)) {
                $sortBy = 'id';
            }

            if (! in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $contacts = SAASContact::query()
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%");
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);

            return $this->responseSuccess(
                $contacts,
                'Contacts fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function saasContact(SAASContactStoreRequest $request): JsonResponse
    {
        try {
            // Store Contact
            $contact = SAASContact::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
            ]);

            return $this->responseSuccess($contact, 'Contact form submitted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
