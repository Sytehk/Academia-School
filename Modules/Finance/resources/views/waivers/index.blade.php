@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Waiver') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Waiver') }}
                    </span>
                </div>
                <div class="">

                    <form action="{{ route('waivers.store') }}"method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Waiver Name') }}</label>
                                <input type="text" class="form-control" name="waiver" required
                                    value="{{ old('waiver') }}" placeholder="Waiver">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Waiver List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Waiver') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($waivers as $waiver)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $waiver->waiver }}</td>

                                    <td>
                                        <form action="{{ route('waivers.destroy', $waiver->id) }}" method="post">
                                            <a href="{{ route('waivers.edit', $waiver->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm show_confirm"><i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
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
