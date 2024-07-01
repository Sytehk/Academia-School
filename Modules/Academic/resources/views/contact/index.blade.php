@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Contact') }}
@endsection
@section('content')
    <style>
        .highlight-row {
            background-color: gray !important;
            color: #fff !important;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">
                    <span class="card-title">{{ _lang('Message List') }}</span>

                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Phone') }}</th>
                            <th>{{ _lang('Email') }}</th>
                            <th>{{ _lang('Message') }}</th>
                            <th>{{ _lang('Date') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($contactsMessage as $message)
                                @php
                                    $clickAbleMessageId = $id;
                                    $highlightClass = $clickAbleMessageId == $message->id ? 'highlight-row' : '';
                                    // $grayShadowClass = $message->isSeen === 1 ? 'gray-shadow' : '';  {{ $grayShadowClass }}
                                @endphp
                                <tr class="{{ $highlightClass }}">
                                    <td>{{ $message->name }}</td>
                                    <td>{{ $message->phone }}</td>
                                    <td>{{ $message->email }}</td>
                                    <td>{{ $message->message }}</td>
                                    <td>{{ $message->created_at }}</td>

                                    <td>
                                        @if ($message->isSeen === 1)
                                            <span class='text-info text-uppercase'>{{ _lang('Read') }}</span>
                                        @else
                                            <span class='text-danger text-uppercase'>{{ _lang('Unread') }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($message->isSeen === 1)
                                            <i class="fa fa-check text-success" aria-hidden="true" title="read"></i>
                                        @else
                                            <a href="{{ route('contact-message-change-status', $message->id) }}"
                                                class="btn btn-warning btn-sm" title="unread">
                                                <i class="fa fa-inbox" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
