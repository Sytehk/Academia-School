@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Assessment Domains') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">{{ _lang('Add Domain') }}</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('assessment-domains.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ _lang('Domain') }}</label>
                            <select name="domain_type" class="form-select" required>
                                <option value="cognitive">{{ _lang('Cognitive') }}</option>
                                <option value="affective">{{ _lang('Affective') }}</option>
                                <option value="psychomotor">{{ _lang('Psychomotor') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ _lang('Name') }}</label>
                            <input name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ _lang('Display order') }}</label>
                            <input type="number" name="display_order" class="form-control" value="1">
                        </div>
                        <button class="btn btn-primary">{{ _lang('Save Domain') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            @foreach ($domains as $domain)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title">{{ ucfirst($domain->domain_type) }} — {{ $domain->name }}</h3>
                        <form method="POST" action="{{ route('assessment-domains.destroy', $domain->id) }}" onsubmit="return confirm('Delete this domain?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">{{ _lang('Delete') }}</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Item') }}</th>
                                    <th>{{ _lang('Max') }}</th>
                                    <th>{{ _lang('Type') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($domain->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->max_score }}</td>
                                        <td>{{ $item->rating_type }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <form method="POST" action="{{ route('assessment-domains.items.store', $domain->id) }}" class="row g-2">
                            @csrf
                            <div class="col-md-4"><input name="name" class="form-control" placeholder="{{ _lang('Assessment item') }}" required></div>
                            <div class="col-md-2"><input name="max_score" type="number" step="0.01" class="form-control" value="5" required></div>
                            <div class="col-md-2">
                                <select name="rating_type" class="form-select">
                                    <option value="scale">{{ _lang('Scale') }}</option>
                                    <option value="numeric">{{ _lang('Numeric') }}</option>
                                    <option value="grade">{{ _lang('Grade') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3"><input name="grade_scale" class="form-control" placeholder="Excellent, Very Good, Good"></div>
                            <div class="col-md-1"><button class="btn btn-light">{{ _lang('Add') }}</button></div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
