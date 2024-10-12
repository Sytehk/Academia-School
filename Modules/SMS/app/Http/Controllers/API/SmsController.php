<?php

namespace Modules\SMS\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Academic\Models\SmsLog;
use Modules\Academic\Services\UserService;

class SmsController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function sentSMSReport(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;

        try {
            // Parse date filters
            $fromDate = $request->from ? Carbon::parse($request->from)->startOfDay() : null;
            $toDate = $request->to ? Carbon::parse($request->to)->endOfDay() : null;

            $smsReportsQuery = SmsLog::where('status', 1);

            // Apply date filters if provided
            if ($fromDate && $toDate) {
                $smsReportsQuery->whereBetween('created_at', [$fromDate, $toDate]);
            }

            // Add user relationship
            $smsReportsQuery->with('user');

            // Apply pagination
            $smsReports = $smsReportsQuery->orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess($smsReports, 'SMS Reports have been fetched successfully.');
        } catch (Exception $exception) {
            return $this->responseError(
                [],
                $exception->getMessage(),
                $exception->getCode() ?: 500
            );
        }
    }

    public function get_users(Request $request): JsonResponse
    {
        $request->validate([
            'user_type' => 'required|string|in:Student,Teacher,Staff,Admin,Parent',
        ]);

        $userType = $request->user_type;

        if (! $userType) {
            return $this->responseError([], _lang('Something went wrong. User not found.'));
        }

        $users = $this->userService->getUsersByExistType($userType);

        return $this->responseSuccess($users, 'Users fetch successfully.');
    }

    public function send(Request $request): JsonResponse
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        // ✅ Validate request
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:500',
            'user_type' => 'nullable|string|in:Teacher,Admin,Staff,Individual,Student',
            'users' => 'nullable|array',
            'users.*.user_id' => 'nullable|integer',
            'users.*.mobile_number' => 'nullable|string',
            'individual_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->responseError([], $validator->errors()->first());
        }

        $body = strip_tags($request->body);

        // ✅ Must have either users[] or individual_number
        if (empty($request->users) && empty($request->individual_number)) {
            return $this->responseError([], _lang('Recipient not found. Please provide users or individual_number.'));
        }

        DB::beginTransaction();
        try {
            // ✅ Case 1: Multiple users
            if (! empty($request->users)) {
                foreach ($request->users as $user) {
                    if (empty($user['mobile_number'])) {
                        continue;
                    }

                    SmsLog::create([
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'receiver' => $user['mobile_number'],
                        'message' => $body,
                        'user_id' => $user['user_id'] ?? 0,
                        'user_type' => $request->user_type ?? 'Unknown',
                        'sender_id' => Auth::id(),
                        'status' => 0,
                    ]);
                }
            }

            // ✅ Case 2: Single individual number
            if (! empty($request->individual_number)) {
                SmsLog::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'receiver' => $request->individual_number,
                    'message' => $body,
                    'user_id' => 0,
                    'user_type' => 'Individual',
                    'sender_id' => Auth::id(),
                    'status' => 0,
                ]);
            }

            DB::commit();

            return $this->responseSuccess([], _lang('Message queued successfully for sending.'));
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], _lang('Failed to send message: ').$e->getMessage());
        }
    }

    public function calculateSmsLength($message)
    {
        // Count the number of characters in the message
        $messageLength = strlen($message);

        // Check if the message length is within the SMS limit
        if ($messageLength <= 160) {
            // The message fits in a single SMS
            return 1;
        } else {
            // The message needs to be split into multiple SMS messages
            $numberOfSms = ceil($messageLength / 160);

            return $numberOfSms;
        }
    }
}
