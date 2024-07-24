<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Signature;
use Modules\Examination\Models\ResultCardSetting;

class ResultCardSettingController extends Controller
{
    public function edit(): Factory|View
    {
        abort_unless(auth()->user()?->can('exam_module_exam_result') || auth()->user()?->can('exam_module_exam_start_up'), 403);

        $row = ResultCardSetting::query()->first();
        $settings = $row ? $row->mergedSettings() : ResultCardSetting::defaults();

        return view('examination::results.settings', [
            'row' => $row,
            'settings' => $settings,
            'card_settings' => $settings,
            'signatures' => Signature::orderBy('title')->get(['id', 'title', 'place_at']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('exam_module_exam_result') || auth()->user()?->can('exam_module_exam_start_up'), 403);

        $booleanKeys = [
            'show_photo', 'show_position', 'show_gpa', 'show_percentage', 'show_failed_subjects',
            'show_attendance', 'show_remarks', 'show_cognitive', 'show_affective', 'show_psychomotor',
            'show_parent_signature', 'show_teacher_signature', 'show_principal_signature',
        ];

        $settings = ResultCardSetting::defaults();
        foreach ($booleanKeys as $key) {
            $settings[$key] = $request->boolean($key);
        }
        $settings['rank_failed_students'] = $request->input('rank_failed_students', 'with_others');
        $settings['template'] = in_array($request->input('template'), ResultCardSetting::TEMPLATES, true)
            ? $request->input('template')
            : 'standard';
        $settings['watermark_opacity'] = min(1, max(0, (float) $request->input('watermark_opacity', 0.08)));
        $settings['primary_color'] = $request->input('primary_color', '#1a365d');
        $settings['accent_color'] = $request->input('accent_color', '#c9a227');
        $settings['show_watermark'] = $request->boolean('show_watermark');
        $settings['teacher_signature_id'] = $request->input('teacher_signature_id') ?: null;
        $settings['principal_signature_id'] = $request->input('principal_signature_id') ?: null;
        $settings['signature_labels'] = [
            'class_teacher' => $request->input('signature_class_teacher', 'Class Teacher'),
            'principal' => $request->input('signature_principal', 'Head Teacher / Principal'),
            'parent' => $request->input('signature_parent', 'Parent / Guardian'),
        ];

        ResultCardSetting::updateOrCreate(
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => get_option('academic_year') ?: null,
            ],
            [
                'result_title' => $request->input('result_title'),
                'settings' => $settings,
            ]
        );

        return back()->with('success', _lang('Result card settings saved.'));
    }
}
