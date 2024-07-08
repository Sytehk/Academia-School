<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\SubscriptionItem;
use Modules\Authentication\Models\SubscriptionUpgradeRequest;
use Modules\Authentication\Services\InstituteProvisioningService;
use Modules\Frontend\Models\Onboarding;
use Throwable;

class OnboardingsController extends Controller
{
    use Authenticatable;

    private const DEFAULT_PER_PAGE = 10;

    /** Upper bound for caller-supplied page sizes. */
    private const MAX_PER_PAGE = 100;

    /**
     * Sign-up requests still awaiting provisioning.
     *
     * Uses the model's pending scope, which checks both approved_at and status.
     * The list itself was never the bug — the public flow provisioned tenants
     * without ever stamping the record, so completed sign-ups stayed pending
     * forever. See {@see InstituteProvisioningService::markApproved()}.
     *
     * `per_page` is clamped: it is caller-supplied and previously unbounded, so
     * `?per_page=1000000` would try to hydrate the entire table.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        $onboardings = Onboarding::pending()
            ->orderByDesc('id')
            ->paginate($perPage);

        return $this->responseSuccess($onboardings, 'Onboardings fetch successfully');
    }

    /**
     * Approve a pending sign-up and provision its tenant.
     *
     * Provisioning is delegated to {@see InstituteProvisioningService}, shared with
     * the public sign-up flow. The previous inline copy had drifted badly: it
     * hardcoded `institute_id => 1` on the subscription (so approving any institute
     * subscribed institute 1 and left the new tenant with none), referenced an
     * undefined `$plan` variable, and re-hashed the already-hashed password.
     */
    public function approve(int $onboardingId, InstituteProvisioningService $provisioner): JsonResponse
    {
        $onboarding = Onboarding::find($onboardingId);

        if (! $onboarding) {
            return $this->responseError([], 'No Data Found!', 404);
        }

        // Guards against double provisioning, which would otherwise fail on the
        // institute's unique email and leave a confusing error.
        if ($onboarding->isApproved()) {
            return $this->responseError([], 'This onboarding request has already been approved.', 409);
        }

        try {
            $result = $provisioner->provisionFromOnboarding($onboarding, $this->getCurrentUserId());
        } catch (Throwable $e) {
            Log::error('Institute approval failed', [
                'onboarding_id' => $onboardingId,
                'error' => $e->getMessage(),
            ]);

            // The raw exception message used to be returned to the client, which
            // leaks schema and connection details on failure.
            return $this->responseError([], 'An error occurred while approving this institute.', 500);
        }

        return $this->responseSuccess([
            'institute' => $result['institute'],
            'user' => $result['user'],
        ], 'Institute approved and created with credentials successfully.');
    }

    public function upgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'plan_id' => 'required|exists:plans,id',
            'extra_days' => 'nullable|integer|min:0',
            // A manual admin edit (date/status only, no payment taken) leaves these
            // out entirely — only a real upgrade needs payment info recorded.
            'payment_method' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            // Optional manual overrides for the computed start/end date, so an
            // admin can backdate, extend, or shorten a subscription directly.
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,inactive,expired',
        ]);

        $instituteId = $validated['institute_id'];
        $plan = Plan::findOrFail($validated['plan_id']);
        $extraDays = $validated['extra_days'] ?? 0;
        $amountPaid = (float) ($validated['amount_paid'] ?? 0);

        $startDate = isset($validated['start_date']) ? Carbon::parse($validated['start_date']) : Carbon::now();
        // A plan with no duration_days is a lifetime/unlimited plan — give it a
        // far-future end date rather than expiring it the same day. An explicit
        // end_date always wins over the computed one.
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])
            : $startDate->copy()->addDays(((int) $plan->duration_days ?: 36500) + (int) $extraDays);
        $status = $validated['status'] ?? 'active';

        // Fetch or create subscription for the institute
        $subscription = Subscription::where('institute_id', $instituteId)->first();
        if ($subscription) {
            // Update existing subscription
            $subscription->update([
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'invoice_details' => json_encode([
                    'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
                    'issued_at' => now()->toDateString(),
                    'notes' => $validated['notes'] ?? 'Upgraded Package',
                    'amount' => $amountPaid,
                ]),
            ]);
        } else {
            // Fallback (shouldn’t happen unless subscription was deleted)
            $subscription = Subscription::create([
                'institute_id' => $instituteId,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'invoice_details' => json_encode([
                    'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
                    'issued_at' => now()->toDateString(),
                    'notes' => $validated['notes'] ?? 'Initial Subscription',
                    'amount' => $amountPaid,
                ]),
            ]);
        }

        // Only record a payment/subscription item when an actual payment was taken.
        if ($amountPaid > 0) {
            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'reference_no' => 'PAY-'.strtoupper(Str::random(8)),
                'amount_paid' => $amountPaid,
                'payment_method' => $validated['payment_method'] ?? 'manual',
            ]);
        }

        // Respond with a success message
        return $this->responseSuccess([], 'Subscription updated successfully.');
    }

    public function requestUpgradeList(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = SubscriptionUpgradeRequest::where('status', 'pending')
            ->with(['institute', 'plan'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('institute', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc');

        $upgradeRequests = $query->paginate($perPage);

        return $this->responseSuccess($upgradeRequests, 'Upgrade requests fetched successfully.');
    }

    public function requestUpgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'notes' => 'nullable|string',
        ]);

        $instituteId = $this->getCurrentInstituteId(); // assume this gets the current user's institute ID
        $plan = Plan::findOrFail($validated['plan_id']);

        // Check for existing pending upgrade request for the same institute & plan
        $existingPending = SubscriptionUpgradeRequest::where('institute_id', $instituteId)
            // ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            // Update the existing pending request
            $existingPending->update([
                'extra_days' => (int) $plan->duration_days,
                'payment_method' => 'online', // or from config
                'amount_paid' => $plan->price,
                'notes' => $validated['notes'] ?? null,
            ]);

            $upgradeRequest = $existingPending;
        } else {
            // Create a new upgrade request
            $upgradeRequest = SubscriptionUpgradeRequest::create([
                'institute_id' => $instituteId,
                'plan_id' => $plan->id,
                'extra_days' => (int) $plan->duration_days,
                'payment_method' => 'online',
                'amount_paid' => $plan->price,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);
        }

        return $this->responseSuccess($upgradeRequest, 'Upgrade request submitted successfully.');
    }

    public function approveRequest(int $requestId): JsonResponse
    {
        DB::beginTransaction();
        try {
            $subscriptionRequest = SubscriptionUpgradeRequest::where('id', $requestId)->where('status', 'pending')->first();

            if (! $subscriptionRequest) {
                return $this->responseError([], 'No pending request found.', 404);
            }

            $plan = Plan::findOrFail($subscriptionRequest->plan_id);
            $extraDays = $subscriptionRequest->extra_days ?? 0;

            $startDate = Carbon::now();
            // A plan with no duration_days is a lifetime/unlimited plan — give it a
            // far-future end date rather than expiring it the same day.
            $endDate = $startDate->copy()->addDays(((int) $plan->duration_days ?: 36500) + (int) $extraDays);

            // Update or create subscription
            $subscription = Subscription::where('institute_id', $subscriptionRequest->institute_id)->first();

            if ($subscription) {
                $subscription->update([
                    'plan_id' => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'invoice_details' => json_encode([
                        'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
                        'issued_at' => now()->toDateString(),
                        'notes' => $subscriptionRequest->notes,
                        'amount' => $subscriptionRequest->amount_paid,
                    ]),
                ]);
            } else {
                $subscription = Subscription::create([
                    'institute_id' => $subscriptionRequest->institute_id,
                    'plan_id' => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'invoice_details' => json_encode([
                        'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
                        'issued_at' => now()->toDateString(),
                        'notes' => $subscriptionRequest->notes,
                        'amount' => $subscriptionRequest->amount_paid,
                    ]),
                ]);
            }

            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'reference_no' => 'PAY-'.strtoupper(Str::random(8)),
                'amount_paid' => $subscriptionRequest->amount_paid,
                'payment_method' => $subscriptionRequest->payment_method,
            ]);

            $subscriptionRequest->update([
                'status' => 'approved',
                'approved_by' => getUserId(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return $this->responseSuccess($subscription, 'Subscription request approved successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Error: '.$e->getMessage());
        }
    }

    public function paymentSubscriptions(Request $request): JsonResponse
    {
        $request->validate([
            'institute_id' => 'required|exists:institutes,id',
            'plan_id' => 'required|exists:plans,id',
            'upgrade_request_id' => 'nullable|exists:subscription_upgrade_requests,id',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'amount_paid' => 'nullable|numeric',
            'extra_days' => 'nullable|integer',
        ]);

        // A non-platform caller may only pay for their own institute, whatever
        // institute_id they put in the body. Only a SAAS Admin can target another.
        $actor = $request->user();
        $instituteId = ($actor && $actor->user_type === 'SAAS Admin')
            ? (int) $request->institute_id
            : $this->getCurrentInstituteId();

        DB::beginTransaction();

        try {
            $plan = Plan::findOrFail((int) $request->plan_id);
            $startDate = Carbon::now();
            $extraDays = $request->input('extra_days', 0);
            // A plan with no duration_days is a lifetime/unlimited plan — give it a
            // far-future end date rather than expiring it the same day.
            $endDate = $startDate->copy()->addDays(((int) $plan->duration_days ?: 36500) + (int) $extraDays);

            $subscription = Subscription::where('institute_id', $instituteId)->first();
            $invoiceData = [
                'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
                'issued_at' => now()->toDateString(),
                'notes' => $request->input('notes', 'Payment for upgrade'),
                'amount' => $request->input('amount_paid', $plan->price),
            ];

            if ($subscription) {
                $subscription->update([
                    'plan_id' => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'invoice_details' => $invoiceData,
                ]);
            } else {
                $subscription = Subscription::create([
                    'institute_id' => $instituteId,
                    'plan_id' => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'invoice_details' => $invoiceData,
                ]);
            }

            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'reference_no' => 'PAY-'.strtoupper(Str::random(8)),
                'amount_paid' => $invoiceData['amount'],
                'payment_method' => $request->input('payment_method', 'online'),
            ]);

            // If upgrading through a pending request, mark it as approved
            if ($request->filled('upgrade_request_id')) {
                $upgradeRequest = SubscriptionUpgradeRequest::where('id', $request->upgrade_request_id)
                    ->where('institute_id', $instituteId)
                    ->where('status', 'pending')
                    ->first();

                if (! $upgradeRequest) {
                    return $this->responseError([], 'No pending upgrade request found.', 404);
                }

                $upgradeRequest->update([
                    'status' => 'approved',
                    'approved_by' => getUserId(),
                    'approved_at' => now(),
                ]);
            }

            DB::commit();

            return $this->responseSuccess($subscription, 'Subscription payment processed successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Error: '.$e->getMessage());
        }
    }
}
