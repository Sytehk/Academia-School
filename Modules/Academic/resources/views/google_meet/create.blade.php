@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Google Meet') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('google-meet.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Meeting Title') }}</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Teacher') }}</label>
                        <select name="teacher_id" class="form-control">
                            <option value="">{{ _lang('Select Teacher') }}</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ _lang('Description') }}</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Class') }}</label>
                        <select name="class_id" class="form-control">
                            <option value="">{{ _lang('Select Class') }}</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Section') }}</label>
                        <select name="section_id" class="form-control"
                            data-selected="{{ old('section_id') }}">
                            <option value="">{{ _lang('Select Class First') }}</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Group') }}</label>
                        <select name="group_id" class="form-control">
                            <option value="">{{ _lang('Select Group') }}</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>{{ $group->group_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Subject') }}</label>
                        <select name="subject_id" class="form-control">
                            <option value="">{{ _lang('Select Subject') }}</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Start Date') }}</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Start Time') }}</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('End Time') }}</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Duration (minutes)') }}</label>
                        <input type="number" name="duration" class="form-control" value="{{ old('duration', get_option('google_meet_default_duration') ?: 40) }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ _lang('Notes / Attachments Description') }}</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label d-block">{{ _lang('Visibility') }}</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="visibility[]" value="teacher" id="visTeacher" checked>
                            <label class="form-check-label" for="visTeacher">{{ _lang('Teacher') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="visibility[]" value="student" id="visStudent" checked>
                            <label class="form-check-label" for="visStudent">{{ _lang('Student') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="visibility[]" value="guardian" id="visGuardian">
                            <label class="form-check-label" for="visGuardian">{{ _lang('Guardian') }}</label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label d-block">{{ _lang('Recurring') }}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurring" onchange="document.getElementById('recurringOptions').classList.toggle('d-none', !this.checked)">
                            <label class="form-check-label" for="isRecurring">{{ _lang('Enable') }}</label>
                        </div>
                    </div>

                    <div id="recurringOptions" class="col-md-6 d-none">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Repeat') }}</label>
                                <select name="recurrence_frequency" class="form-control">
                                    <option value="weekly">{{ _lang('Weekly') }}</option>
                                    <option value="daily">{{ _lang('Daily') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Until') }}</label>
                                <input type="date" name="recurrence_end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary mt-4">{{ _lang('Create Meeting') }}</button>
            </form>
        </div>
    </div>
@endsection
