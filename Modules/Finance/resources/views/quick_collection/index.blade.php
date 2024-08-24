@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Quick Collection') }}
@endsection

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <form method="get">
                @csrf

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Class') }}</label>
                            <select name="class_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                                onChange="getData(this.value);" required>
                                <option value="">{{ _lang('Select Class') }}</option>
                                {!! create_option('classes', 'id', 'class_name', $classId) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Section') }}</label>
                            <select name="section_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" data-selected="{{ $sectionId ?? '' }}">
                                <option value="">{{ _lang('Select Class First') }}</option>
                            </select>

                        </div>
                    </div>

                    <div class="col-lg-4 d-flex align-items-end custom-margin">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary">{{ _lang('Search') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        @if (isset($students) && count($students) > 0)


            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ _lang('Student ID') }}</th>
                                <th>{{ _lang('Roll No') }}</th>
                                <th>{{ _lang('Name') }}</th>
                                <th>{{ _lang('Class') }}</th>
                                <th>{{ _lang('Group') }}</th>
                                <th>{{ _lang('Category') }}</th>
                                <th>{{ _lang('Action') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($students as $student)
                                @if ((int) $student->student_status === 1)
                                    <tr>
                                        <td>{{ $student->id }}</td>
                                        <td>{{ $student->roll }}</td>
                                        <td>
                                            @if ($student->image != null)
                                                <img src="{{ $student->image ? asset('storage/users/' . $student->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                                    class="w-75px me-3" alt="">
                                            @else
                                                <img src="{{ asset('images/no-image.png') }}" class="w-75px me-3">
                                            @endif
                                            {{ $student->name }}
                                        </td>
                                        <td>{{ $student->class_name }}</td>
                                        <td>{{ $student->group_name }}</td>
                                        <td>
                                            {{ $student->studentCategory?->name }}
                                        </td>
                                        <td>
                                            <a href="{{ route('quick-collection.show', $student->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="fa fa-cart-plus" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

@endsection

@section('js-script')
    <script type="text/javascript">
        function getData(val) {
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();
            $.ajax({
                type: "POST",
                url: "{{ url('sections-section') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                beforeSend: function() {
                    $("#preloader").css("display", "block");
                },
                success: function(sections) {
                    $("#preloader").css("display", "none");
                    $('select[name=section_id]').html(sections);
                }
            });
        }
    </script>
@stop
