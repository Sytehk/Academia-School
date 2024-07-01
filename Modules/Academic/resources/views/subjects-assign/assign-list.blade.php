@if ($subjects->isEmpty())
    <tr>
        <td colspan="2" class="text-center">No subjects found.</td>
    </tr>
@else
    @foreach ($subjects as $data)
        @php
            $assign = $data->assign; // may be null
        @endphp

        <tr>
            <td>{{ $data->subject_name }}</td>
            <td>
                <input type="hidden" name="subject_id[]" value="{{ $data->id }}">
                <input type="hidden" name="section_id[]" value="{{ $sectionId }}">

                <select name="teacher_id[]" class="form-select form-select-sm" required>
                    {!! create_option('teachers', 'id', 'name', $assign->teacher_id ?? null) !!}
                </select>
            </td>
        </tr>
    @endforeach

    <tr>
        <td colspan="2" class="text-center">
            <button type="button" id="assign_teacher" class="btn btn-primary">
                Save
            </button>
        </td>
    </tr>
@endif
