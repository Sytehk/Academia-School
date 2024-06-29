<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\Academic\Models\Event;

class APIEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Event::select('id', 'start_date', 'end_date', 'name', 'image', 'details', 'location')->paginate($perPage);

        return $this->responseSuccess($events, 'Events fetch successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'name' => 'required',
            'details' => 'required',
            'location' => 'required',
            'image' => 'nullable',
        ]);

        $event = new Event;
        $event->institute_id = get_institute_id();
        $event->branch_id = get_branch_id();
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->name = $request->name;
        $event->details = $request->details;
        $event->location = $request->location;

        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $event->image = fileUploader('events/', 'png', $request['image']);
        }
        $event->save();

        return $this->responseSuccess([], _lang('Event has been create successfully.'));
    }

    public function show(int $id): JsonResponse
    {
        $event = Event::find($id);
        if (! $event) {
            return $this->responseError([], _lang('Something went wrong. Event can not be found.'), 404);
        }

        return $this->responseSuccess($event, _lang('Event has been fetch.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'name' => 'required',
            'details' => 'required',
            'location' => 'required',
            'image' => 'nullable',
        ]);

        $event = Event::find($id);
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->name = $request->name;
        $event->details = $request->details;
        $event->location = $request->location;
        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $event->image = fileUploader('events/', 'png', $request['image'], $event->image);
        }
        $event->save();

        return $this->responseSuccess([], _lang('Event has been updated successfully.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $event = Event::find($id);
        if (! $event) {
            return $this->responseError([], _lang('Something went wrong. Event can not be found.'), 404);
        }

        $event->delete();

        return $this->responseSuccess([], _lang('Event has been deleted successfully.'));
    }
}
