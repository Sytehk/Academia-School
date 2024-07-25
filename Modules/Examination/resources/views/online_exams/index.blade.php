@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Online Examinations') }}
@endsection
@section('content')
    <div class="d-flex justify-content-end mb-4">
        <a class="btn btn-primary" href="{{ route('online-exams.create') }}">{{ _lang('Create Online Exam') }}</a>
    </div>
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ _lang('Name') }}</th>
                        <th>{{ _lang('Exam') }}</th>
                        <th>{{ _lang('Class') }}</th>
                        <th>{{ _lang('Subject') }}</th>
                        <th>{{ _lang('Window') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr>
                            <td>{{ $exam->name }}</td>
                            <td>{{ $exam->exam?->name }}</td>
                            <td>{{ $exam->class?->class_name }}</td>
                            <td>{{ $exam->subject?->subject_name }}</td>
                            <td>{{ optional($exam->start_at)->format('d M H:i') }} - {{ optional($exam->end_at)->format('d M H:i') }}</td>
                            <td>{{ $exam->is_active ? _lang('Active') : _lang('Inactive') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light" href="{{ route('online-exams.edit', $exam->id) }}">{{ _lang('Edit') }}</a>
                                <a class="btn btn-sm btn-light" href="{{ route('online-exams.results', $exam->id) }}">{{ _lang('Results') }}</a>
                                <a class="btn btn-sm btn-primary" href="{{ route('online-exams.transfer', $exam->id) }}">{{ _lang('Transfer Marks') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">{{ _lang('No online exams yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
