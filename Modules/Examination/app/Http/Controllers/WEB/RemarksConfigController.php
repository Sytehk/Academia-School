<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Examination\Http\Requests\RemarkConfigRequest;
use Modules\Examination\Models\RemarkConfig;
use RealRashid\SweetAlert\Facades\Alert;

class RemarksConfigController extends Controller
{
    public function index(): View|Factory
    {
        return view('examination::remarks_config.index', [
            'remarks_configs' => RemarkConfig::get(),
        ]);
    }

    public function store(RemarkConfigRequest $request): Redirector|RedirectResponse
    {
        $remarkConfig = RemarkConfig::create($request->validated());

        if (! $remarkConfig) {
            Alert::error('Error', _lang('Something went wrong. Remark Config can not be create.'));

            return redirect('remarks-config');
        }

        Alert::toast('Remark Config has been submitted.', 'success');

        return redirect('remarks-config');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $remarkConfig = RemarkConfig::where('id', $id)->first();

        if (! $remarkConfig) {
            Alert::error('Error', _lang('Something went wrong. Remark Config can not be edit.'));

            return redirect('remarks-config');
        }

        return view('examination::remarks_config.edit', [
            'remarkConfig' => $remarkConfig,
        ]);
    }

    public function update(RemarkConfigRequest $request, int $id): Redirector|RedirectResponse
    {
        $remarkConfig = RemarkConfig::where('id', $id)->first();
        $remarkConfig->update($request->validated());

        Alert::toast('Remark Configuration has been updated successfully.', 'success');

        return redirect('remarks-config');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $remarkConfig = RemarkConfig::where('id', $id)->first();

        if (! $remarkConfig) {
            Alert::error('Error', _lang('Something went wrong. Remark Config can not be delete.'));

            return redirect('remarks-config');
        }

        $remarkConfig->delete();

        Alert::toast('Remark Config has been delete.', 'success');

        return redirect('remarks-config');
    }
}
