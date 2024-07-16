<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\RedirectResponse;
use Modules\Frontend\Http\Requests\Policy\PolicyUpdateRequest;
use Modules\Frontend\Models\Policy;
use Modules\Frontend\Repositories\PolicyRepository;

class PolicyController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private PolicyRepository $policy
    ) {}

    public function index()
    {
        try {
            $this->ensureDefaultPolicies();

            $policies = $this->policy->getAll(request()->all());

            return view('frontend::policies.index', compact('policies'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('policies.index')
            ->with('error', 'Default policy pages are assigned automatically and can only be edited.');
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('policies.index')
            ->with('error', 'Default policy pages are assigned automatically and can only be edited.');
    }

    public function edit(int $id)
    {
        try {
            $policy = $this->policy->getById($id);

            return view('frontend::policies.edit', compact('policy', 'id'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(PolicyUpdateRequest $request, int $id)
    {
        try {
            $payload = $this->getUpdateRequest($request);
            unset($payload['type']);

            $this->policy->update($id, $payload);

            return redirect()
                ->route('policies.index')
                ->with('success', 'Policy updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        return redirect()
            ->route('policies.index')
            ->with('error', 'Default policy pages cannot be deleted.');
    }

    private function ensureDefaultPolicies(): void
    {
        $user = auth()->user();

        if (! $user?->institute_id || ! $user?->branch_id) {
            return;
        }

        Policy::ensureDefaultsFor((int) $user->institute_id, (int) $user->branch_id);
    }
}
