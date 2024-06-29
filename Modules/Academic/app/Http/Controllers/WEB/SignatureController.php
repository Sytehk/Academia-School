<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Academic\Http\Requests\SignatureCreateRequest;
use Modules\Academic\Services\SignatureService;
use RealRashid\SweetAlert\Facades\Alert;

class SignatureController extends Controller
{
    public function __construct(private readonly SignatureService $signatureService) {}

    public function index(): View|Factory
    {
        return view('academic::signatures.index', [
            'signatures' => $this->signatureService->getSignatures(),
        ]);
    }

    public function store(SignatureCreateRequest $request): Redirector|RedirectResponse
    {
        $signature = $this->signatureService->createSignature($request->validated());

        if (! $signature) {
            Alert::error('Error', _lang('Something went wrong !'));

            return redirect('signatures');
        }

        Alert::toast('Signature application has been submitted.', 'success');

        return redirect('signatures');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $signature = $this->signatureService->findSignatureById($id);

        if (! $signature) {
            Alert::error('Error', _lang('Something went wrong !'));

            return redirect('signatures');
        }

        return view('academic::signatures.edit', [
            'signature' => $signature,
        ]);
    }

    public function update(SignatureCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $signature = $this->signatureService->findSignatureById($id);

        if (! $signature) {
            Alert::error('Error', _lang('Something went wrong !'));

            return redirect('signatures');
        }

        // Was $request->all() — let a client add hidden institute_id/branch_id/user_id
        // fields to the POST body and reassign the signature to another tenant/user.
        $this->signatureService->updateSignature($request->validated(), $id);
        Alert::toast('Signature application has been updated.', 'success');

        return redirect('signatures');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $signature = $this->signatureService->deleteSignatureById($id);

        if (! $signature) {
            Alert::error('Error', _lang('Something went wrong !'));

            return redirect('signatures');
        }

        Alert::toast('Signature application has been deleted.', 'success');

        return redirect('signatures');
    }
}
