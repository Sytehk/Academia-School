<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Authentication\Http\Requests\Plan\PlanStoreRequest;
use Modules\Authentication\Http\Requests\Plan\PlanUpdateRequest;
use Modules\Authentication\Repositories\PlanRepository;

class PlanController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private PlanRepository $plan) {}

    public function index(): View
    {
        $plans = $this->plan->getAll(request()->all());

        return view('authentication::plan.index', compact('plans'));
    }

    public function create(): View
    {
        return view('authentication::plan.create');
    }

    public function store(PlanStoreRequest $request): RedirectResponse
    {
        try {
            $this->plan->create($request->validated());

            return redirect()
                ->route('plans.index')
                ->with('success', 'Plan has been created successfully.');
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function show(int $id): View
    {
        $plan = $this->plan->getById($id);

        return view('authentication::plan.show', compact('plan'));
    }

    public function edit(int $id): View
    {
        $plan = $this->plan->getById($id);

        return view('authentication::plan.edit', compact('plan'));
    }

    public function update(
        PlanUpdateRequest $request,
        int $id
    ): RedirectResponse {
        try {
            $this->plan->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('plans.index')
                ->with('success', 'Plan has been updated successfully.');
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $plan = $this->plan->getById($id);

            if (! $plan) {
                return back()->with('error', 'Plan Not Found');
            }

            $this->plan->delete($id);

            return redirect()
                ->route('plans.index')
                ->with('success', 'Plan has been deleted successfully.');
        } catch (Exception $exception) {
            return back()
                ->with('error', $exception->getMessage());
        }
    }
}
