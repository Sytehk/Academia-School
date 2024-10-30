@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Certificate Templates') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="card-title">{{ _lang('Certificate Templates') }}</span>
                    <a href="{{ route('certificate-templates.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> {{ _lang('New Template') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Name') }}</th>
                                    <th>{{ _lang('Certificate Type') }}</th>
                                    <th>{{ _lang('Orientation') }}</th>
                                    <th>{{ _lang('Colors') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th class="text-end">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($templates as $template)
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $types[$template->type] ?? $template->type }}</td>
                                        <td class="text-capitalize">{{ $template->orientation }}</td>
                                        <td>
                                            <span title="{{ _lang('Primary') }}"
                                                style="display:inline-block; width:16px; height:16px; border-radius:50%; background:{{ $template->primary_color }}; vertical-align:middle;"></span>
                                            <span title="{{ _lang('Secondary') }}"
                                                style="display:inline-block; width:16px; height:16px; border-radius:50%; background:{{ $template->secondary_color }}; vertical-align:middle;"></span>
                                        </td>
                                        <td>
                                            @if ($template->is_active)
                                                <span class="badge bg-success">{{ _lang('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ _lang('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('certificate-templates.edit', $template->id) }}"
                                                class="btn btn-sm btn-outline-primary">{{ _lang('Edit') }}</a>

                                            @unless ($template->is_active)
                                                <form action="{{ route('certificate-templates.activate', $template->id) }}"
                                                    method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-success">{{ _lang('Activate') }}</button>
                                                </form>
                                            @endunless

                                            <form action="{{ route('certificate-templates.duplicate', $template->id) }}"
                                                method="post" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-secondary">{{ _lang('Duplicate') }}</button>
                                            </form>

                                            <form action="{{ route('certificate-templates.destroy', $template->id) }}"
                                                method="post" class="d-inline"
                                                onsubmit="return confirm('{{ _lang('Delete this template?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger">{{ _lang('Delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            {{ _lang('No templates yet. Every certificate type uses a sensible built-in default until you create one.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
