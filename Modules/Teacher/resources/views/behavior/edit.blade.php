@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ ucfirst('behavior') }}
@endsection

@section('create-button')
    <a href="{{ route('behaviors.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('behavior') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('behaviors.update', $behavior->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row">

                    {{-- TITLE --}}
                    <div class="col-md-6 mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control"
                            value="{{ old('title', $behavior->title) }}" required>
                    </div>

                    {{-- DATE --}}
                    <div class="col-md-6 mb-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', $behavior->date) }}">
                    </div>

                    {{-- CLASS --}}
                    <div class="col-md-6 mb-3">
                        <label>Class</label>
                        <select name="class_id" class="form-select" data-control="select2"
                            onchange="getSections(this.value)" required>

                            <option value="">Select Class</option>

                            {!! create_option('classes', 'id', 'class_name', $behavior->class_id) !!}
                        </select>
                    </div>

                    {{-- SECTION --}}
                    <div class="col-md-6 mb-3">
                        <label>Section</label>
                        <select name="section_id" class="form-select" data-control="select2" onchange="getStudents()">

                            <option value="">Select Section</option>

                            @if ($behavior->section_id)
                                <option value="{{ $behavior->section_id }}" selected>
                                    Current Section
                                </option>
                            @endif
                        </select>
                    </div>

                    {{-- STUDENT --}}
                    <div class="col-md-12 mb-3">
                        <label>Student</label>
                        <select name="student_id" id="student_id" class="form-select" data-control="select2" required>

                            @if ($behavior->student)
                                <option value="{{ $behavior->student_id }}" selected>
                                    {{ $behavior->student->first_name }}
                                    {{ $behavior->student->last_name }}
                                </option>
                            @else
                                <option value="">Select Student</option>
                            @endif
                        </select>
                    </div>

                    {{-- NOTE --}}
                    <div class="col-md-12 mb-3">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="4">{{ old('note', $behavior->note) }}</textarea>
                    </div>

                    {{-- FILE --}}
                    <div class="col-md-12 mb-3">
                        <label>Attachment</label>

                        <input type="file" name="file" class="form-control">

                        @if ($behavior->file)
                            <a href="{{ asset('uploads/files/behaviors/' . $behavior->file) }}" target="_blank"
                                class="btn btn-sm btn-info mt-2">
                                View Current File
                            </a>
                        @endif
                    </div>

                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                </div>

            </form>

        </div>
    </div>
@endsection
