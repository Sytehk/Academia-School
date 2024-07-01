@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Notice') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('notices.create') }}">
        {{ _lang('Create') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-bordered data-table">
                <thead>
                    <tr>
                        <th>{{ _lang('Image') }}</th>
                        <th>{{ _lang('Title') }}</th>
                        <th>{{ _lang('User Type') }}</th>
                        <th>{{ _lang('Notice') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($notices as $notice)
                        <tr id="row_{{ $notice->id }}">

                            <td>
                                @if ($notice->image)
                                    <img src="{{ $notice->image ? asset('storage/notices/' . $notice->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                        class="me-3" alt="" width="50px">
                                @else
                                    <img src="{{ asset('uploads/images/' . $notice->image) }}" width="50px"
                                        alt="">
                                @endif
                            </td>

                            <td class='heading'>{{ $notice->title }}</td>
                            <td class='user_type'>
                                {{ object_to_string($notice->userType, 'user_type') }}
                            </td>
                            <td>
                                {{ Str::limit($notice->notice, 20) }}
                            </td>

                            <td>
                                <form action="{{ route('notices.destroy', $notice['id']) }}" method="post">
                                    <a href="{{ route('notices.edit', $notice['id']) }}"
                                        class="btn btn-warning btn-sm">{{ _lang('Edit') }}</a>
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
