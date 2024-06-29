<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\ZoomMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\Academic\Models\ClassModel;

class ZoomController extends Controller
{
    private function zoomToken()
    {
        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode(
                    get_option('zoom_client_key').
                        ':'.
                        get_option('zoom_client_secret')
                ),
            ])
            ->post(
                'https://zoom.us/oauth/token',
                [
                    'grant_type' => 'account_credentials',
                    'account_id' => get_option('zoom_account_id'),
                ]
            );

        return $response->json()['access_token'] ?? null;
    }

    private function zoom()
    {
        return Http::withToken(
            $this->zoomToken()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $zoomConnected = false;
        try {
            $check = $this->zoom()
                ->get(
                    config('zoom.base_url').'users/me'
                );

            $zoomConnected = $check->successful();
        } catch (\Exception $e) {
            $zoomConnected = false;
        }

        if ($zoomConnected) {
            $meetings = ZoomMeeting::latest()->get();
        } else {
            $meetings = collect();
        }

        return view(
            'academic::zoom.index',
            compact(
                'meetings',
                'zoomConnected'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'academic::zoom.create',
            [
                'classes' => ClassModel::all(),
            ]

        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable',
            'section_id' => 'nullable',
            'topic' => 'required|string|max:255',
            'agenda' => 'nullable|string',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
            'password' => 'nullable|string|min:6|max:10',
        ]);

        $response =
            $this->zoom()
                ->post(
                    config('zoom.base_url').'users/me/meetings',
                    [
                        'topic' => $request->topic,
                        'type' => 2,
                        'start_time' => $request->start_time,
                        'duration' => $request->duration,
                        'password' => $request->password,
                        'agenda' => $request->agenda,
                        'settings' => [
                            'host_video' => true,
                            'participant_video' => true,
                            'waiting_room' => false,
                        ],
                    ]
                );

        if (! $response->successful()) {
            return back()
                ->with('error', 'Zoom API connection failed');
        }

        $data = $response->json();
        ZoomMeeting::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'teacher_id' => get_teacher_id(),
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'meeting_id' => $data['id'],
            'topic' => $data['topic'],
            'agenda' => $data['agenda'] ?? null,
            'start_time' => $data['start_time'],
            'duration' => $data['duration'],
            'password' => $data['password'],
            'join_url' => $data['join_url'],
            'start_url' => $data['start_url'],
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('zoom.index')
            ->with(
                'success',
                'Zoom Meeting Created'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $meeting =
            ZoomMeeting::findOrFail($id);

        return view(
            'academic::zoom.edit',
            [
                'meeting' => $meeting,
                'classes' => ClassModel::all(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $meeting =
            ZoomMeeting::findOrFail($id);

        $request->validate([
            'topic' => 'required',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
        ]);

        $response =
            $this->zoom()
                ->patch(
                    config('zoom.base_url').
                        'meetings/'.$meeting->meeting_id,
                    [
                        'topic' => $request->topic,
                        'start_time' => $request->start_time,
                        'duration' => $request->duration,
                        'password' => $request->password,
                    ]
                );

        if ($response->successful()) {
            $meeting->update(
                $request->only([
                    'teacher_id',
                    'class_id',
                    'section_id',
                    'topic',
                    'agenda',
                    'start_time',
                    'duration',
                    'password',
                ])

            );
        }

        return redirect()
            ->route('zoom.index')
            ->with(
                'success',
                'Zoom Meeting Updated'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $meeting =
            ZoomMeeting::findOrFail($id);

        $this->zoom()
            ->delete(
                config('zoom.base_url').
                    'meetings/'.$meeting->meeting_id

            );

        $meeting->delete();

        return back()
            ->with(
                'success',
                'Deleted'
            );
    }
}
