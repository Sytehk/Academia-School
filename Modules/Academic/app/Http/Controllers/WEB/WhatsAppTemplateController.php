<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Modules\Academic\Http\Requests\WhatsAppTemplate\WhatsAppTemplateStoreRequest;
use Modules\Academic\Http\Requests\WhatsAppTemplate\WhatsAppTemplateUpdateRequest;
use Modules\Academic\Repositories\WhatsAppTemplateRepository;
use RealRashid\SweetAlert\Facades\Alert;

class WhatsAppTemplateController extends Controller
{
    public function __construct(
        private WhatsAppTemplateRepository $repo
    ) {}

    public function index()
    {
        $templates = $this->repo->getAll(
            request()->all()
        );

        return view(
            'academic::whats_app_templates.index',
            compact('templates')
        );
    }

    public function create()
    {
        return view(
            'academic::whats_app_templates.create'
        );
    }

    public function store(
        WhatsAppTemplateStoreRequest $request
    ) {

        try {

            $this->repo->create(
                $request->validated()
            );

            Alert::success(
                'Success',
                'WhatsApp template created successfully'
            );

            return redirect()
                ->route('whats-app-templates.index');
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

        $template = $this->repo->getById($id);

        return view(
            'academic::whats_app_templates.edit',
            compact('template')
        );
    }

    public function update(
        WhatsAppTemplateUpdateRequest $request,
        $id
    ) {

        try {

            $this->repo->update(

                $id,

                $request->validated()

            );

            Alert::success(
                'Success',
                'WhatsApp template updated successfully'
            );

            return redirect()
                ->route('whats-app-templates.index');
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
                'WhatsApp template deleted successfully'
            );

            return back();
        } catch (Exception $e) {

            Alert::error(
                'Error',
                $e->getMessage()
            );

            return back();
        }
    }
}
