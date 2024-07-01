@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('teachers.bulk-file-upload') }}">
        {{ _lang('CSV/Xlsx Upload File') }}
    </a>
@endsection

@section('content')
    <form action="{{ route('teachers.store') }}" method="post" enctype="multipart/form-data" data-disable-on-submit>
        @csrf

        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Name') }} <span class='text-danger'>*</span> </label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Name" required>

                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Designation') }} <span class='text-danger'>*</span> </label>
                    <select name="designation" class="form-select form-select-sm form-select-solid" data-control="select2"
                        id="designation" required>

                        {!! create_option('picklists', 'value', 'value', old('designation'), ['type' => 'Designation']) !!}

                    </select>

                    @error('designation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Department') }} <span class='text-danger'>*</span> </label>
                    <select class="form-select form-select-sm form-select-solid" data-control="select2" name="department_id"
                        required>
                        <option value="">{{ _lang('Select Department') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">
                                {{ $department->department_name }}</option>
                        @endforeach
                    </select>

                    @error('department_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Gender') }} <span
                            class="text-info">(optional)</span></label>
                    <select name="gender" class="form-select form-select-sm form-select-solid" data-control="select2"
                        required>
                        <option value="">Select One</option>
                        <option @if (old('gender') == 'Male') selected @endif value="Male">Male
                        </option>
                        <option @if (old('gender') == 'Female') selected @endif value="Female">Female
                        </option>
                    </select>

                    @error('gender')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Religion') }} <span
                            class="text-info">(optional)</span></label>
                    <select name="religion" class="form-select form-select-sm form-select-solid" data-control="select2"
                        id="religion">

                        {!! create_option('picklists', 'value', 'value', old('religion'), ['type' => 'Religion']) !!}

                    </select>

                    @error('religion')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Blood Group') }} <span
                            class="text-info">(optional)</span></label>
                    <select name="blood" class="form-select form-select-sm form-select-solid" data-control="select2">
                        <option value=""> Select {{ _lang('Blood Group') }}</option>
                        @foreach ($bloods as $item)
                            <option value="{{ $item }}"> {{ $item }}</option>
                        @endforeach
                    </select>

                    @error('blood')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Serial No') }} <span
                            class="text-info">(optional)</span></label>
                    <input type="number" class="form-control" name="sl" value="{{ old('sl') }}"
                        placeholder="Serial No">

                    @error('sl')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Joining Date') }} <span
                            class="text-info">(optional)</span></label>
                    <input type="date" class="form-control" name="joining_date" value="{{ old('joining_date') }}">

                    @error('joining_date')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Address') }}
                        <span class="text-info">(optional)</span>
                    </label>
                    <textarea class="form-control" name="address" id='address' cols="2" rows="4" placeholder="Address"> {{ old('address') }}</textarea>

                    @error('address')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6 my-5 text-center">
                <h2 class="text-primary block my-5 font_size_medium">
                    <i class="bi bi-lock-fill font_size_medium"></i>
                    <strong>Login Details</strong>
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Email') }}
                        <span class='text-danger'>*</span>
                    </label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                        autocomplete="off" placeholder="Email">

                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Phone Number') }} <span
                            class='text-danger'>*</span></label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                        id="user_phone" autocomplete="off" placeholder="Phone">

                    @error('phone')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                @php
                    $random6DigitPassword = 123456;
                @endphp
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Password') }} <span class='text-danger'>*</span></label>
                    <input type="text" class="form-control" name="password"
                        value="{{ old('password', $random6DigitPassword) }}" placeholder="Enter Password">

                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Confirm Password') }}
                        <span class='text-danger'>*</span>
                    </label>
                    <input type="text" class="form-control" name="password_confirmation"
                        value="{{ old('password_confirmation', $random6DigitPassword) }}"
                        placeholder="Password Confirmation">

                    @error('password_confirmation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <!--begin::Thumbnail settings-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>Teacher Picture <span class="text-info">(optional)</span></h2>
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
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change image">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <!--begin::Inputs-->
                            <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                            <input type="hidden" name="image_remove" />
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->
                        <!--begin::Cancel-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel image">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                        <!--end::Cancel-->
                        <!--begin::Remove-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove image">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                        <!--end::Remove-->
                    </div>
                    <!--end::Image input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">Set the product thumbnail image. Only *.png, *.jpg and *.jpeg image files
                        are accepted</div>
                    <!--end::Description-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Thumbnail settings-->
        </div>

        <div class="form-group my-5">
            <button type="submit" class="btn btn-primary mt-5">{{ _lang('Create Teacher') }}</button>
        </div>
    </form>
@endsection
