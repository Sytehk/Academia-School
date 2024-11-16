@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher ID Cards') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Print Teacher ID Cards') }}</h3>
                </div>
                <div class="card-body">
                    {{-- Plain GET form to the print route (opened in a new tab, matching
                         the sidebar link's existing target="_blank"): no JS needed, the
                         browser builds department_id/validity into the query string
                         itself. Department is optional -- "All Departments" prints
                         every teacher, same as the old unconditional behaviour, but now
                         as a deliberate choice rather than the only option. --}}
                    <form method="get" action="{{ route('teacher.id.card.print') }}" target="_blank">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label mb-2">{{ _lang('Department') }}</label>
                                    <select name="department_id" class="form-select form-select-solid"
                                        data-control="select2">
                                        <option value="">{{ _lang('All Departments') }}</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label mb-2">{{ _lang('Card Validity Date') }}</label>
                                    <input type="date" class="form-control" name="validity">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary">
                                {{ _lang('Print ID Cards') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
