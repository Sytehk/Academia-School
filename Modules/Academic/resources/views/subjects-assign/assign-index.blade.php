@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Subject Assign') }}
@endsection

@section('content')
    <div class="row">
        <!-- Filter Form -->
        <div class="col-lg-4">
            <div class="card p-3">
                <form id="search_form" action="{{ route('assign-subjects.search') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ _lang('Class') }}</label>
                        <select name="class_id" class="form-select" onchange="getSections(this.value)" required>
                            <option value="">Select Class</option>
                            {!! create_option('classes', 'id', 'class_name') !!}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ _lang('Section') }}</label>
                        <select name="section_id" class="form-select" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </form>
            </div>
        </div>

        <!-- Assign Table -->
        <div class="col-lg-8">
            <div class="card p-3">
                <form id="assign_teacher_form" action="{{ route('assign-subjects.store') }}" method="post">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Subject') }}</th>
                                    <th>{{ _lang('Teacher') }}</th>
                                </tr>
                            </thead>
                            <tbody id="list">
                                <tr>
                                    <td colspan="2" class="text-center">Select class & section to load subjects.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        function getSections(classId) {
            var _token = $('input[name=_token]').val();
            $.post("{{ url('sections-section') }}", {
                _token: _token,
                class_id: classId
            }, function(sections) {
                $('select[name=section_id]').html(sections);
            });
        }

        $('#search_form').on('submit', function(e) {
            e.preventDefault();
            $.post($(this).attr('action'), $(this).serialize(), function(data) {
                $('#list').html(data);
                $('.form-select').select2(); // reinitialize select2 if used
            });
        });

        $(document).on('click', '#assign_teacher', function() {
            var btn = $(this);
            btn.prop('disabled', true).text('Saving...');

            $.post($('#assign_teacher_form').attr('action'),
                $('#assign_teacher_form').serialize(),
                function(response) {
                    btn.prop('disabled', false).text('Save');

                    toastr.success('Subjects assigned successfully.');
                }
            ).fail(function() {
                btn.prop('disabled', false).text('Save');
                toastr.error('Something went wrong!');
            });
        });
    </script>
@endsection
