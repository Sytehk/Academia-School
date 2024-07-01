@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Picklist') }}
@endsection
@section('create-button')
    <a href="{{ route('picklists.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Picklist') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">{{ _lang('Update Picklist') }}</div>

                <div class="">
                    <form method="post" action="{{ route('picklists.update', $id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Type') }}</label>
                                <select name="type" id="type" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>
                                    <option value="">{{ _lang('Select Type') }}</option>
                                    <option {{ old('type', $picklist->type) == 'Religion' ? 'selected' : '' }}>Religion</option>
                                    <option {{ old('type', $picklist->type) == 'Designation' ? 'selected' : '' }}>Designation</option>
                                    <option {{ old('type', $picklist->type) == 'Department' ? 'selected' : '' }}>Department</option>
                                    <option {{ old('type', $picklist->type) == 'Staff Designation' ? 'selected' : '' }}>Staff Designation</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Value') }}</label>
                                <input type="text" class="form-control" name="value" value="{{ $picklist->value }}"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js-script')
    <script>
        $("#type").val("{{ $picklist->type }}");
    </script>
@stop
