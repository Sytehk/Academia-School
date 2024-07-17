@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Policies') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($policies as $policy)
                            <tr>
                                <td>{{ $policy->id }}</td>
                                <td>{{ \Modules\Frontend\Models\Policy::titleFor((int) $policy->type) }}</td>
                                <td>
                                    @if ($policy->status == 1)
                                        <span class="badge bg-success">{{ _lang('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ _lang('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ Str::limit(strip_tags($policy->description), 60) }}
                                </td>
                                <td>
                                    <a href="{{ route('policies.edit', $policy->id) }}" class="btn btn-warning btn-sm">
                                        {{ _lang('Edit') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>
@endsection
