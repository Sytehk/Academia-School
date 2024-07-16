<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\AcademicImage\AcademicImageStoreRequest;
use Modules\Frontend\Http\Requests\AcademicImage\AcademicImageUpdateRequest;
use Modules\Frontend\Repositories\AcademicImageRepository;

class AcademicImageController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private AcademicImageRepository $academicImage
    ) {}

    /**
     * Academic Image List Page
     */
    public function index()
    {
        try {

            $academicImages = $this->academicImage->getAll(request()->all());

            return view('frontend::gallery_images.index', compact('academicImages'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::gallery_images.create');
    }

    /**
     * Store Academic Image
     */
    public function store(AcademicImageStoreRequest $request)
    {
        try {

            $this->academicImage->create($request->all());

            return redirect()
                ->route('gallery-images.index')
                ->with('success', 'Academic Image created successfully');
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
            $academicImage = $this->academicImage->getById($id);

            return view('frontend::gallery_images.edit', compact('academicImage', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update Academic Image
     */
    public function update(AcademicImageUpdateRequest $request, int $id)
    {
        try {
            $this->academicImage->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('gallery-images.index')
                ->with('success', 'Academic Image updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Academic Image
     */
    public function destroy(int $id)
    {
        try {

            $academicImage = $this->academicImage->getById($id);

            if (! $academicImage) {

                return back()->with('error', 'Academic Image Not Found');
            }

            // Remove image if exists
            if (! empty($academicImage->image)) {

                fileRemover('academic-images/', $academicImage->image);
            }

            $this->academicImage->delete($id);

            return redirect()
                ->route('gallery-images.index')
                ->with('success', 'Academic Image deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
