<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\MobileAppSection\MobileAppSectionStoreRequest;
use Modules\Frontend\Http\Requests\MobileAppSection\MobileAppSectionUpdateRequest;
use Modules\Frontend\Repositories\MobileAppSectionRepository;

class MobileAppSectionController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private MobileAppSectionRepository $mobileAppSection
    ) {}

    /**
     * List
     */
    public function index()
    {
        try {
            $mobileAppSections = $this->mobileAppSection->getAll(request()->all());

            return view('frontend::mobile_app_sections.index', compact('mobileAppSections'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create
     */
    public function create()
    {
        return view('frontend::mobile_app_sections.create');
    }

    /**
     * Store
     */
    public function store(MobileAppSectionStoreRequest $request)
    {
        try {
            $this->mobileAppSection->create($request->all());

            return redirect()
                ->route('mobile-app-sections.index')
                ->with('success', 'Mobile App Section created successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit
     */
    public function edit(int $id)
    {
        try {
            $mobileAppSection = $this->mobileAppSection->getById($id);

            return view('frontend::mobile_app_sections.edit', compact('mobileAppSection', 'id'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update
     */
    public function update(MobileAppSectionUpdateRequest $request, int $id)
    {
        try {
            $this->mobileAppSection->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('mobile-app-sections.index')
                ->with('success', 'Mobile App Section updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete
     */
    public function destroy(int $id)
    {
        try {
            $item = $this->mobileAppSection->getById($id);

            if (! $item) {
                return back()->with('error', 'Not Found');
            }

            if (! empty($item->image)) {
                fileRemover('mobile-app-sections/', $item->image);
            }

            $this->mobileAppSection->delete($id);

            return redirect()
                ->route('mobile-app-sections.index')
                ->with('success', 'Deleted successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
