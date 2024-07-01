@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('teachers.bulk-file-upload') }}">
        {{ _lang('Bulk Teacher Upload') }}
    </a>
@endsection

@section('content')
    <form action="{{ route('teachers.update', $teacher->id) }}"method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <select name="class_id" class="form-control mt-3">
            <option value="">{{ _lang('Select Class') }}</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">
                    {{ $class->name ?? $class->class_name }}
                </option>
            @endforeach
        </select>

        <select name="section_id" class="form-control mt-3">
            <option value="">{{ _lang('Select Class First') }}</option>
        </select>

        <input name="topic" class="form-control mt-3" placeholder="Meeting Topic">

        <textarea name="agenda" class="form-control mt-3"></textarea>

        <input type="datetime-local" name="start_time" class="form-control mt-3">

        <input name="duration" class="form-control mt-3" placeholder="Minutes">

        <input name="password" class="form-control mt-3" placeholder="Password">

        <div class="form-group my-5">
            <button type="submit" class="btn btn-primary mt-5">Update Teacher</button>
        </div>
    </form>
@endsection
