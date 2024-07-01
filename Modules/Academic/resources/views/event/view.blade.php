@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Event') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('events.index') }}">
        {{ _lang('Events List') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">{{ _lang('View Event') }}</div>

                <div class="">
                    <table class="table table-bordered">
                        <tr>
                            <td>{{ _lang('Start Date') }}</td>
                            <td>{{ date('d M, Y - H:i', strtotime($event->start_date)) }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('End Date') }}</td>
                            <td>{{ date('d M, Y - H:i', strtotime($event->end_date)) }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Name') }}</td>
                            <td>{{ $event->name }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Details') }}</td>
                            <td>{!! $event->details !!}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Location') }}</td>
                            <td>{{ $event->location }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
