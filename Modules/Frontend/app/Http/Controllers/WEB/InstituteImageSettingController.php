<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Modules\Authentication\Http\Requests\InstituteImageSetting\InstituteImageSettingStoreRequest;
use Modules\Authentication\Http\Requests\InstituteImageSetting\InstituteImageSettingUpdateRequest;
use Modules\Authentication\Repositories\InstituteImageSettingRepository;

class InstituteImageSettingController extends Controller
{
    public function __construct(
        private InstituteImageSettingRepository $repo
    ) {}

    /**
     * List Page
     */
    public function index()
    {
        try {

            $instituteImages = $this->repo->getAll(request()->all());

            return view(
                'frontend::institute_image_settings.index',
                compact('instituteImages')
            );
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::institute_image_settings.create');
    }

    /**
     * Store Data
     */
    public function store(InstituteImageSettingStoreRequest $request)
    {
        try {

            $this->repo->create($request->all());

            return redirect()
                ->route('institute-image-settings.index')
                ->with('success', 'Institute Image Setting created successfully');
        } catch (Exception $e) {

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit Page
     */
    public function edit(int $id)
    {
        try {

            $instituteImage = $this->repo->getById($id);

            return view(
                'frontend::institute_image_settings.edit',
                compact('instituteImage', 'id')
            );
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update Data
     */
    public function update(
        InstituteImageSettingUpdateRequest $request,
        int $id
    ) {
        try {

            $this->repo->update($id, $request->all());

            return redirect()
                ->route('institute-image-settings.index')
                ->with('success', 'Institute Image Setting updated successfully');
        } catch (Exception $e) {

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Data
     */
    public function destroy(int $id)
    {
        try {

            $instituteImage = $this->repo->getById($id);

            if (! $instituteImage) {

                return back()->with('error', 'Institute Image Setting not found');
            }

            $this->repo->delete($id);

            return redirect()
                ->route('institute-image-settings.index')
                ->with('success', 'Institute Image Setting deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
