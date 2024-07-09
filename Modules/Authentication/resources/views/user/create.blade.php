@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('user') }}
@endsection

@section('create-button')
    <a href="{{ route('users.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('user') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('users.store') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Name') }} <span class='text-danger'>*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="Name" required>

                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Assign Roles') }}
                                <span class='text-danger'>*</span></label>
                            <select name="role_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>

                            @error('role_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Email') }} <span
                                    class='text-danger'>*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                placeholder="Email" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Phone') }}
                                <span class='text-danger'>*</span>
                            </label>
                            {{-- pattern="[0-9]{10,15}" --}}
                            <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                                placeholder="Phone Number" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Password') }} <span
                                    class='text-danger'>*</span></label>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Confirm Password') }} <span
                                    class='text-danger'>*</span></label>
                            <input type="password" class="form-control" name="password_confirmation"
                                placeholder="Confirm Password" required>
                            @error('password_confirmation')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Linkedin Link') }} <span
                                    class="text-info">(optional)</span> </label>
                            <input type="text" class="form-control" name="linkedin" value="{{ old('linkedin') }}"
                                placeholder="Linkedin link">
                            @error('linkedin')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Google Plus Link') }} <span
                                    class="text-info">(optional)</span> </label>
                            <input type="text" class="form-control" name="google_plus" value="{{ old('google_plus') }}"
                                placeholder="Google plus link">
                            @error('google_plus')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Facebook Link') }} <span
                                    class="text-info">(optional)</span> </label>
                            <input type="text" class="form-control" name="facebook" value="{{ old('facebook') }}"
                                placeholder="Facebook link">
                            @error('facebook')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Twitter Link') }} <span
                                    class="text-info">(optional)</span> </label>
                            <input type="text" class="form-control" name="twitter" value="{{ old('twitter') }}"
                                placeholder="Twitter link">
                            @error('twitter')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <!--begin::Thumbnail settings-->
                            <div class="card card-flush py-4">
                                <!--begin::Card header-->
                                <div class="card-header">
                                    <!--begin::Card title-->
                                    <div class="card-title">
                                        <h2>User Picture <span class="text-info">(optional)</span></h2>
                                    </div>
                                    <!--end::Card title-->
                                </div>
                                <!--end::Card header-->
                                <!--begin::Card body-->
                                <div class="card-body text-center pt-0">
                                    <!--begin::Image input-->
                                    <!--begin::Image input placeholder-->
                                    <style>
                                        .image-input-placeholder {
                                            background-image: url('{{ asset('assets/media/svg/files/blank-image.svg') }}');
                                        }

                                        [data-theme="dark"] .image-input-placeholder {
                                            background-image: url('{{ asset('assets/media/svg/files/blank-image-dark.svg') }}');
                                        }
                                    </style>

                                    <!--end::Image input placeholder-->
                                    <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                                        data-kt-image-input="true">
                                        <!--begin::Preview existing image-->
                                        <div class="image-input-wrapper w-150px h-150px"></div>
                                        <!--end::Preview existing image-->
                                        <!--begin::Label-->
                                        <label
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                            title="Change image">
                                            <i class="bi bi-pencil-fill fs-7"></i>
                                            <!--begin::Inputs-->
                                            <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                            <input type="hidden" name="image_remove" />
                                            <!--end::Inputs-->
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Cancel-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                            title="Cancel image">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Cancel-->
                                        <!--begin::Remove-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                            title="Remove image">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Remove-->
                                    </div>
                                    <!--end::Image input-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">Set the product thumbnail image. Only *.png, *.jpg and
                                        *.jpeg image files
                                        are accepted</div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Card body-->
                                @error('image')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <!--end::Thumbnail settings-->
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-5">
                        <button type="submit" class="btn btn-primary mt-5">{{ _lang('Create User') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
