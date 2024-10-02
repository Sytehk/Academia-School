@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book Issue Filter') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Book Issues Filter') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ url('bookissues-filter-all') }}" method="post" autocomplete="off">
                        @csrf
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="status_id" class="control-label">{{ _lang('Filter By Status') }}</label>
                                <select name="status_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2">
                                    <option value="" {{ $idSelect == '' ?? 'selected' }}>{{ _lang('Select one') }}
                                    </option>
                                    <option value="0" {{ $idSelect == '0' ? 'selected' : '' }}>{{ _lang('ALL') }}
                                    </option>
                                    <option value="1" {{ $idSelect == '1' ? 'selected' : '' }}>{{ _lang('OPEN') }}
                                    </option>
                                    <option value="2" {{ $idSelect == '2' ? 'selected' : '' }}>
                                        {{ _lang('RETURNED') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="user_id" class="control-label">{{ _lang('Filter By  ID') }}</label>
                                <input type="text" class="form-control" name="user_id" placeholder="Enter ID"
                                    value="{{ request()->user_id }}">
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <input class="btn btn-primary btn-block rect-btn" style="margin-top:24px" value="Search"
                                type="submit">
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-default" data-collapsed="0">
                <div class="">
                    @if (!empty($issues))
                        <div class="col-lg-12 mt-5">
                            @if (isset($issues))
                                <form action="{{ route('books-issue-report-pdf-download') }}" method="post"
                                    style="display: inline;">
                                    @csrf

                                    <input type="hidden" name="status_id" value="{{ request()->status_id }}">
                                    <input type="hidden" name="user_id" value="{{ request()->user_id }}">

                                    <button type="submit" style="" class="btn btn-primary btn-sm pull-right">
                                        <i class="fa fa-print"></i>
                                        {{ _lang('Print Report') }}
                                    </button>
                                </form>
                            @endif
                            <div class="table-responsive mt-5">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>{{ _lang('Book Name') }}</th>
                                            <th>{{ _lang('Type') }}</th>
                                            <th>{{ _lang('Library ID') }}</th>
                                            <th>{{ _lang('Name') }}</th>
                                            <th>{{ _lang('Phone') }}</th>
                                            <th>{{ _lang('Issue Date') }}</th>
                                            <th>{{ _lang('Return Date') }}</th>
                                            <th>{{ _lang('Received Date') }}</th>
                                            <th>{{ _lang('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($issues as $data)
                                            <tr>
                                                <td>{{ $data->code }}</td>
                                                <td>{{ $data->book_name }}</td>
                                                <td>{{ $data->type }}</td>
                                                <td>
                                                    {{ $data->library_id }}
                                                </td>
                                                <td>
                                                    @if (!empty($data->student_id))
                                                        <span
                                                            style="text-transform: capitalize">{{ $data->student_first_name }}</span>
                                                        <span
                                                            style="text-transform: capitalize">{{ $data->student_last_name }}</span>
                                                    @elseif (!empty($data->teacher_id))
                                                        <span
                                                            style="text-transform: capitalize">{{ $data->teacher_name }}</span>
                                                    @else
                                                        <span
                                                            style="text-transform: capitalize">{{ $data->staff_name }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (!empty($data->student_id))
                                                        {{ $data->student_phone }}
                                                    @elseif (!empty($data->teacher_id))
                                                        {{ $data->teacher_phone }}
                                                    @else
                                                        {{ $data->staff_phone }}
                                                    @endif
                                                </td>
                                                <td>{{ $data->issue_date }}</td>
                                                <td>{{ $data->due_date }}</td>
                                                <td>{{ $data->return_date }}</td>
                                                <td>
                                                    @if ($data->status == '1')
                                                        <span class="badge badge-danger">{{ _lang('Open') }}</span>
                                                    @else
                                                        <span class="badge badge-success">{{ 'Returned' }}</span>
                                                    @endif
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
