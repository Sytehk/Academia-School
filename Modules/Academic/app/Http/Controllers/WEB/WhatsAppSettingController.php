<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Modules\Academic\Http\Requests\WhatsAppSetting\WhatsAppSettingStoreRequest;
use Modules\Academic\Http\Requests\WhatsAppSetting\WhatsAppSettingUpdateRequest;
use Modules\Academic\Repositories\WhatsAppSettingRepository;
use RealRashid\SweetAlert\Facades\Alert;

class WhatsAppSettingController extends Controller
{
    public function __construct(
        private WhatsAppSettingRepository $repo
    ) {}

    public function index()
    {
        $whatsAppSettings = $this->repo->getAll(
            request()->all()
        );

        return view('academic::whats_app_settings.index', compact('whatsAppSettings'));
    }

    public function create()
    {
        return view(
            'academic::whats_app_settings.create'
        );
    }

    public function store(
        WhatsAppSettingStoreRequest $request
    ) {
        try {

            $this->repo->create(
                $request->validated()
            );

            Alert::success(
                'Success',
                'WhatsApp setting created successfully'
            );

            return redirect()
                ->route('whats-app-settings.index');
        } catch (Exception $e) {

            Alert::error(
                'Error',
                $e->getMessage()
            );

            return back()
                ->withInput();
        }
    }

    public function edit($id)
    {
        $setting = $this->repo->getById($id);

        return view(
            'academic::whats_app_settings.edit',
            compact('setting')
        );
    }

    public function update(
        WhatsAppSettingUpdateRequest $request,
        $id
    ) {

        try {

            $this->repo->update(
                $id,
                $request->validated()
            );

            Alert::success(
                'Success',
                'WhatsApp setting updated successfully'
            );

            return redirect()
                ->route('whats-app-settings.index');
        } catch (Exception $e) {

            Alert::error(
                'Error',
                $e->getMessage()
            );

            return back()
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $this->repo->delete($id);

            Alert::success(
                'Success',
                'WhatsApp setting deleted successfully'
            );

            return redirect()
                ->route('whats-app-settings.index');
        } catch (Exception $e) {

            Alert::error(
                'Error',
                $e->getMessage()
            );

            return back();
        }
    }
}
