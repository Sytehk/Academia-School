@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('teachers.bulk-file-upload') }}">
        {{ _lang('Bulk Teacher Upload') }}
    </a>
@endsection

@section('content')
    <form action="{{ route('teachers.update', $teacher->id) }}"method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Name') }}</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $teacher->name }}"
                        placeholder="Name">

                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Designation') }}</label>
                    <select name="designation" class="form-select form-select-sm form-select-solid" data-control="select2"
                        id="designation">
                        {!! create_option('picklists', 'value', 'value', $teacher->designation, ['type' => 'Designation']) !!}
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
                    <label class="control-label my-2">{{ _lang('Department') }}</label>
                    <select class="form-select form-select-sm form-select-solid" data-control="select2"
                        name="department_id">
                        @foreach ($departments as $department)
                            <option @if ($teacher->department_id == $department->id) selected @endif value="{{ $department->id }}">
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
                    <label class="control-label my-2">{{ _lang('Gender') }}</label>
                    <select name="gender" class="form-select form-select-sm form-select-solid" data-control="select2">
                        <option value="">Select One</option>
                        <option @if ($teacher->gender == 'Male') selected @endif value="Male">Male
                        </option>
                        <option @if ($teacher->gender == 'Female') selected @endif value="Female">Female
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
                    <label class="control-label my-2">{{ _lang('Religion') }}</label>
                    <select name="religion" class="form-select form-select-sm form-select-solid" data-control="select2"
                        id="religion">
                        {!! create_option('picklists', 'value', 'value', $teacher->religion, ['type' => 'Religion']) !!}
                    </select>
                    @error('religion')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Blood Group') }}</label>
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
                    <label class="control-label my-2">{{ _lang('Serial No') }}</label>
                    <input type="number" class="form-control" name="sl" value="{{ $teacher->sl }}"
                        placeholder="Serial No">

                    @error('sl')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Joining Date') }}</label>
                    <input type="date" class="form-control" name="joining_date" value="{{ $teacher->joining_date }}">

                    @error('joining_date')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Address') }}</label>
                    <textarea class="form-control" name="address" id='address'> {{ $teacher->address }}</textarea>
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
                    <label class="control-label my-2">
                        {{ _lang('Email') }}
                        <span class='text-danger'>*</span>
                    </label>
                    <input type="email" class="form-control" name="email" value="{{ $teacher['email'] }}" required>

                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">
                        {{ _lang('Phone Number') }}
                        <span class='text-danger'>*</span>
                    </label>
                    <input type="text" class="form-control" name="phone" value="{{ $teacher['user_phone'] }}"
                        min="10" max="15" placeholder="Phone" required>
                    @error('phone')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Password') }}</label>
                    <input type="text" class="form-control" name="password" value="">

                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Confirm Password') }}</label>
                    <input type="text" class="form-control" name="password_confirmation" value="">

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

                    @php
                        $userImage = $teacher->user?->image;
                        $hasCustomImage = $userImage && $userImage !== null;
                        $imageUrl = $hasCustomImage
                            ? asset('storage/users/' . $userImage)
                            : asset('assets/media/svg/files/blank-image.svg');
                        $imageUrlDark = $hasCustomImage
                            ? asset('storage/users/' . $userImage)
                            : asset('assets/media/svg/files/blank-image-dark.svg');
                    @endphp

                    <style>
                        .image-input-placeholder {
                            background-image: url('{{ $imageUrl }}');
                        }

                        [data-theme="dark"] .image-input-placeholder {
                            background-image: url('{{ $imageUrlDark }}');
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
            <button type="submit" class="btn btn-primary mt-5">Update Teacher</button>
        </div>
    </form>
@endsection
