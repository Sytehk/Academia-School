@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Phone Book') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('New Phone book') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('phone-book.store') }}"enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang(' Name') }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                    placeholder="Name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Phone') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="phone" value="{{ old('phone') }}"
                                    placeholder="Phone" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Category') }}</label>
                            <div class="col-sm-9">
                                <select name="category_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" id="" required>
                                    <option value="">{{ _lang('--Select--') }}</option>
                                    @foreach ($phoneBookCategories as $category)
                                        <option value="{{ (int) $category->id }}">
                                            {{ _lang($category->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Note') }}</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="note" placeholder="Note">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-default mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('phone-book.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">{{ _lang('Class') }}</label>
                            <select name="class_id" id="class_id_select" class="form-select">
                                <option value="">{{ _lang('All Classes') }}</option>
                                @foreach ($classes as $classItem)
                                    <option value="{{ $classItem->id }}" @selected(($filters['class_id'] ?? '') == $classItem->id)>{{ $classItem->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ _lang('Section') }}</label>
                            <select name="section_id" id="section_id_select" class="form-select" data-selected="{{ $filters['section_id'] ?? '' }}">
                                <option value="">{{ _lang('All Sections') }}</option>
                                @foreach ($sections ?? [] as $sectionItem)
                                    <option value="{{ $sectionItem->id }}" @selected(($filters['section_id'] ?? '') == $sectionItem->id)>{{ $sectionItem->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button class="btn btn-primary">{{ _lang('Filter') }}</button>
                            <a href="{{ route('phone-book.index') }}" class="btn btn-light">{{ _lang('Reset') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-default">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        {{ _lang('Phone Books') }}
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('phone-book.sync') }}" method="post" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fa fa-rotate"></i> {{ _lang('Sync from Students') }}
                            </button>
                        </form>
                        <a class="btn btn-sm btn-light" href="{{ route('phone-book.export-excel', request()->query()) }}">
                            <i class="fa fa-file-excel"></i> {{ _lang('Excel') }}
                        </a>
                        <a class="btn btn-sm btn-light" href="{{ route('phone-book.export-pdf', request()->query()) }}">
                            <i class="fa fa-file-pdf"></i> {{ _lang('PDF') }}
                        </a>
                    </div>
                </div>

                <div class="">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Contact Name') }}</th>
                            <th>{{ _lang('Contact Number') }}</th>
                            <th>{{ _lang('Contact Category') }}</th>
                            <th>{{ _lang('Class') }}</th>
                            <th>{{ _lang('Section') }}</th>
                            <th>{{ _lang('Note') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @if (isset($phoneBooks) && count($phoneBooks) > 0)
                                @foreach ($phoneBooks as $phoneBook)
                                    <tr>
                                        <td>{{ $phoneBook->name }}</td>
                                        <td>{{ $phoneBook->phone }}</td>
                                        <td>{{ $phoneBook->category?->name }}</td>
                                        <td>{{ $phoneBook->class?->class_name ?? '-' }}</td>
                                        <td>{{ $phoneBook->section?->section_name ?? '-' }}</td>
                                        <td>{{ $phoneBook->note }}</td>
                                        <td>
                                            <form action="{{ route('phone-book.destroy', $phoneBook->id) }}" method="post"
                                                class="d-flex">
                                                {{-- <a href="{{ route('phone-bookshow', $phoneBook->id) }}"
                                                    class="btn btn-primary btn-sm"><i class="fa fa-eye"
                                                        aria-hidden="true"></i></a> --}}
                                                <a href="{{ route('phone-book.edit', $phoneBook->id) }}"
                                                    class="btn btn-warning btn-sm mx-2"><i class="fa fa-pencil"
                                                        aria-hidden="true"></i></a>
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
                                                        class="fa fa-trash" aria-hidden="true"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-danger">Not Found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
