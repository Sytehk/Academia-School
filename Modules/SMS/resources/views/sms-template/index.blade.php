@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('New Template') }}
                    </div>
                </div>

                <div class="">
                    <form action="{{ route('sms-template.store') }}"enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Title') }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" placeholder="SMS Title"
                                    value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Message') }}</label>
                            <div class="col-sm-9">
                                <textarea required class="form-control" name="description" placeholder="Message" cols="6" role="6">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('SMS Templates') }}
                    </div>
                </div>

                <div class="">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Template Title') }}</th>
                            <th>{{ _lang('Message') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @if (isset($smsTemplates))
                                @foreach ($smsTemplates as $template)
                                    <tr>

                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->description }}</td>
                                        <td>
                                            <form action="{{ route('sms-template.destroy', $template->id) }}"
                                                method="post" class="d-flex">
                                                @csrf
                                                @method('DELETE')

                                                <a href="{{ route('sms-template.edit', $template->id) }}"
                                                    class="btn btn-warning btn-sm mx-2"><i class="fa fa-pencil"
                                                        aria-hidden="true"></i>
                                                </a>

                                                @if ($template->id < 1 || $template->id > 7)
                                                    <button type="submit" class="btn btn-danger btn-sm btn-remove">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-danger">Not Found</td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
