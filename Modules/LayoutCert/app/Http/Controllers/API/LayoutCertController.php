<?php

namespace Modules\LayoutCert\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LayoutCertController extends Controller
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $type = $request->type;
        $roll = (int) $request->roll;
        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;

        $allowedTypes = [
            'general-certificate',
            'testimonial',
            'attendance-certificate',
            'transfer-certificate',
            'hsc-recommendation',
            'abroad-recommendation',
        ];

        if (! in_array($type, $allowedTypes, true)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid certificate type.',
            ], 400);
        }

        if (! $classId || ! $sectionId || ! $roll) {
            return response()->json([
                'status' => false,
                'message' => 'Class, section, and roll are required.',
            ], 422);
        }

        try {
            $this->academicContext->assertClassSectionGroup($classId, $sectionId);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $studentSession = $this->academicContext
            ->studentSessionQuery($classId, $sectionId)
            ->with(['student', 'session', 'class', 'section'])
            ->where('roll', $roll)
            ->first();

        if (! $studentSession) {
            return response()->json([
                'status' => false,
                'message' => 'No student found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Student data retrieved successfully.',
            'data' => [
                'type' => $type,
                'student_session' => $studentSession,
            ],
        ]);
    }
}
