@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Grand Final Result') }}
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">{{ _lang('Select Students') }}</h3>
                    <a href="{{ route('grand-final-result.index') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ _lang('Back to Filter') }}
                    </a>
                </div>
                <div class="card-body">
                    @if ($studentSessions->isEmpty())
                        <div class="alert alert-warning">{{ _lang('No students found for the selected filters.') }}</div>
                    @else
                        <form id="grand-final-select-form" method="POST"
                            action="{{ route('grand-final-result.preview') }}">
                            @csrf
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-3">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" id="select_all_students">
                                            </th>
                                            <th>{{ _lang('Roll') }}</th>
                                            <th>{{ _lang('Name') }}</th>
                                            <th>{{ _lang('Section') }}</th>
                                            <th>{{ _lang('Group') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($studentSessions as $studentSession)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="student_session_ids[]"
                                                        value="{{ $studentSession->id }}" class="student-checkbox">
                                                </td>
                                                <td>{{ $studentSession->roll }}</td>
                                                <td>{{ trim(($studentSession->student->first_name ?? '') . ' ' . ($studentSession->student->last_name ?? '')) }}</td>
                                                <td>{{ $studentSession->section?->section_name }}</td>
                                                <td>{{ $studentSession->group?->group_name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-5 d-flex gap-3">
                                <button type="submit" formaction="{{ route('grand-final-result.preview') }}"
                                    formtarget="_blank" class="btn btn-primary rect-btn">
                                    <i class="fa fa-eye" aria-hidden="true"></i> {{ _lang('Preview Result') }}
                                </button>
                                <button type="submit" formaction="{{ route('grand-final-result.pdf') }}"
                                    formtarget="_blank" class="btn btn-success rect-btn">
                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> {{ _lang('Download PDF') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $('#select_all_students').on('change', function() {
            $('.student-checkbox').prop('checked', $(this).is(':checked'));
        });
    </script>
@stop
