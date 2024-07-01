<form method="get">
    @csrf

    <div class="row">

        {{-- CLASS --}}
        <div class="col-sm-3">
            <div class="form-group">
                <label class="control-label mb-2">
                    {{ _lang('Class') }}
                </label>

                <select name="class_id" id="class_id_select" class="form-select form-select-sm form-select-solid"
                    data-control="select2" required>

                    <option value="">Select Class</option>

                    {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}

                </select>
            </div>
        </div>

        {{-- SECTION --}}
        <div class="col-sm-3">
            <div class="form-group">
                <label class="control-label mb-2">
                    {{ _lang('Section') }}
                </label>

                <select name="section_id" id="section_id_select" class="form-select form-select-sm form-select-solid"
                    data-control="select2" data-selected="{{ request()->section_id }}">
                    <option value="">{{ _lang('Select Class First') }}</option>
                </select>
            </div>
        </div>

        {{-- BUTTON --}}
        <div class="col-sm-3">
            <div class="form-group">
                <button type="submit" class="btn btn-primary margin_top_24">
                    {{ _lang('Search') }}
                </button>
            </div>
        </div>

    </div>
</form>
