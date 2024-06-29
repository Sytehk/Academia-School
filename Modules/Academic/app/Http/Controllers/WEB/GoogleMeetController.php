<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Enums\UserLogAction;
use App\Exports\GoogleMeetMeetingExport;
use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use App\Traits\Trackable;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Http\Requests\GoogleMeetMeeting\GoogleMeetMeetingStoreRequest;
use Modules\Academic\Http\Requests\GoogleMeetMeeting\GoogleMeetMeetingUpdateRequest;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\GoogleMeetMeeting;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\StudentGroup;
use Modules\Academic\Models\Subject;
use Modules\Academic\Models\Teacher;
use Modules\Academic\Services\GoogleMeetService;
use Pdf;
use RealRashid\SweetAlert\Facades\Alert;

class GoogleMeetController extends Controller
{
    use HasPermissionWebTrait, Trackable;

    public function __construct(private readonly GoogleMeetService $googleMeetService) {}

    private function isTeacherScoped(): bool
    {
        return Auth::user()->user_type === 'Teacher';
    }

    /**
     * Teacher accounts are never assigned a Spatie role/permission (only Admin is), so
     * gate on permissions for Admin and rely on route + isTeacherScoped() filtering for
     * Teacher, matching how the existing shared Zoom controller handles both panels.
     */
    private function authorizeAction(string $permission): bool|RedirectResponse
    {
        if ($this->isTeacherScoped()) {
            return true;
        }

        return $this->checkPermission($permission);
    }

    private function baseQuery(Request $request)
    {
        $query = GoogleMeetMeeting::query()->with(['classModel', 'section', 'group', 'subject', 'teacher']);

        if ($this->isTeacherScoped()) {
            $query->where('teacher_id', get_teacher_id());
        }

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->section_id) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date) {
            $query->whereDate('start_date', $request->date);
        }

        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        return $query->orderByDesc('start_date')->orderByDesc('start_time');
    }

    private function formData(): array
    {
        return [
            'classes' => ClassModel::all(),
            'sections' => Section::all(),
            'groups' => StudentGroup::all(),
            'subjects' => Subject::all(),
            'teachers' => Teacher::all(),
        ];
    }

    public function index(Request $request): bool|Factory|RedirectResponse|View
    {
        $meetings = $this->baseQuery($request)->paginate(20)->withQueryString();

        return view('academic::google_meet.index', array_merge(
            compact('meetings'),
            $this->formData()
        ));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        return view('academic::google_meet.create', $this->formData());
    }

    private function buildIsoDateTime(string $date, string $time): string
    {
        return Carbon::parse($date.' '.$time)->format('Y-m-d\TH:i:s');
    }

    private function createSingleOccurrence(array $data, ?int $parentId = null): ?GoogleMeetMeeting
    {
        $timezone = get_option('google_meet_default_timezone') ?: 'UTC';
        $startDateTime = $this->buildIsoDateTime($data['start_date'], $data['start_time']);
        $endTime = $data['end_time'] ?? Carbon::parse($data['start_time'])->addMinutes((int) $data['duration'])->format('H:i');
        $endDateTime = $this->buildIsoDateTime($data['start_date'], $endTime);

        $result = $this->googleMeetService->createMeeting(
            $data['title'],
            $data['description'] ?? null,
            $startDateTime,
            $endDateTime,
            $timezone
        );

        if (! $result) {
            return null;
        }

        return GoogleMeetMeeting::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? ($this->isTeacherScoped() ? get_teacher_id() : null),
            'class_id' => $data['class_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'group_id' => $data['group_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'start_date' => $data['start_date'],
            'start_time' => $data['start_time'],
            'end_time' => $endTime,
            'duration' => $data['duration'],
            'meet_link' => $result['meet_link'],
            'meet_event_id' => $result['event_id'],
            'organizer_id' => Auth::id(),
            'status' => 'scheduled',
            'is_recurring' => ! empty($data['is_recurring']),
            'recurrence_frequency' => $data['recurrence_frequency'] ?? null,
            'recurrence_end_date' => $data['recurrence_end_date'] ?? null,
            'parent_meeting_id' => $parentId,
            'notes' => $data['notes'] ?? null,
            'visibility' => $data['visibility'] ?? ['teacher', 'student'],
            'created_by' => Auth::id(),
        ]);
    }

    public function store(GoogleMeetMeetingStoreRequest $request): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $meeting = $this->createSingleOccurrence($data);

        if (! $meeting) {
            Alert::error('Error', 'Failed to create the Google Meet. Please verify your Google Meet settings are connected.');

            return back()->withInput();
        }

        if (! empty($data['is_recurring']) && ! empty($data['recurrence_end_date'])) {
            $frequency = $data['recurrence_frequency'] ?? 'weekly';
            $interval = $frequency === 'daily' ? 1 : 7;
            $nextDate = Carbon::parse($data['start_date'])->addDays($interval);
            $endDate = Carbon::parse($data['recurrence_end_date']);

            while ($nextDate->lte($endDate)) {
                $occurrenceData = array_merge($data, ['start_date' => $nextDate->toDateString()]);
                $this->createSingleOccurrence($occurrenceData, $meeting->id);
                $nextDate = $nextDate->copy()->addDays($interval);
            }
        }

        $this->trackAction(UserLogAction::CREATE, GoogleMeetMeeting::class, $meeting->id, 'Google Meet meeting created: '.$meeting->title);

        Alert::toast('Google Meet meeting created successfully.', 'success');

        return redirect()->route('google-meet.index');
    }

    public function edit(int $id): bool|Factory|RedirectResponse|View
    {
        $meeting = GoogleMeetMeeting::findOrFail($id);

        return view('academic::google_meet.edit', array_merge(
            compact('meeting'),
            $this->formData()
        ));
    }

    public function update(GoogleMeetMeetingUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        $meeting = GoogleMeetMeeting::findOrFail($id);
        $data = $request->validated();

        $timezone = get_option('google_meet_default_timezone') ?: 'UTC';
        $startDateTime = $this->buildIsoDateTime($data['start_date'], $data['start_time']);
        $endTime = $data['end_time'] ?? Carbon::parse($data['start_time'])->addMinutes((int) $data['duration'])->format('H:i');
        $endDateTime = $this->buildIsoDateTime($data['start_date'], $endTime);

        if ($meeting->meet_event_id) {
            $this->googleMeetService->updateMeeting(
                $meeting->meet_event_id,
                $data['title'],
                $data['description'] ?? null,
                $startDateTime,
                $endDateTime,
                $timezone
            );
        }

        $meeting->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? $meeting->teacher_id,
            'class_id' => $data['class_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'group_id' => $data['group_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'start_date' => $data['start_date'],
            'start_time' => $data['start_time'],
            'end_time' => $endTime,
            'duration' => $data['duration'],
            'notes' => $data['notes'] ?? null,
            'visibility' => $data['visibility'] ?? $meeting->visibility,
        ]);

        $this->trackAction(UserLogAction::UPDATE, GoogleMeetMeeting::class, $meeting->id, 'Google Meet meeting updated: '.$meeting->title);

        Alert::toast('Google Meet meeting updated successfully.', 'success');

        return redirect()->route('google-meet.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $meeting = GoogleMeetMeeting::findOrFail($id);

        if ($meeting->meet_event_id) {
            $this->googleMeetService->deleteMeeting($meeting->meet_event_id);
        }

        $meeting->delete();

        $this->trackAction(UserLogAction::DELETE, GoogleMeetMeeting::class, $id, 'Google Meet meeting deleted: '.$meeting->title);

        Alert::toast('Google Meet meeting deleted successfully.', 'success');

        return back();
    }

    public function cancel(int $id): RedirectResponse
    {
        $meeting = GoogleMeetMeeting::findOrFail($id);
        $meeting->update(['status' => 'cancelled']);

        $this->trackAction(UserLogAction::UPDATE, GoogleMeetMeeting::class, $id, 'Google Meet meeting cancelled: '.$meeting->title);

        Alert::toast('Meeting cancelled.', 'success');

        return back();
    }

    public function duplicate(int $id): RedirectResponse
    {
        $original = GoogleMeetMeeting::findOrFail($id);

        $meeting = $this->createSingleOccurrence([
            'title' => $original->title.' (Copy)',
            'description' => $original->description,
            'teacher_id' => $original->teacher_id,
            'class_id' => $original->class_id,
            'section_id' => $original->section_id,
            'group_id' => $original->group_id,
            'subject_id' => $original->subject_id,
            'start_date' => $original->start_date->toDateString(),
            'start_time' => $original->start_time,
            'end_time' => $original->end_time,
            'duration' => $original->duration,
            'notes' => $original->notes,
            'visibility' => $original->visibility,
        ]);

        if (! $meeting) {
            Alert::error('Error', 'Failed to duplicate the meeting.');

            return back();
        }

        Alert::toast('Meeting duplicated successfully.', 'success');

        return redirect()->route('google-meet.edit', $meeting->id);
    }

    public function print(int $id): Factory|View
    {
        $meeting = GoogleMeetMeeting::with(['classModel', 'section', 'group', 'subject', 'teacher'])->findOrFail($id);

        return view('academic::google_meet.print', compact('meeting'));
    }

    public function exportPdf(Request $request)
    {
        $meetings = $this->baseQuery($request)->get();

        return Pdf::loadView('academic::google_meet.pdf', compact('meetings'))
            ->setPaper('a4', 'landscape')
            ->download('google-meet-meetings-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $meetings = $this->baseQuery($request)->get();

        return Excel::download(new GoogleMeetMeetingExport($meetings), 'google-meet-meetings-'.now()->format('Y-m-d').'.xlsx');
    }
}
