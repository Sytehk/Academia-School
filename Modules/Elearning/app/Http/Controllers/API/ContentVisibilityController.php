<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Elearning\Models\ContentVisibility;

class ContentVisibilityController extends Controller
{
    public function bulkToggleContentVisibility(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'chapter_id' => 'required|exists:chapters,id',
            'content_id' => 'required|integer',
            // 'academic_class_id' => 'required|exists:academic_classes,id',
            // 'section_permissions' => 'required|array|min:1',
            // 'section_permissions.*.academic_section_id' => 'required|exists:academic_sections,id',
            // 'section_permissions.*.is_enabled' => 'required|boolean',
        ]);

        $responses = [];

        foreach ($request->section_permissions as $permission) {
            $record = ContentVisibility::updateOrCreate([
                'course_id' => $request->course_id,
                'chapter_id' => $request->chapter_id,
                'content_id' => $request->content_id,
                // 'academic_class_id' => $request->academic_class_id,
                // 'academic_section_id' => $permission['academic_section_id'],
            ], [
                'is_enabled' => $permission['is_enabled'],
                'institute_id' => get_institute_id(),
                'created_by' => getUserId(),
            ]);

            $responses[] = [
                // 'academic_section_id' => $permission['academic_section_id'],
                'is_enabled' => $record->is_enabled,
            ];
        }

        return $this->responseSuccess([
            'course_id' => $request->course_id,
            'chapter_id' => $request->chapter_id,
            'content_id' => $request->content_id,
            // 'academic_class_id' => $request->academic_class_id,
            'results' => $responses,
        ], 'Content visibility updated successfully for multiple sections.');
    }
}
