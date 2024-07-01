@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Signature') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Signature') }}
                    </span>
                </div>
                <div class="">

                    <form action="{{ route('signatures.store') }}"enctype="multipart/form-data" method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Place At') }}</label>
                                <input type="text" class="form-control" name="place_at" required
                                    value="{{ old('place_at') }}" placeholder="Place At">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Title') }}</label>
                                <input type="text" class="form-control" name="title" required
                                    value="{{ old('title') }}" placeholder="Title">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Signature') }}</label>
                                <input type="file" class="form-control dropify" name="image"
                                    data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Signature') }}</button>
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
                        {{ _lang('Signature List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Place At') }}</th>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Signature') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($signatures as $data)
                                <tr>
                                    <td>{{ $data->place_at }}</td>
                                    <td>{{ $data->title }}</td>
                                    <td>
                                        <img src="{{ $data['image'] ? asset('storage/signatures/' . $data['image']) : asset('assets/media/svg/files/blank-image.svg') }}"
                                            class="me-3" alt="" width="50px">
                                    </td>
                                    <td>
                                        <form action="{{ route('signatures.destroy', $data->id) }}" method="post">
                                            <a href="{{ route('signatures.edit', $data->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
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

@section('js-script')
    <script>
        function showClass(elem) {
            if ($(elem).val() == "") {
                return;
            }
            window.location = "<?php echo url('signatures/class'); ?>/" + $(elem).val();
        }
    </script>
@stop
