@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Academic/Session Year') }}
@endsection

@section('create-button')
    <a href="{{ route('academic-years.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Session') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
          <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                <thead>
                    <tr>
                        <th>{{ _lang('Session Name') }}</th>
                        <th>{{ _lang('Academic Year') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $current = get_option('academic_year'); @endphp
                    @foreach ($academicYears as $academicYear)
                        <tr id="row_{{ $academicYear->id }}">
                            <td class='session'>{{ $academicYear->session }}
                                {{ $academicYear->id == $current ? '(Active)' : '' }}</td>
                            <td class='year'>{{ $academicYear->year }}</td>

                            <td>
                                <form action="{{ route('academic-years.destroy', $academicYear['id']) }}" method="post">
                                    <a href="{{ route('academic-years.edit', $academicYear['id']) }}"
                                        data-title="{{ _lang('Update Academic Year') }}"
                                        class="btn btn-warning btn-sm ajax-modal">{{ _lang('Edit') }}</a>
                                    @csrf
                                    <input name="_method" type="hidden" value="DELETE">
                                    <button class="btn btn-danger btn-sm btn-remove"
                                        type="submit">{{ _lang('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
          </div>
        </div>
    </div>
@endsection
