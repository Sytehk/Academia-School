@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Google Meet') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('google-meet.update', $meeting->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Meeting Title') }}</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $meeting->title) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Teacher') }}</label>
                        <select name="teacher_id" class="form-control">
                            <option value="">{{ _lang('Select Teacher') }}</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $meeting->teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ _lang('Description') }}</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $meeting->description) }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Class') }}</label>
                        <select name="class_id" class="form-control">
                            <option value="">{{ _lang('Select Class') }}</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id', $meeting->class_id) == $class->id)>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Section') }}</label>
                        <select name="section_id" class="form-control"
                            data-selected="{{ old('section_id', $meeting->section_id) }}">
                            <option value="">{{ _lang('Select Class First') }}</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Group') }}</label>
                        <select name="group_id" class="form-control">
                            <option value="">{{ _lang('Select Group') }}</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected(old('group_id', $meeting->group_id) == $group->id)>{{ $group->group_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Subject') }}</label>
                        <select name="subject_id" class="form-control">
                            <option value="">{{ _lang('Select Subject') }}</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id', $meeting->subject_id) == $subject->id)>{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Start Date') }}</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($meeting->start_date)->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Start Time') }}</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $meeting->start_time) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('End Time') }}</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $meeting->end_time) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Duration (minutes)') }}</label>
                        <input type="number" name="duration" class="form-control" value="{{ old('duration', $meeting->duration) }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ _lang('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $meeting->notes) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label d-block">{{ _lang('Visibility') }}</label>
                        @php $visibility = old('visibility', $meeting->visibility ?? []); @endphp
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="visibility[]" value="teacher" id="visTeacher" @checked(in_array('teacher', $visibility))>
                            <label class="form-check-label" for="visTeacher">{{ _lang('Teacher') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="visibility[]" value="student" id="visStudent" @checked(in_array('student', $visibility))>
                            <label class="form-check-label" for="visStudent">{{ _lang('Student') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="visibility[]" value="guardian" id="visGuardian" @checked(in_array('guardian', $visibility))>
                            <label class="form-check-label" for="visGuardian">{{ _lang('Guardian') }}</label>
                        </div>
                    </div>

                    @if ($meeting->meet_link)
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0">
                                {{ _lang('Meet Link') }}: <a href="{{ $meeting->meet_link }}" target="_blank">{{ $meeting->meet_link }}</a>
                            </div>
                        </div>
                    @endif
                </div>

                <button class="btn btn-primary mt-4">{{ _lang('Update Meeting') }}</button>
            </form>
        </div>
    </div>
@endsection
