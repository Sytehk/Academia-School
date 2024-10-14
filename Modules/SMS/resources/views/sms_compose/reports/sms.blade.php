@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form method="POST" action="{{ route('send-sms-report.index') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('From Date') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $requestFromDate }}" required>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('To Date') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $requestToDate }}" required>
                        </div>

                    </div>

                    <div class="col-lg-3 mt-5">
                        <div class="form-group">
                            <button class="btn btn-success mt-4" type="submit">Search</button>
                        </div>
                    </div>
                </div>
            </form>

            @if (isset($smsReports) && count($smsReports) > 0)
                <div class="content">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Phone') }}</th>
                            <th>{{ _lang('User Type') }}</th>
                            <th>{{ _lang('Send Date') }}</th>
                            <th>{{ _lang('Message') }}</th>
                            <th>{{ _lang('SMS Count') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($smsReports as $item)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $item->receiver }}</td>
                                    <td>{{ $item->user_type }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}
                                    </td>
                                    <td>
                                        {{ \Illuminate\Support\Str::limit($item->message, $limit = 500, $end = '...') }}
                                    </td>
                                    <td style="text-align: center">
                                        {{ isset($item->message) && $item->message ? ceil(strlen($item->message) / 250) : 0 }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
