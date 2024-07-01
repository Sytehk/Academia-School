@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student List') }}
@endsection

@section('create-button')
    <a href="{{ route('students.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Student') }}
    </a>
@endsection
@section('content')
    <div class="card mb-5">
        @include('academic::students.modal.student-search-from', [
            'sections' => $sections,
        ])
    </div>

    <!--begin::Card-->
    @if (isset($students) && count($students) > 0)
        <div class="table-responsive mt-5">
            <form action="{{ route('students-multiple.delete') }}" method="POST" id="student-multiple-select">
                @csrf

                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <!--begin::Table head-->
                    <thead>
                        <!--begin::Table row-->
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <input class="form-check-input" type="checkbox" id="student-select-all">
                            </th>
                            <th class="">{{ _lang('Image') }}</th>
                            <th class="">{{ _lang('Roll') }}</th>
                            <th class="">{{ _lang('Admission Number') }}</th>
                            <th class="">{{ _lang('Name') }}</th>
                            <th class="">{{ _lang('Class') }}</th>
                            <th class="">{{ _lang('Section') }}</th>
                            <th class="">{{ _lang('Group') }}</th>
                            <th class="">{{ _lang('Phone No') }}</th>
                            <th class="">{{ _lang('Status') }}</th>
                            <th class="">{{ _lang('Action') }}</th>
                        </tr>
                        <!--end::Table row-->
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody class="fw-semibold text-gray-600">

                        @if (isset($students))
                            @foreach ($students as $data)
                                <tr>
                                    <!--begin::Checkbox-->
                                    <td>
                                        <input type="checkbox" class="form-check-input student-checkbox"
                                            name="student_ids[]" value="{{ $data['id'] }}">

                                        @error('student_ids')
                                            <span class="text-danger">Please cheked</span>
                                        @enderror
                                    </td>
                                    <!--end::Checkbox-->
                                    <!--begin::Name=-->
                                    <td>
                                        @if ($data['image'] != null)
                                            <img src="{{ $data['image'] ? asset('storage/users/' . $data['image']) : asset('assets/media/svg/files/blank-image.svg') }}"
                                                class="w-75px me-3" alt="">
                                        @else
                                            <img src="{{ asset('images/no-image.png') }}" class="w-75px me-3" alt="">
                                        @endif
                                    </td>
                                    <td>{{ $data['roll'] }}</td>
                                    <td>{{ $data['qr_code'] }}</td>
                                    <td>{{ $data['name'] }}</td>
                                    <td>{{ $data['class_name'] }}</td>
                                    <td>{{ $data['section_name'] }}</td>
                                    <td>{{ $data['group_name'] ?? '-' }}</td>
                                    <td>{{ $data['phone'] }}</td>
                                    <td>
                                        <span class="badge {{ $data['status'] == 1 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $data['status'] == 1 ? 'Enable' : 'Disable' }}
                                        </span>
                                    </td>

                                    <!--begin::Action=-->
                                    <td class="text-end">
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
                                                <a href="{{ route('students.show', $data['id']) }}" class="menu-link px-3">
                                                    {{ _lang('View') }}
                                                </a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="{{ route('students.edit', $data['id']) }}" class="menu-link px-3"
                                                    data-kt-customer-table-filter="delete_row">{{ _lang('Edit') }}</a>
                                            </div>
                                            <!--end::Menu item-->

                                            @if ($data['status'] == 1)
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('students.status-disable', $data['id']) }}"
                                                        class="menu-link px-3"
                                                        data-kt-customer-table-filter="delete_row">{{ _lang('Disable') }}</a>
                                                </div>
                                            @endif
                                            @if ($data['status'] == 0)
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('students.status-enable', $data['id']) }}"
                                                        class="menu-link px-3"
                                                        data-kt-customer-table-filter="delete_row">{{ _lang('Enable') }}</a>
                                                </div>
                                            @endif

                                        </div>
                                        <!--end::Menu-->
                                    </td>
                                    <!--end::Action=-->
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-danger">{{ _lang('No Students Found') }}</td>
                            </tr>
                        @endif
                    </tbody>
                    <!--end::Table body-->
                </table>
                <!--end::Table-->

                {{-- <!-- Pagination Links -->
                <div class="pagination">
                    {{ $students->appends(request()->query())->links() }}
                </div> --}}

                <button type="submit" class="btn btn-danger btn-sm btn-remove">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                    Delete Students</button>
            </form>
        </div>
    @endif
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            $("#student-select-all").click(function() {
                var isChecked = $(this).prop("checked");
                $(".student-checkbox").prop("checked", isChecked);
            });

            $(".student-checkbox").click(function() {
                var allChecked = $(".student-checkbox:checked").length === $(".student-checkbox").length;
                $("#student-select-all").prop("checked", allChecked);
            });

            var form = document.getElementById('student-multiple-select');

            // Check if the form exists before adding the event listener
            if (form) {
                form.addEventListener('submit', function(event) {
                    var atLeastOneChecked = [...document.querySelectorAll('.student-checkbox')].some(
                        checkbox => checkbox.checked);
                    if (!atLeastOneChecked) {
                        event.preventDefault();
                        alert('Please select at least one checkbox.');
                    }
                });
            }
        });


        $(document).ready(function() {
            $('#class_id_select').on('change', function() {

                let class_id = $(this).val();

                $.ajax({
                    url: "{{ url('sections-section') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        class_id: class_id
                    },

                    beforeSend: function() {
                        $("#preloader").show();
                    },

                    success: function(response) {

                        $("#preloader").hide();

                        $('#section_id_select').html(response);

                        $('#section_id_select').trigger('change');
                    },

                    error: function() {
                        $("#preloader").hide();
                    }
                });

            });

        });
    </script>
@stop
