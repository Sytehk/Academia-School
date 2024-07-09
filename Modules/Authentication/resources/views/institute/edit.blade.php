@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('institute') }}
@endsection

@section('create-button')
    <a href="{{ route('institutes.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('institute') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('institutes.update', $institute->id) }}" enctype="multipart/form-data"
                        method="post">
                        @csrf
                        @method('PATCH')

                        <h4 class="mb-4">{{ _lang('Institute Information') }}</h4>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Name') }} <span
                                            class='text-danger'>*</span></label>
                                    <input type="text" class="form-control" name="name"
                                        value="{{ old('name', $institute->name) }}" placeholder="Name" required>

                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Email') }}</label>
                                    <input type="email" class="form-control" name="email"
                                        value="{{ old('email', $institute->email) }}" placeholder="Email">
                                    @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Address') }}</label>
                                    <input type="text" class="form-control" name="address"
                                        value="{{ old('address', $institute->address) }}" placeholder="Address">
                                    @error('address')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Phone') }}</label>
                                    <input type="text" class="form-control" name="phone"
                                        value="{{ old('phone', $institute->phone) }}" placeholder="Phone Number">
                                    @error('phone')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Domain') }}</label>
                                    <input type="text" class="form-control" name="domain"
                                        value="{{ old('domain', $institute->domain) }}" placeholder="Domain">
                                    @error('domain')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Institute Type') }}</label>
                                    <input type="text" class="form-control" name="institute_type"
                                        value="{{ old('institute_type', $institute->institute_type) }}"
                                        placeholder="School / College / Coaching">
                                    @error('institute_type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Theme') }}</label>
                                    <select class="form-control" name="theme_id">
                                        <option value="">{{ _lang('None') }}</option>
                                        @foreach ($themes as $theme)
                                            <option value="{{ $theme->id }}"
                                                {{ old('theme_id', $institute->theme_id) == $theme->id ? 'selected' : '' }}>
                                                {{ $theme->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('theme_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Status') }}</label>
                                    <select class="form-control" name="status">
                                        <option value="1" {{ old('status', $institute->status) == 1 ? 'selected' : '' }}>
                                            {{ _lang('Active') }}
                                        </option>
                                        <option value="0" {{ old('status', $institute->status) == 0 ? 'selected' : '' }}>
                                            {{ _lang('Inactive') }}
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Logo') }}</label>
                                    <input type="file" class="form-control" name="logo">
                                    @if ($institute->logo)
                                        <img src="{{ asset($institute->logo) }}" class="mt-2"
                                            style="max-height:60px;">
                                    @endif
                                    @error('logo')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if ($institute->owner)
                            <hr>
                            <h4 class="mb-4">{{ _lang('Owner Information') }}</h4>
                            <p class="text-muted">{{ _lang('These are the details filled in at onboarding time — leave a field blank to keep its current value.') }}</p>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('Owner Name') }}</label>
                                        <input type="text" class="form-control" name="owner_name"
                                            value="{{ old('owner_name', $institute->owner->name) }}">
                                        @error('owner_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('Owner Email') }}</label>
                                        <input type="email" class="form-control" name="owner_email"
                                            value="{{ old('owner_email', $institute->owner->email) }}">
                                        @error('owner_email')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('Owner Phone') }}</label>
                                        <input type="text" class="form-control" name="owner_phone"
                                            value="{{ old('owner_phone', $institute->owner->phone) }}">
                                        @error('owner_phone')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('New Password') }}</label>
                                        <input type="password" class="form-control" name="owner_password"
                                            placeholder="{{ _lang('Leave blank to keep current password') }}">
                                        @error('owner_password')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('Confirm New Password') }}</label>
                                        <input type="password" class="form-control"
                                            name="owner_password_confirmation">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mt-3">{{ _lang('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
