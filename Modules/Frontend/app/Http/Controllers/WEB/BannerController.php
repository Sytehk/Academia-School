<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\Banner\BannerStoreRequest;
use Modules\Frontend\Http\Requests\Banner\BannerUpdateRequest;
use Modules\Frontend\Repositories\BannerRepository;

class BannerController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private BannerRepository $banner) {}

    /**
     * Banner List Page
     */
    public function index()
    {
        try {
            $banners = $this->banner->getAll(request()->all());

            return view('frontend::banners.index', compact('banners'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::banners.create');
    }

    /**
     * Store Banner
     */
    public function store(BannerStoreRequest $request)
    {
        try {

            $this->banner->create($request->all());

            return redirect()
                ->route('banners.index')
                ->with('success', 'Banner created successfully');
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

            $banner = $this->banner->getById($id);

            return view('frontend::banners.edit', compact('banner', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update Banner
     */
    public function update(BannerUpdateRequest $request, int $id)
    {
        try {

            $this->banner->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('banners.index')
                ->with('success', 'Banner updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Banner
     */
    public function destroy(int $id)
    {
        try {

            $banner = $this->banner->getById($id);

            if (! $banner) {

                return back()->with('error', 'Banner Not Found');
            }

            if ($banner->image) {

                fileRemover('banners/', $banner->image);
            }

            $this->banner->delete($id);

            return redirect()
                ->route('banners.index')
                ->with('success', 'Banner deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
