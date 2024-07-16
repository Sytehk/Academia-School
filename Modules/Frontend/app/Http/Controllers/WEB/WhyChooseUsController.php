<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\WhyChooseUs\WhyChooseUsStoreRequest;
use Modules\Frontend\Http\Requests\WhyChooseUs\WhyChooseUsUpdateRequest;
use Modules\Frontend\Repositories\WhyChooseUsRepository;

class WhyChooseUsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private WhyChooseUsRepository $whyChooseUs
    ) {}

    /**
     * Why Choose Us List Page
     */
    public function index()
    {
        try {

            $whyChooseUs = $this->whyChooseUs->getAll(request()->all());

            return view('frontend::why_choose_us.index', compact('whyChooseUs'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::why_choose_us.create');
    }

    /**
     * Store Why Choose Us
     */
    public function store(WhyChooseUsStoreRequest $request)
    {
        try {

            $this->whyChooseUs->create($request->all());

            return redirect()
                ->route('why-choose-us.index')
                ->with('success', 'Why Choose Us created successfully');
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

            $whyChoose = $this->whyChooseUs->getById($id);

            return view('frontend::why_choose_us.edit', compact('whyChoose', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update Why Choose Us
     */
    public function update(WhyChooseUsUpdateRequest $request, int $id)
    {
        try {

            $this->whyChooseUs->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('why-choose-us.index')
                ->with('success', 'Why Choose Us updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Why Choose Us
     */
    public function destroy(int $id)
    {
        try {

            $whyChoose = $this->whyChooseUs->getById($id);

            if (! $whyChoose) {

                return back()->with('error', 'Why Choose Us Not Found');
            }

            $this->whyChooseUs->delete($id);

            return redirect()
                ->route('why-choose-us.index')
                ->with('success', 'Why Choose Us deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
