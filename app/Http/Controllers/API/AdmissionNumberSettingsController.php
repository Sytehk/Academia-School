<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdmissionNumberSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Authentication\Models\Institute;

/**
 * Per-institute admission-number settings: manual (admin types the number,
 * unique per institute) or auto (institute-wide sequence, 00001, 00002, ...,
 * with an optional prefix). See AdmissionNumberService for generation.
 */
class AdmissionNumberSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $institute = Institute::findOrFail(get_institute_id());

        return $this->responseSuccess([
            'mode' => $institute->admission_no_mode,
            'prefix' => $institute->admission_no_prefix,
            'next_number' => AdmissionNumberSequence::where('institute_id', $institute->id)->value('next_number') ?? 1,
        ], 'Admission number settings fetched.');
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['manual', 'auto'])],
            'prefix' => 'nullable|string|max:20|regex:/^[A-Za-z0-9\-\/]*$/',
        ]);

        $institute = Institute::findOrFail(get_institute_id());
        $institute->update([
            'admission_no_mode' => $data['mode'],
            'admission_no_prefix' => $data['prefix'] ?? null,
        ]);

        return $this->responseSuccess([
            'mode' => $institute->admission_no_mode,
            'prefix' => $institute->admission_no_prefix,
        ], 'Admission number settings saved.');
    }
}
