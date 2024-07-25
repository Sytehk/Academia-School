@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Transfer Online Marks') }}
@endsection
@section('content')
    <div class="d-flex gap-2 mb-3">
        <a class="btn btn-light" target="_blank" href="{{ route('online-exams.transfer-print', $onlineExam->id) }}">{{ _lang('Print Preview') }}</a>
    </div>
    <form method="POST" action="{{ route('online-exams.transfer.store', $onlineExam->id) }}">
        @csrf
        <div class="card mb-4">
            <div class="card-body">
                <p>{{ _lang('Online exam') }}: <strong>{{ $onlineExam->name }}</strong> → {{ $onlineExam->subject?->subject_name }}</p>
                <p class="text-muted">{{ _lang('Marks are transferred using each student\'s current roll and group from their active session record.') }}</p>
                <div class="mb-3" style="max-width:280px;">
                    <label class="form-label">{{ _lang('If a mark already exists') }}</label>
                    <select name="policy" class="form-select">
                        <option value="ask" @selected($onlineExam->transfer_policy === 'ask')>{{ _lang('Ask / skip conflicts') }}</option>
                        <option value="skip">{{ _lang('Skip existing') }}</option>
                        <option value="update">{{ _lang('Update existing') }}</option>
                    </select>
                </div>
                @if (empty($preview))
                    <div class="alert alert-warning">{{ _lang('No submitted online exam attempts found for transfer.') }}</div>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ _lang('Student') }}</th>
                                <th>{{ _lang('Roll') }}</th>
                                <th>{{ _lang('Section') }}</th>
                                <th>{{ _lang('Group') }}</th>
                                <th>{{ _lang('Online Score') }}</th>
                                <th>{{ _lang('Mark Input') }}</th>
                                <th>{{ _lang('Existing') }}</th>
                                <th>{{ _lang('Session') }}</th>
                                <th>{{ _lang('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview as $row)
                                <tr class="{{ empty($row['session_found']) ? 'table-warning' : '' }}">
                                    <td>
                                        <input type="checkbox" name="student_ids[]" value="{{ $row['student_id'] }}" @checked($row['session_found']) @disabled(empty($row['session_found']))>
                                    </td>
                                    <td>{{ $row['student_name'] }}</td>
                                    <td>{{ $row['roll'] ?? '-' }}</td>
                                    <td>{{ $row['section_name'] ?? '-' }}</td>
                                    <td>{{ $row['group_name'] ?? '-' }}</td>
                                    <td>{{ $row['online_score'] }} / {{ $row['online_full_marks'] }}</td>
                                    <td>{{ $row['transferred_marks'] }} / {{ $row['academic_full_marks'] }} @if($row['converted']) ({{ _lang('converted') }}) @endif</td>
                                    <td>{{ $row['existing_mark'] ?? '-' }}</td>
                                    <td>
                                        @if ($row['session_found'])
                                            <span class="badge bg-success">{{ _lang('Valid') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ _lang('Not Found') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['already_transferred'] ? _lang('Transferred') : _lang('Pending') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button class="btn btn-primary">{{ _lang('Transfer Selected') }}</button>
                @endif
            </div>
        </div>
    </form>
@endsection
