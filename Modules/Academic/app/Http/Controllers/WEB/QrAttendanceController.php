<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\QrAttendanceService;
use Illuminate\Http\Request;

/**
 * Admin/staff QR scanner. Shares the SAME check-in/check-out state machine as
 * the Teacher QR flow (App\Services\QrAttendanceService) — no independent
 * exists()/create() attendance logic here anymore, so a student scanned once
 * via Admin and once via Teacher (in either order) still lands on one
 * consistent row. Admin passes no expected class/section, preserving its
 * original unrestricted-scan behavior (see QrAttendanceService::scan()'s
 * $expectedClassId/$expectedSectionId, both left null here).
 */
class QrAttendanceController extends Controller
{
    public function __construct(private readonly QrAttendanceService $qrAttendance) {}

    public function index()
    {
        return view('academic::qrattendance.scanner');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required',
        ]);

        $result = $this->qrAttendance->scan($request->qr_code);

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
     * — same shape as the Teacher QR controllers, so Admin/Teacher responses
     * are consistent for the same underlying state.
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
