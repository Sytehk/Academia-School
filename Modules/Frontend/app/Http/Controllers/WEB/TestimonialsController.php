<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\Testimonial\TestimonialStoreRequest;
use Modules\Frontend\Http\Requests\Testimonial\TestimonialUpdateRequest;
use Modules\Frontend\Repositories\TestimonialRepository;

class TestimonialsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private TestimonialRepository $testimonial
    ) {}

    /**
     * Testimonial List Page
     */
    public function index()
    {
        try {

            $testimonials = $this->testimonial->getAll(request()->all());

            return view('frontend::testimonials.index', compact('testimonials'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::testimonials.create');
    }

    /**
     * Store Testimonial
     */
    public function store(TestimonialStoreRequest $request)
    {
        try {

            $this->testimonial->create($request->all());

            return redirect()
                ->route('testimonials.index')
                ->with('success', 'Testimonial created successfully');
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

            $testimonial = $this->testimonial->getById($id);

            return view('frontend::testimonials.edit', compact('testimonial', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update Testimonial
     */
    public function update(TestimonialUpdateRequest $request, int $id)
    {
        try {

            $this->testimonial->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('testimonials.index')
                ->with('success', 'Testimonial updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Testimonial
     */
    public function destroy(int $id)
    {
        try {

            $testimonial = $this->testimonial->getById($id);

            if (! $testimonial) {

                return back()->with('error', 'Testimonial Not Found');
            }

            // delete image if exists
            if (! empty($testimonial->image)) {

                fileRemover('testimonials/', $testimonial->image);
            }

            $this->testimonial->delete($id);

            return redirect()
                ->route('testimonials.index')
                ->with('success', 'Testimonial deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
