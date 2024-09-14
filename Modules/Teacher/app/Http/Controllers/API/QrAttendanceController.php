<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\QrAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Teacher\Http\Controllers\Concerns\ScopesTeacherResources;

class QrAttendanceController extends Controller
{
    use ScopesTeacherResources;

    public function __construct(private readonly QrAttendanceService $qrAttendance) {}

    /** Sections this teacher may take attendance for, to populate a class/section picker. */
    public function sections(): JsonResponse
    {
        return $this->responseSuccess($this->teacherOwnedSections(), 'Sections fetched successfully.');
    }

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'qr_code' => 'required|string',
        ]);

        $sectionId = (int) $request->section_id;

        if (! $this->teacherOwnsSection($sectionId)) {
            return $this->responseError([], 'You are not assigned to this class/section.', 403);
        }

        $result = $this->qrAttendance->scan($request->qr_code, (int) $request->class_id, $sectionId);

        return match ($result['status']) {
            QrAttendanceService::STATUS_CHECKED_IN,
            QrAttendanceService::STATUS_CHECKED_OUT,
            QrAttendanceService::STATUS_ALREADY_CHECKED_OUT => $this->responseSuccess($this->scanResponseData($result), $result['message']),
            QrAttendanceService::STATUS_NOT_FOUND => $this->responseError([], $result['message'], 404),
            QrAttendanceService::STATUS_INACTIVE, QrAttendanceService::STATUS_SECTION_MISMATCH => $this->responseError([], $result['message'], 422),
            default => $this->responseError([], 'Something went wrong.', 500),
        };
    }

    /**
     * {status: check_in|check_out|already_checked_out, check_in_at, check_out_at}
     * — the minimal payload the QR check-in/check-out spec asks for.
     */
    private function scanResponseData(array $result): array
    {
        return [
            'status' => match ($result['status']) {
                QrAttendanceService::STATUS_CHECKED_IN => 'check_in',
                QrAttendanceService::STATUS_CHECKED_OUT => 'check_out',
                QrAttendanceService::STATUS_ALREADY_CHECKED_OUT => 'already_checked_out',
                default => $result['status'],
            },
            'check_in_at' => $result['check_in_at'],
            'check_out_at' => $result['check_out_at'],
        ];
    }
}
