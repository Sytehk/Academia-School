@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Picklist') }}
@endsection
@section('create-button')
    <a href="{{ route('picklists.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="control-label my-2">
                                <strong>{{ _lang('Select Picklist') }}</strong>
                            </label>
                            <select id="type" class="form-select form-select-sm form-select-solid"
                                data-control="select2" onchange="show(this);">
                                <option value="" disabled>{{ _lang('-- Select Type --') }}</option>
                                <option value="Religion">Religion</option>
                                <option value="Designation">Designation</option>
                                <option value="Staff Designation">Staff Designation</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ _lang('Type') }}</th>
                                <th>{{ _lang('Value') }}</th>
                                <th>{{ _lang('Slug') }}</th>
                                <th>{{ _lang('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($picklists as $picklist)
                                <tr>
                                    <td class='id'>{{ $loop->index + 1 }}</td>
                                    <td class='type'>{{ $picklist->type }}</td>
                                    <td class='value'>{{ $picklist->value }}</td>
                                    <td class='type'>{{ $picklist->slug }}</td>

                                    <td>
                                        <form action="{{ route('picklists.destroy', $picklist['id']) }}" method="post">
                                            <a href="{{ route('picklists.edit', $picklist['id']) }}"
                                                data-title="{{ _lang('Update Picklist') }}"
                                                class="btn btn-warning btn-sm ajax-modal">{{ _lang('Edit') }}</a>
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
    </div>
@endsection

@section('js-script')
    <script>
        $("#type").val("{{ $type }}");

        function show(elem) {
            if ($(elem).val() == "") {
                return;
            }
            window.location = "<?php echo url('picklists-type'); ?>/" + $(elem).val();
        }
    </script>
@stop
