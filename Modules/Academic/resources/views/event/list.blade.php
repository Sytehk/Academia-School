@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Event') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('events.create') }}">
        {{ _lang('Create') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive mt-5">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Image') }}</th>
                            <th>{{ _lang('Start Date') }}</th>
                            <th>{{ _lang('End Date') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Location') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($events as $event)
                            <tr id="row_{{ $event->id }}">
                                <td>
                                    @if ($event->image)
                                        <img src="{{ $event->image ? asset('storage/events/' . $event->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                            class="me-3" alt="" width="50px">
                                    @else
                                        <img src="{{ asset('uploads/images/' . $event->image) }}" width="50px"
                                            alt="">
                                    @endif
                                </td>

                                <td class='start_date'>{{ date('d/M/Y - H:i', strtotime($event->start_date)) }}</td>
                                <td class='end_date'>{{ date('d/M/Y - H:i', strtotime($event->end_date)) }}</td>
                                <td class='name'>{{ $event->name }}</td>
                                <td class='location'>{{ $event->location }}</td>
                                <td>
                                    <form action="{{ route('events.destroy', $event['id']) }}" method="post">
                                        <a href="{{ route('events.edit', $event['id']) }}"
                                            class="btn btn-warning btn-sm">{{ _lang('Edit') }}</a>
                                        <a href="{{ route('events.show', $event['id']) }}"
                                            data-title="{{ _lang('View Event') }}"
                                            class="btn btn-primary btn-sm ajax-modal">{{ _lang('View') }}</a>
                                        @csrf
                                        <input name="_method" type="hidden" value="DELETE">
                                        <button class="btn btn-danger btn-sm btn-remove"
                                            type="submit">{{ _lang('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
