@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Groups') }}
@endsection

@section('create-button')
    <a href="{{ route('student-groups.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Student Group') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Group Name') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($studentgroups as $studentgroup)
                            <tr>
                                <td class='id'>{{ $studentgroup->id }}</td>
                                <td class='group_name'>{{ $studentgroup->group_name }}</td>

                                <td>
                                    <form action="{{ route('student-groups.destroy', $studentgroup['id']) }}" method="post">
                                        <a href="{{ route('student-groups.edit', $studentgroup['id']) }}"
                                            data-title="{{ _lang('Update Student Group') }}"
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
