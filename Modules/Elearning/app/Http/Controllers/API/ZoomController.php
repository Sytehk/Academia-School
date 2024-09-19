<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\Elearning\Models\ZoomMeeting;

class ZoomController extends Controller
{
    use Authenticatable;

    private $zoomToken;

    public function __construct()
    {
        $this->zoomToken = $this->generateZoomToken();
    }

    private function generateZoomToken()
    {
        $response = Http::asForm()->withHeaders([
            'Authorization' => 'Basic '.base64_encode(config('zoom.client_id').':'.config('zoom.client_secret')),
        ])->post('https://zoom.us/oauth/token', [
            'grant_type' => 'account_credentials',
            'account_id' => config('zoom.account_id'),
        ]);

        return $response->json()['access_token'] ?? null;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page', 10);
        $zoomMeetings = ZoomMeeting::where('start_time', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString())
            ->latest()
            ->paginate($perPage);

        return $this->responseSuccess(
            $zoomMeetings,
            'Zoom Meetings list fetch successfully.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date_format:Y-m-d\TH:i:s\Z', // ISO 8601 format (Asia/Dhaka)
            'duration' => 'required|integer|min:1', // Duration in minutes
            'password' => 'nullable|string|min:6|max:10', // Optional password, min 6 chars
            'agenda' => 'nullable|string|max:5000',
        ]);

        $startTimeUtc = Carbon::parse($request->start_time, 'Asia/Dhaka')->setTimezone('Asia/Dhaka')->toIso8601String();
        $response = Http::withToken($this->zoomToken)->post(config('zoom.base_url').'users/me/meetings', [
            'topic' => $request->topic,
            'type' => 2, // Scheduled Meeting
            'start_time' => $startTimeUtc,
            'duration' => $request->duration,
            'password' => '123456',
            'agenda' => 'Live Class',
            'timezone' => 'Asia/Dhaka',
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'waiting_room' => false,
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $meeting = ZoomMeeting::create([
                'institute_id' => $this->getCurrentInstituteId(),
                'course_id' => $request->course_id,
                'meeting_id' => $data['id'],
                'topic' => $data['topic'],
                'agenda' => 'Live Class',
                'start_time' => $data['start_time'],
                'duration' => $data['duration'],
                'password' => '123456',
                'join_url' => $data['join_url'],
                'start_url' => $data['start_url'],
                'created_by' => Auth::id(),
            ]);

            return $this->responseSuccess(
                $meeting,
                'Zoom Meeting create successfully.'
            );
        }

        return $this->responseError([], 'Zoom API Error');
    }

    public function show(string $id): JsonResponse
    {
        $zoomMeeting = ZoomMeeting::where('meeting_id', $id)->first();
        if (! $zoomMeeting) {
            return $this->responseError($zoomMeeting, 'Zoom Meeting not found');
        }

        return $this->responseSuccess(
            $zoomMeeting,
            'Zoom Meeting fetch successfully.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $zoomMeeting = ZoomMeeting::where('meeting_id', $id)->first();
        if (! $zoomMeeting) {
            return $this->responseError($zoomMeeting, 'Zoom Meeting not found');
        }
        $response = Http::withToken($this->zoomToken)->patch(config('zoom.base_url')."meetings/{$id}", [
            'topic' => $request->topic ?? $zoomMeeting->topic,
            'start_time' => $request->start_time ?? $zoomMeeting->start_time,
            'duration' => $request->duration ?? $zoomMeeting->duration,
            'password' => $request->password ?? $zoomMeeting->password,
        ]);

        if ($response->successful()) {
            $zoomMeeting->update($request->only(['topic', 'start_time', 'duration', 'password']));

            return $this->responseSuccess(
                $zoomMeeting,
                'Zoom Meeting update successfully.'
            );
        }

        return $this->responseError([], 'Failed to update meeting');
    }

    public function destroy(int $id): JsonResponse
    {
        $zoomMeeting = ZoomMeeting::where('meeting_id', $id)->first();
        if (! $zoomMeeting) {
            return $this->responseError($zoomMeeting, 'Zoom Meeting not found');
        }

        $response = Http::withToken($this->zoomToken)->delete(config('zoom.base_url')."meetings/{$id}");
        if ($response->successful()) {
            $zoomMeeting->delete();

            return $this->responseSuccess(
                [],
                'Zoom Meeting deleted successfully.'
            );
        }

        return $this->responseError([], 'Failed to delete meeting');
    }
}
