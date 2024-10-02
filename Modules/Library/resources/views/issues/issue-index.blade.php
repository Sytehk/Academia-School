@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Book Issues') }}
@endsection

@section('content')
    <style>
        .form-inline .form-control {
            width: 100px !important;
            text-align: center !important;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <form action="{{ url('bookissues-list') }}" method="post">
                    @csrf

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Library Id') }}</label>
                                <select name="library_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>

                                    @foreach ($library_members->unique('name') as $memberSingle)
                                        <option value="{{ $memberSingle->library_id }}"
                                            {{ $memberSingle->library_id == $library_id ? 'selected' : '' }}>
                                            {{ $memberSingle->library_id }} -
                                            {{ $memberSingle->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <input class="btn btn-primary btn-block rect-btn" style="margin-top:30px" value="Search"
                                type="submit">
                        </div>
                    </div>

                </form>
            </div>

            <div class="row my-5">
                @if (!empty($issues) && count($issues) > 0)
                    <div class="col-lg-12">
                        @if (!empty($member))
                            <div class="card-header text-center">
                                <div class="card-title">
                                    {{ _lang('Book Issues Of') }}<br>
                                    {{ _lang('Member Name - ') }}{{ $member->name }}<br>
                                    {{ _lang('Library Id - ') }}{{ $member->library_id }}<br>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('books-issue-multiple.return') }}" method="POST"
                            id="books-multiple-select">
                            @csrf

                            <div class="table-responsive mt-5">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="books-select-all">
                                            </th>
                                            <th>{{ _lang('Code') }}</th>
                                            <th>{{ _lang('Library ID') }}</th>
                                            <th>{{ _lang('Book Name') }}</th>
                                            <th>{{ _lang('Issue Date') }}</th>
                                            <th>{{ _lang('Submission Date') }}</th>
                                            <th>{{ _lang('Return Date') }}</th>
                                            <th>{{ _lang('Expaired Date') }}</th>
                                            <th>{{ _lang('Fine') }}</th>
                                            <th>{{ _lang('Lost Book') }}</th>
                                            <th>{{ _lang('Status') }}</th>
                                            <th>{{ _lang('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($issues as $data)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="student-checkbox" name="book_issues_ids[]"
                                                        value="{{ $data->id }}">
                                                </td>

                                                <td>{{ $data->code }}</td>
                                                <td>{{ $data->library_id }}</td>
                                                <td>{{ $data->book_name }}</td>
                                                <td>{{ date('d-M-Y', strtotime($data->issue_date)) }}</td>
                                                <td>{{ date('d-M-Y', strtotime($data->due_date)) }}</td>
                                                <td>
                                                    @if ($data->return_date != '')
                                                        {{ date('d-M-Y', strtotime($data->return_date)) }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @php
                                                        $expaireDate = 0;
                                                        $totalFine = 0;
                                                        if (now()->greaterThan($data->due_date)) {
                                                            $daysOverdue = now()->diffInDays($data->due_date);
                                                            $expaireDate = $daysOverdue;
                                                            $totalFine = $daysOverdue * 5;
                                                        }
                                                    @endphp

                                                    <input type="text" class="form-control" name="expaire_dates[]"
                                                        value="{{ number_format($expaireDate, 2) }}">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control" name="fines[]"
                                                        value="{{ number_format($totalFine, 2) }}">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control" name="lost_books[]"
                                                        value="0.0">
                                                </td>

                                                <td>
                                                    @if ($data->issue_status == 1)
                                                        <span class="badge badge-danger">{{ _lang('Not Return') }}</span>
                                                    @else
                                                        <span class="badge badge-success">{{ 'Returned' }}</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <a href="{{ route('bookissues.show', $data->id) }}"
                                                        class="btn btn-success btn-sm"><i class="fa fa-eye"
                                                            aria-hidden="true" title="edit book issue"></i></a>
                                                    <a href="{{ route('bookissues.edit', $data->id) }}"
                                                        class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                            aria-hidden="true" title="edit book issue"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>


                            <button type="submit" class="btn btn-success mt-5">
                                <i class="fa fa-share" aria-hidden="true"></i>
                                Return Books</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@section('js-script')
    <script>
        $(document).ready(function() {
            $("#books-select-all").click(function() {
                var isChecked = $(this).prop("checked");
                $(".student-checkbox").prop("checked", isChecked);
            });

            $(".student-checkbox").click(function() {
                var allChecked = $(".student-checkbox:checked").length === $(".student-checkbox").length;
                $("#books-select-all").prop("checked", allChecked);
            });

            var form = document.getElementById('books-multiple-select');

            form.addEventListener('submit', function(event) {
                var atLeastOneChecked = [...document.querySelectorAll('.student-checkbox')].some(
                    checkbox => checkbox.checked);
                if (!atLeastOneChecked) {
                    event.preventDefault();
                    alert('Please select at least one salary.');
                }
            });
        });
    </script>
@stop
