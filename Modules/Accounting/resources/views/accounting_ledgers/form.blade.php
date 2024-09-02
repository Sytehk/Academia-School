@php
    $route = isset($id) ? route('accounting-ledgers.update', $accountingLedger->id) : route('accounting-ledgers.store');
    $accountingLedger = isset($accountingLedger) ? $accountingLedger : null;
    $accountingGroups = $accountingGroups ?? [];
    $selectedGroupId = old('accounting_group_id', $accountingLedger->accounting_group_id ?? '');
    $selectedCategoryId = old('accounting_category_id', $accountingLedger->accounting_category_id ?? '');
@endphp

<div class="row">
    <div class="col-lg-12">
        <form action="{{ $route }}" method="post">
            @csrf

            @isset($id)
                @method('PATCH')
            @endisset

            <div class="form-group">
                <label class="control-label my-2">{{ _lang('Category') }}</label>

                <select class="form-select form-select-sm form-select-solid" data-control="select2"
                    name="accounting_category_id" id="accounting_category_select" required>
                    <option value="">--Select Category--</option>
                    @foreach ($accountingCategories as $accountingCategory)
                        <option value="{{ $accountingCategory->id }}"
                            @selected($selectedCategoryId == $accountingCategory->id)>
                            {{ $accountingCategory->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="control-label my-2">{{ _lang('Group') }}</label>

                <select name="accounting_group_id" id="accounting_group_select"
                    class="form-select form-select-sm form-select-solid" data-control="select2" required>
                    <option value="">--Select Group--</option>
                    @foreach ($accountingGroups as $groupId => $groupName)
                        <option value="{{ $groupId }}" @selected($selectedGroupId == $groupId)>
                            {{ $groupName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="control-label my-2">{{ _lang('Ledger Name') }}</label>
                <input type="text" class="form-control" name="ledger_name" required
                    value="{{ get_old_value($accountingLedger, 'ledger_name') }}" placeholder="Ledger Name">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
            </div>
        </form>
    </div>
</div>

@section('js-script')
    <script>
        $(document).ready(function() {
            var groupsUrl = @json(url('get-account-groups'));
            var selectedGroupId = @json((string) $selectedGroupId);

            function loadAccountGroups(categoryId, selectedId) {
                var groupSelect = $('#accounting_group_select');

                if (!categoryId) {
                    groupSelect.empty().append('<option value="">--Select Group--</option>');
                    return;
                }

                $.ajax({
                    url: groupsUrl + '/' + categoryId,
                    type: 'GET',
                    success: function(data) {
                        groupSelect.empty().append('<option value="">--Select Group--</option>');

                        $.each(data, function(key, value) {
                            var selected = String(selectedId) === String(key) ? ' selected' : '';
                            groupSelect.append('<option value="' + key + '"' + selected + '>' +
                                value + '</option>');
                        });
                    }
                });
            }

            $('#accounting_category_select').on('change', function() {
                loadAccountGroups($(this).val(), '');
            });
        });
    </script>
@endsection
