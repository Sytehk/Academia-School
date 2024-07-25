@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ $onlineExam->exists ? _lang('Edit Online Exam') : _lang('Create Online Exam') }}
@endsection
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <form method="POST" action="{{ $onlineExam->exists ? route('online-exams.update', $onlineExam->id) : route('online-exams.store') }}">
                @csrf
                @if ($onlineExam->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Name') }}</label>
                        <input name="name" class="form-control" value="{{ old('name', $onlineExam->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Academic Exam') }}</label>
                        <select name="exam_id" class="form-select" required>
                            @foreach ($exams as $exam)
                                <option value="{{ $exam->id }}" @selected(old('exam_id', $onlineExam->exam_id) == $exam->id)>{{ $exam->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Class') }}</label>
                        <select name="class_id" class="form-select" required>
                            {!! create_option('classes', 'id', 'class_name', old('class_id', $onlineExam->class_id)) !!}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ _lang('Subject') }}</label>
                        <select name="subject_id" class="form-select" required>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id', $onlineExam->subject_id) == $subject->id)>{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ _lang('Duration (min)') }}</label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $onlineExam->duration_minutes ?: 30) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Start') }}</label>
                        <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', optional($onlineExam->start_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('End') }}</label>
                        <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', optional($onlineExam->end_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ _lang('Pass marks') }}</label>
                        <input type="number" step="0.01" name="pass_marks" class="form-control" value="{{ old('pass_marks', $onlineExam->pass_marks) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ _lang('Attempt limit') }}</label>
                        <input type="number" name="attempt_limit" class="form-control" value="{{ old('attempt_limit', $onlineExam->attempt_limit ?: 1) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ _lang('Online full marks') }}</label>
                        <input type="number" step="0.01" name="online_full_marks" class="form-control" value="{{ old('online_full_marks', $onlineExam->online_full_marks) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ _lang('Academic full marks') }}</label>
                        <input type="number" step="0.01" name="academic_full_marks" class="form-control" value="{{ old('academic_full_marks', $onlineExam->academic_full_marks) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Transfer policy') }}</label>
                        <select name="transfer_policy" class="form-select">
                            <option value="ask" @selected(old('transfer_policy', $onlineExam->transfer_policy) === 'ask')>{{ _lang('Ask') }}</option>
                            <option value="skip" @selected(old('transfer_policy', $onlineExam->transfer_policy) === 'skip')>{{ _lang('Skip existing') }}</option>
                            <option value="update" @selected(old('transfer_policy', $onlineExam->transfer_policy) === 'update')>{{ _lang('Update existing') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ _lang('Mark slot (1-6)') }}</label>
                        <input type="number" min="1" max="6" name="mark_component_slot" class="form-control" value="{{ old('mark_component_slot', $onlineExam->mark_component_slot ?: 1) }}">
                    </div>
                    <div class="col-md-12 d-flex gap-4">
                        <label><input type="checkbox" name="negative_marking" value="1" @checked(old('negative_marking', $onlineExam->negative_marking))> {{ _lang('Negative marking') }}</label>
                        <label><input type="checkbox" name="random_questions" value="1" @checked(old('random_questions', $onlineExam->random_questions))> {{ _lang('Random questions') }}</label>
                        <label><input type="checkbox" name="random_options" value="1" @checked(old('random_options', $onlineExam->random_options))> {{ _lang('Random options') }}</label>
                        <label><input type="checkbox" name="conversion_enabled" value="1" @checked(old('conversion_enabled', $onlineExam->conversion_enabled))> {{ _lang('Convert scale') }}</label>
                        <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $onlineExam->is_active ?? true))> {{ _lang('Active') }}</label>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ _lang('Negative per wrong') }}</label>
                        <input type="number" step="0.01" name="negative_marks_per_wrong" class="form-control" value="{{ old('negative_marks_per_wrong', $onlineExam->negative_marks_per_wrong) }}">
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>

    @if ($onlineExam->exists)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ _lang('Questions') }}</h3></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Question') }}</th>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Marks') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($onlineExam->questions as $question)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::limit(strip_tags($question->question_text), 80) }}</td>
                                <td>{{ $question->type }}</td>
                                <td>{{ $question->marks }}</td>
                                <td>
                                    <form method="POST" action="{{ route('online-exams.questions.destroy', $question->id) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">{{ _lang('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <form method="POST" action="{{ route('online-exams.questions.store', $onlineExam->id) }}" class="row g-3 mt-3">
                    @csrf
                    <div class="col-md-12">
                        <textarea name="question_text" class="form-control" placeholder="{{ _lang('Question text') }}" required></textarea>
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="multiple_choice">MCQ</option>
                            <option value="multiple_select">{{ _lang('Multiple select') }}</option>
                            <option value="true_false">{{ _lang('True/False') }}</option>
                            <option value="fill_blank">{{ _lang('Fill in the blank') }}</option>
                            <option value="short_answer">{{ _lang('Short answer') }}</option>
                            <option value="descriptive">{{ _lang('Descriptive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3"><input name="options" class="form-control" placeholder="A,B,C,D"></div>
                    <div class="col-md-2"><input name="correct_answer" class="form-control" placeholder="A"></div>
                    <div class="col-md-2"><input type="number" step="0.01" name="marks" class="form-control" value="1" required></div>
                    <div class="col-md-2"><button class="btn btn-light">{{ _lang('Add Question') }}</button></div>
                </form>
            </div>
        </div>
    @endif
@endsection
