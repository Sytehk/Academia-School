<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ZoomMeeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ZoomController extends Controller
{
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
        $zoomMeetings = ZoomMeeting::latest()->paginate($perPage);
        if (! $zoomMeetings) {
            return $this->responseError($zoomMeetings, _lang('ZoomMeetings not found'));
        }

        return $this->responseSuccess(
            $zoomMeetings,
            _lang('Zoom Meeting fetch successfully.')
        );
    }

    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $request->validate([
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date_format:Y-m-d\TH:i:s\Z', // ISO 8601 format (UTC)
            'duration' => 'required|integer|min:1', // Duration in minutes
            'password' => 'nullable|string|min:6|max:10', // Optional password, min 6 chars
            'agenda' => 'nullable|string|max:500',
        ]);

        $response = Http::withToken($this->zoomToken)->post(config('zoom.base_url').'users/me/meetings', [
            'topic' => $request->topic,
            'type' => 2, // Scheduled Meeting
            'start_time' => $request->start_time,
            'duration' => $request->duration,
            'password' => $request->password,
            'agenda' => $request->agenda,
            'timezone' => 'UTC',
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'waiting_room' => false,
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $meeting = ZoomMeeting::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'meeting_id' => $data['id'],
                'topic' => $data['topic'],
                'agenda' => $request->agenda,
                'start_time' => $data['start_time'],
                'duration' => $data['duration'],
                'password' => $data['password'],
                'join_url' => $data['join_url'],
                'start_url' => $data['start_url'],
                'created_by' => Auth::id(),
            ]);

            return $this->responseSuccess(
                $meeting,
                _lang('Zoom Meeting create successfully.')
            );
        }

        return $this->responseError([], 'Zoom API Error');
    }

    public function show(string $id): JsonResponse
    {
        $zoomMeeting = ZoomMeeting::where('meeting_id', $id)->firstOrFail();
        if (! $zoomMeeting) {
            return $this->responseError($zoomMeeting, _lang('ZoomMeeting not found'));
        }

        return $this->responseSuccess(
            $zoomMeeting,
            _lang('Zoom Meeting fetch successfully.')
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $meeting = ZoomMeeting::where('meeting_id', $id)->firstOrFail();

        $response = Http::withToken($this->zoomToken)->patch(config('zoom.base_url')."meetings/{$id}", [
            'topic' => $request->topic ?? $meeting->topic,
            'start_time' => $request->start_time ?? $meeting->start_time,
            'duration' => $request->duration ?? $meeting->duration,
            'password' => $request->password ?? $meeting->password,
        ]);

        if ($response->successful()) {
            $meeting->update($request->only(['topic', 'start_time', 'duration', 'password']));

            return $this->responseSuccess(
                $meeting,
                _lang('Zoom Meeting update successfully.')
            );
        }

        return $this->responseError([], 'Failed to update meeting');
    }

    public function destroy(int $id): JsonResponse
    {
        $meeting = ZoomMeeting::where('meeting_id', $id)->firstOrFail();

        $response = Http::withToken($this->zoomToken)->delete(config('zoom.base_url')."meetings/{$id}");

        if ($response->successful()) {
            $meeting->delete();

            return $this->responseSuccess(
                $meeting,
                _lang('Zoom Meeting deleted successfully.')
            );
        }

        return $this->responseError([], 'Failed to delete meeting');
    }
}
