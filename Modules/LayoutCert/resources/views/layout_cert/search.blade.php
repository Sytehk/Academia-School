@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $typeLabel ?? _lang('Certificate') }}
@endsection

@section('content')
    <form action="{{ route('layout-cert.store') }}" method="POST">
        @csrf

        <input type="hidden" name="type" value="{{ $type }}">

        <div class="row g-3">

            {{-- CLASS --}}
            <div class="col-md-3">
                <label>{{ _lang('Class') }}</label>
                <select name="class_id" id="class_id" class="form-select" data-control="select2" required>
                    <option value="">{{ _lang('Select Class') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((string) old('class_id') === (string) $class->id)>
                            {{ $class->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- SECTION --}}
            <div class="col-md-3">
                <label>{{ _lang('Section') }}</label>
                <select name="section_id" id="section_id" class="form-select" data-control="select2"
                    data-selected="{{ old('section_id') }}" required>
                    <option value="">{{ _lang('Select Section') }}</option>
                </select>
            </div>

            {{-- STUDENT (value = roll) --}}
            <div class="col-md-4">
                <label>{{ _lang('Student') }}</label>
                <select name="roll" id="roll" class="form-select" data-control="select2"
                    data-placeholder="{{ _lang('Search by name or roll') }}" required>
                    <option value="">{{ _lang('Select Class and Section first') }}</option>
                </select>
            </div>

        </div>

        <div class="mt-3">
            <button class="btn btn-primary">{{ _lang('Search Student') }}</button>
        </div>
    </form>
@endsection

@section('js-script')
    <script>
        (function() {
            var studentsUrl = @json(route('layout-cert.students'));
            var csrf = @json(csrf_token());
            var selectedRoll = @json(old('roll', ''));
            var selectFirst = @json(_lang('Select Class and Section first'));
            var loadingLabel = @json(_lang('Loading...'));
            var unableLabel = @json(_lang('Unable to load students'));

            function $roll() {
                return $('#roll');
            }

            function resetStudents(placeholder) {
                $roll().html('<option value="">' + placeholder + '</option>').val('').trigger('change');
            }

            function loadStudents() {
                var classId = $('#class_id').val();
                var sectionId = $('#section_id').val();

                if (!classId || !sectionId) {
                    resetStudents(selectFirst);
                    return;
                }

                resetStudents(loadingLabel);

                $.ajax({
                    url: studentsUrl,
                    type: 'POST',
                    data: {
                        _token: csrf,
                        class_id: classId,
                        section_id: sectionId
                    },
                    success: function(html) {
                        $roll().html(html);
                        if (selectedRoll) {
                            $roll().val(selectedRoll);
                            if ($roll().val() === selectedRoll) {
                                selectedRoll = '';
                            }
                        }
                        $roll().trigger('change');
                    },
                    error: function() {
                        resetStudents(unableLabel);
                    }
                });
            }

            $('#class_id').on('change', function() {
                resetStudents(selectFirst);
            });

            $('#section_id').on('change', function() {
                loadStudents();
            });

            if ($('#class_id').val() && $('#section_id').val()) {
                loadStudents();
            }
        })();
    </script>
@endsection
