<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Event;
use RealRashid\SweetAlert\Facades\Alert;

class EventController extends Controller
{
    public function index(): Factory|View
    {
        $events = Event::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderByDesc('id')
            ->get();

        return view('academic::event.list', compact('events'));
    }

    public function create(Request $request): Factory|View
    {
        return view('academic::event.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'name' => 'required',
            'details' => 'required',
            'location' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $event = new Event;
        $event->institute_id = get_institute_id();
        $event->branch_id = get_branch_id();
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->name = $request->name;
        $event->details = sanitize_rich_text($request->details);
        $event->location = $request->location;

        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $event->image = fileUploader('events/', 'png', $request['image']);
        }
        $event->save();

        Alert::toast('Event has been create successfully.', 'success');

        return redirect('events');
    }

    private function findEventOrFail(int $id): ?Event
    {
        return Event::where('id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();
    }

    public function show(Request $request, int $id): Factory|View|RedirectResponse
    {
        $event = $this->findEventOrFail($id);

        if (! $event) {
            Alert::error('Error', _lang('Event not found.'));

            return redirect('events');
        }

        return view('academic::event.view', compact('event', 'id'));
    }

    public function edit(Request $request, int $id): Factory|View|RedirectResponse
    {
        $event = $this->findEventOrFail($id);

        if (! $event) {
            Alert::error('Error', _lang('Event not found.'));

            return redirect('events');
        }

        return view('academic::event.edit', compact('event', 'id'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'name' => 'required',
            'details' => 'required',
            'location' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $event = $this->findEventOrFail($id);

        if (! $event) {
            Alert::error('Error', _lang('Event not found.'));

            return redirect('events');
        }

        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->name = $request->name;
        $event->details = sanitize_rich_text($request->details);
        $event->location = $request->location;
        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $event->image = fileUploader('events/', 'png', $request['image'], $event->image);
        }
        $event->save();

        Alert::toast('Event has been update successfully.', 'success');

        return redirect('events');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $event = $this->findEventOrFail($id);

        if (! $event) {
            Alert::error('Error', _lang('Event not found.'));

            return redirect('events');
        }

        $event->delete();

        Alert::toast('Event has been delete successfully.', 'success');

        return redirect('events');
    }
}
