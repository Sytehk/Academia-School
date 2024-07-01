@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Staffs List') }}
@endsection

@section('create-button')
    <a href="{{ route('staffs.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Staff') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @if (isset($staffs) && count($staffs) > 0)
                <div class="table-responsive">
                    <form action="{{ route('staffs-multiple.delete') }}" method="POST" id="teacher-multiple-select">
                        @csrf

                        <table class="table align-middle table-row-dashed fs-6 gy-5 data-table" data-server-paginated="true">
                            <thead>
                                <th width="3%">
                                    <input class="form-check-input" type="checkbox" id="teacher-select-all">
                                </th>
                                <th>{{ _lang('Profile') }}</th>
                                <th>{{ _lang('Name') }}</th>
                                <th>{{ _lang('Phone') }}</th>
                                <th>{{ _lang('Email') }}</th>
                                <th>{{ _lang('Designation') }}</th>
                                <th>{{ _lang('Blood Group') }}</th>
                                <th>{{ _lang('Status') }}</th>
                                <th>{{ _lang('Action') }}</th>
                            </thead>
                            <tbody>
                                @foreach ($staffs->unique('name') as $data)
                                    <tr>
                                        <td width="3%">
                                            <input type="checkbox" class="form-check-input teacher-checkbox"
                                                name="staff_ids[]" value="{{ $data->id }}">

                                            @error('staff_ids')
                                                <span class="text-danger">Please cheked</span>
                                            @enderror
                                        </td>
                                        <td>
                                            @if ($data['image'])
                                                <img src="{{ $data['image'] ? asset('storage/users/' . $data['image']) : asset('assets/media/svg/files/blank-image.svg') }}"
                                                    class="me-3" alt="" width="50px">
                                            @else
                                                <img src="{{ asset('uploads/images/' . $data['image']) }}" width="50px"
                                                    alt="">
                                            @endif
                                        </td>
                                        <td>{{ $data->name }}</td>
                                        <td>{{ $data['user_phone'] }}</td>
                                        <td>{{ $data->email }}</td>
                                        <td>{{ $data->designation }}</td>
                                        <td>{{ $data['blood'] }}</td>
                                        <td>
                                            <span class="badge {{ $data->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-light btn-active-light-primary"
                                                data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr072.svg-->
                                                <span class="svg-icon svg-icon-5 m-0">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z"
                                                            fill="currentColor" />
                                                    </svg>
                                                </span>
                                                <!--end::Svg Icon--></a>
                                            <!--begin::Menu-->
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                                data-kt-menu="true">
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('staffs.show', $data->id) }}" class="menu-link px-3">
                                                        {{ _lang('View') }}
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('staffs.edit', $data->id) }}" class="menu-link px-3"
                                                        data-kt-customer-table-filter="delete_row">{{ _lang('Edit') }}</a>
                                                </div>
                                                <!--end::Menu item-->
                                            </div>
                                            <!--end::Menu-->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <button type="submit" name="checked_delete" class="btn btn-danger btn-sm btn-remove">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            Delete Staffs</button>
                    </form>
                    <div class="p-3">{{ $staffs->appends(request()->query())->links() }}</div>
                </div>

            @endif
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            $("#teacher-select-all").click(function() {
                var isChecked = $(this).prop("checked");
                $(".teacher-checkbox").prop("checked", isChecked);
            });

            $(".teacher-checkbox").click(function() {
                var allChecked = $(".teacher-checkbox:checked").length === $(".teacher-checkbox").length;
                $("#teacher-select-all").prop("checked", allChecked);
            });

            var form = document.getElementById('teacher-multiple-select');

            form.addEventListener('submit', function(event) {
                var atLeastOneChecked = [...document.querySelectorAll('.teacher-checkbox')].some(
                    checkbox => checkbox.checked);
                if (!atLeastOneChecked) {
                    event.preventDefault();
                    alert('Please select at least one salary.');
                }
            });
        });
    </script>
@stop
