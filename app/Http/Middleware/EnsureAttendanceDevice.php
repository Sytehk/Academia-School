<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates a biometric / QR attendance device and pins the tenant it may
 * write to.
 *
 * POST /api/student-device-attendance previously sat outside every auth group
 * with no middleware and no rate limit, and resolved students by looking up
 * `student_sessions.qr_code` across ALL institutes. Since module 4 prints that
 * same QR code onto the student ID card, anyone holding a card could forge
 * attendance and trigger WhatsApp messages to that student's parent.
 *
 * The device now presents its institute's `attendance_device_token` secret, and
 * the resolved institute/branch is attached to the request so the controller can
 * scope its lookup instead of trusting the payload.
 */
class EnsureAttendanceDevice
{
    use ResponseTrait;

    /** Request attribute holding the resolved tenant, read by the controller. */
    public const CONTEXT = 'attendance_device_context';

    public const SETTING_NAME = 'attendance_device_token';

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->presentedToken($request);

        if ($token !== null && $token !== '') {
            $context = $this->resolveTenant($token);

            if ($context !== null) {
                $request->attributes->set(self::CONTEXT, $context);

                return $next($request);
            }

            // A wrong token is always a hard failure, strict mode or not — it is
            // never an un-migrated device, which sends no token at all.
            Log::warning('Attendance device rejected: unknown token.', [
                'ip' => $request->ip(),
            ]);

            return $this->responseError([], _lang('Invalid device token.'), 401);
        }

        if (config('attendance.device_strict', true)) {
            return $this->responseError([], _lang('A device token is required.'), 401);
        }

        // Migration window only. The punch is accepted with no tenant pinned, so
        // the controller falls back to its historical cross-institute lookup.
        Log::warning('Attendance device accepted WITHOUT a token — ATTENDANCE_DEVICE_STRICT is off. Issue this device a token and re-enable strict mode.', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $next($request);
    }

    private function presentedToken(Request $request): ?string
    {
        $token = $request->header('X-Device-Token')
            ?: $request->bearerToken()
            ?: $request->input('device_token');

        return is_string($token) ? trim($token) : null;
    }

    /**
     * Map a device token back to the institute/branch that owns it.
     *
     * @return array{institute_id:int, branch_id:int|null}|null
     */
    private function resolveTenant(string $token): ?array
    {
        // Compared in the database rather than in PHP so the secret never has to
        // be materialised for every institute. Tokens are 64 hex characters from
        // a CSPRNG, so they are not guessable and not enumerable via timing here.
        $row = DB::table('settings')
            ->where('name', self::SETTING_NAME)
            ->where('value', $token)
            ->first(['institute_id', 'branch_id']);

        if (! $row || ! $row->institute_id) {
            return null;
        }

        return [
            'institute_id' => (int) $row->institute_id,
            'branch_id' => $row->branch_id !== null ? (int) $row->branch_id : null,
        ];
    }
}
