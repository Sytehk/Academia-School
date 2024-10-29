<?php

namespace Modules\LayoutCert\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Signature;
use Modules\LayoutCert\Models\CertificateTemplate;
use RealRashid\SweetAlert\Facades\Alert;

class CertificateTemplateController extends Controller
{
    public function index()
    {
        $templates = CertificateTemplate::orderBy('type')->orderByDesc('is_active')->get();
        $types = LayoutCertController::TYPES;

        return view('layoutcert::certificate_templates.index', compact('templates', 'types'));
    }

    public function create()
    {
        $types = LayoutCertController::TYPES;
        $signatures = Signature::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        return view('layoutcert::certificate_templates.form', [
            'template' => CertificateTemplate::defaultFor(array_key_first($types)),
            'types' => $types,
            'signatures' => $signatures,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        if ($request->hasFile('background_image')) {
            $data['background_image'] = fileUploader('certificates/', 'png', $request->file('background_image'));
        }

        CertificateTemplate::create($data);

        Alert::success('Success', _lang('Certificate template created successfully.'));

        return redirect()->route('certificate-templates.index');
    }

    public function edit(int $id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $types = LayoutCertController::TYPES;
        $signatures = Signature::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        return view('layoutcert::certificate_templates.form', compact('template', 'types', 'signatures'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $template = CertificateTemplate::findOrFail($id);
        $data = $this->validated($request);

        if ($request->hasFile('background_image')) {
            $data['background_image'] = fileUploader('certificates/', 'png', $request->file('background_image'), $template->background_image);
        }

        $template->update($data);

        Alert::success('Success', _lang('Certificate template updated successfully.'));

        return redirect()->route('certificate-templates.index');
    }

    public function duplicate(int $id): RedirectResponse
    {
        $template = CertificateTemplate::findOrFail($id);
        $copy = $template->replicate();
        $copy->name = $template->name.' (Copy)';
        $copy->is_active = false;
        $copy->save();

        Alert::success('Success', _lang('Certificate template duplicated successfully.'));

        return redirect()->route('certificate-templates.index');
    }

    public function activate(int $id): RedirectResponse
    {
        $template = CertificateTemplate::findOrFail($id);

        // Only one active template per type — deactivate any siblings so forType() lookups
        // stay unambiguous.
        CertificateTemplate::where('type', $template->type)
            ->where('id', '!=', $template->id)
            ->update(['is_active' => false]);

        $template->update(['is_active' => true]);

        Alert::success('Success', _lang('Certificate template activated.'));

        return redirect()->route('certificate-templates.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        CertificateTemplate::findOrFail($id)->delete();

        Alert::success('Success', _lang('Certificate template deleted successfully.'));

        return redirect()->route('certificate-templates.index');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'body' => 'nullable|string|max:20000',
            'type' => 'required|string|in:'.implode(',', array_keys(LayoutCertController::TYPES)),
            'orientation' => 'required|in:portrait,landscape',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'font_family' => 'required|string|max:60',
            'watermark_text' => 'nullable|string|max:100',
            'signatures' => 'nullable|array',
            'signatures.*.label' => 'nullable|string|max:60',
            'signatures.*.signature_id' => 'nullable|integer',
            'background_image' => 'nullable|image|max:4096',
        ]);

        // Checkboxes are absent from the request when unchecked, not sent as "false" — read
        // them explicitly so unchecking one actually persists as off, instead of the field
        // being silently skipped and the previous value left in place.
        $data['show_watermark'] = $request->boolean('show_watermark');
        $data['show_qr'] = $request->boolean('show_qr');
        $data['show_logo'] = $request->boolean('show_logo');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
