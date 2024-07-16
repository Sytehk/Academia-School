<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\AboutUs\AboutUsStoreRequest;
use Modules\Frontend\Http\Requests\AboutUs\AboutUsUpdateRequest;
use Modules\Frontend\Repositories\AboutUsRepository;

class AboutUsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private AboutUsRepository $aboutUs
    ) {}

    /**
     * AboutUs List Page
     */
    public function index()
    {
        try {

            $aboutUs = $this->aboutUs->getAll(request()->all());

            return view('frontend::about_us.index', compact('aboutUs'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::about_us.create');
    }

    /**
     * Store AboutUs
     */
    public function store(AboutUsStoreRequest $request)
    {
        try {

            $this->aboutUs->create($request->all());

            return redirect()
                ->route('about-us.index')
                ->with('success', 'About Us created successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit Page
     */
    public function edit(int $id)
    {
        try {

            $aboutUs = $this->aboutUs->getById($id);

            return view('frontend::about_us.edit', compact('aboutUs', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update AboutUs
     */
    public function update(AboutUsUpdateRequest $request, int $id)
    {

        // try {

        $this->aboutUs->update(
            $id,
            $this->getUpdateRequest($request)
        );

        return redirect()
            ->route('about-us.index')
            ->with('success', 'About Us updated successfully');
        // } catch (Exception $e) {

        //     return back()->with('error', $e->getMessage());
        // }
    }

    /**
     * Delete AboutUs
     */
    public function destroy(int $id)
    {
        try {

            $aboutUs = $this->aboutUs->getById($id);

            if (! $aboutUs) {

                return back()->with('error', 'About Us Not Found');
            }

            // Remove image if exists
            if (! empty($aboutUs->image)) {

                fileRemover('about-us/', $aboutUs->image);
            }

            $this->aboutUs->delete($id);

            return redirect()
                ->route('about-us.index')
                ->with('success', 'About Us deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
