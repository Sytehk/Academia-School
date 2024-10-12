<?php

namespace Modules\SMS\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\SMS\Http\Requests\SmsTemplateRequest;
use Modules\SMS\Services\SmsTemplateService;
use RealRashid\SweetAlert\Facades\Alert;

class SmsTemplateController extends Controller
{
    public function __construct(private readonly SmsTemplateService $smsTemplateService) {}

    public function index()
    {
        return view('sms::sms-template.index', [
            'smsTemplates' => $this->smsTemplateService->getSmsTemplates(),
        ]);
    }

    public function store(SmsTemplateRequest $request): Redirector|RedirectResponse
    {
        $this->smsTemplateService->store($request->validated());

        Alert::toast('Sms Template Created Successfully.', 'success');

        return redirect('sms-template');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $template = $this->smsTemplateService->findById($id);
        if (! $template) {
            Alert::error('Error', _lang('Sms Template Not Found.'));

            return redirect('sms-template');
        }

        return view('sms::sms-template.edit', compact('template'));
    }

    public function update(SmsTemplateRequest $request, int $id): Redirector|RedirectResponse
    {
        $template = $this->smsTemplateService->findById($id);
        if (! $template) {
            Alert::error('Error', _lang('Sms Template Not Found.'));

            return redirect('sms-template');
        }
        $this->smsTemplateService->update($request->validated(), $id);

        Alert::toast('Sms Template Updated Successfully.', 'success');

        return redirect('sms-template');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $template = $this->smsTemplateService->findById($id);
        if (! $template) {
            Alert::error('Error', _lang('Sms Template Not Found.'));

            return redirect('sms-template');
        }
        $this->smsTemplateService->delete($id);

        Alert::toast('Sms Template Deleted Successfully.', 'success');

        return redirect('sms-template');
    }
}
