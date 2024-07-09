@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('branch') }}
@endsection

@section('create-button')
    <a href="{{ route('branches.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('branch') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('branches.update', $branch->id) }}" enctype="multipart/form-data" method="post">
                @csrf
                @method('PATCH')

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Branch Name') }}
                                <span class='text-danger'>*</span>
                            </label>
                            <input type="text" class="form-control" name="name" value="{{ $branch->name }}"
                                placeholder="Branch Name" required>

                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Status') }}</label>
                            <select class="form-control" name="status">
                                <option value="1" {{ $branch->status == 1 ? 'selected' : '' }}>{{ _lang('Active') }}</option>
                                <option value="0" {{ $branch->status == 0 ? 'selected' : '' }}>{{ _lang('Inactive') }}</option>
                            </select>
                            @error('status')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-9">
                        <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
