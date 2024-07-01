<table class="table table-bordered">
    <thead>
        <tr>
            <th><input type="checkbox" id="student-select-all"></th>
            <th>SL</th>
            <th>Name</th>
            <th>Roll</th>
            <th>Reg No</th>
            <th>Class</th>
            <th>Section</th>
            <th>Group</th>
            <th>New Roll</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($students as $key => $item)
            <tr>
                <td>
                    <input type="checkbox" class="student-checkbox" name="users[]" value="{{ $item->id }}">
                </td>

                <td>{{ $loop->iteration }}</td>

                <td>
                    @if ($item->user?->image != null)
                        <img src="{{ $item->user?->image ? asset('storage/users/' . $item->user?->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                            class="w-75px me-3" alt="">
                    @else
                        <img src="{{ asset('images/no-image.png') }}" class="w-75px me-3">
                    @endif
                    {{ $item->first_name }} {{ $item->last_name }}
                </td>

                <td>
                    {{ $item->roll }}
                    <input type="hidden" name="prev_roll[]" value="{{ $item->roll }}">
                </td>

                <td>
                    {{ $item->registration_no }}
                </td>

                <td>{{ $item->studentSession->class?->class_name }}</td>

                <td>{{ $item->studentSession->section?->section_name }}</td>

                <td>{{ $item->studentSession?->group?->group_name }}</td>

                <td>
                    <input type="number" name="new_roll[]" value="{{ $item->roll }}" class="form-control">

                    <input type="hidden" name="session_table_id[]" value="{{ $item->studentSession->id }}">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
