<?php

namespace Modules\SMS\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Modules\Academic\Models\SmsLog;
use Modules\Academic\Services\UserService;
use Modules\SMS\Services\SmsTemplateService;
use RealRashid\SweetAlert\Facades\Alert;

class SmsController extends Controller
{
    public function __construct(
        private readonly SmsTemplateService $smsTemplateService,
        private readonly UserService $userService,
        private readonly SmsService $smsService,
    ) {}

    public function index(): Factory|View
    {
        return view('sms::sms_compose.index', [
            'smsTemplates' => $this->smsTemplateService->getSmsTemplates(),
        ]);
    }

    public function send(Request $request): Redirector|RedirectResponse
    {
        // These rows were written directly with SmsLog::save() -- bypassing
        // SmsService::queueLog(), which is the only thing that dispatches
        // SendSmsJob. No job meant these messages depended entirely on the
        // sms:process-logs cron sweeping up status-0 rows; combined with that
        // cron never having been scheduled (see notifications:process), bulk
        // SMS from this screen never actually sent. Routed through queueLog()
        // so dispatch is consistent with every other SMS send in the app.
        //
        // The @ini_set/@set_time_limit(0) override is gone with it: it only
        // existed because this used to do everything inline in the request;
        // queueLog() just inserts a row and dispatches a queued job, so the
        // work this endpoint now does is proportional to writes, not sends.
        $body = strip_tags($request->input('body'));
        $senderId = Auth::user()->id;

        try {
            if ($request->input('users') != '') {
                foreach ($request->input('users') as $userId => $mobileNumber) {
                    $this->smsService->queueLog(
                        $mobileNumber,
                        $body,
                        $request->user_type,
                        $senderId,
                        ['user_id' => $userId ?: null]
                    );
                }
            } elseif ($request->input('individual_number') != '') {
                // The original bug: $user_id here read whatever the FOREACH
                // above last assigned it to (or an undefined-variable warning
                // if that branch never ran), not a value meaningful to an
                // "Individual" recipient who was never looked up by user id.
                // sms_logs.user_id is nullable (ON DELETE SET NULL) precisely
                // for this case -- an ad-hoc number with no linked account --
                // so null is used here rather than a sentinel 0 that would
                // fail the foreign key the moment it's enforced.
                $this->smsService->queueLog(
                    $request->individual_number,
                    $body,
                    'Individual',
                    $senderId,
                    ['user_id' => null]
                );
            } else {
                Alert::error('Error', _lang('Invalid mobile number Or Illegal Operation.'));

                return redirect('sms-compose');
            }

            Alert::toast('Message Send Successfully.', 'success');

            return redirect('sms-compose');
        } catch (Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect('sms-compose');
        }
    }

    public function sentSMSReport(Request $request): Factory|View
    {
        $smsReports = [];
        $requestFromDate = $request->from;
        $requestToDate = $request->to;

        $fromDate = Carbon::parse($requestFromDate)->startOfDay();
        $toDate = Carbon::parse($requestToDate)->endOfDay();

        if ($requestToDate && $requestToDate) {
            $smsReports = SmsLog::where('status', 1)->whereBetween('created_at', [$fromDate, $toDate])
                ->with('user')
                ->get();
        }

        return view('sms::sms_compose.reports.sms', compact('smsReports', 'requestFromDate', 'requestToDate'));
    }

    public function get_users($user_type = ''): Factory|Redirector|RedirectResponse|View
    {
        if ($user_type == '') {
            Alert::error('Error', _lang('Something went wrong. User not found.'));

            return redirect('sms-template');
        }

        $users = $this->userService->getUsersByExistType($user_type);

        return view('sms::sms_compose.others-filter-data', compact('users'));
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
