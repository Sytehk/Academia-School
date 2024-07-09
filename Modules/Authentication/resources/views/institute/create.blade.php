@extends('layouts.backend')

@section('page_title_breadcrumb')
    Institute Onboarding
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('institutes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h4 class="mb-4">Institute Information</h4>

                <div class="row">

                    {{-- Institute Name --}}
                    <div class="col-lg-6 mb-3">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="institute_name"
                            class="form-control @error('institute_name') is-invalid @enderror"
                            placeholder="Enter institute name" value="{{ old('institute_name') }}" required>
                        @error('institute_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-lg-6 mb-3">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="institute_email"
                            class="form-control @error('institute_email') is-invalid @enderror"
                            placeholder="example@institute.com" value="{{ old('institute_email') }}" required>
                        @error('institute_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="col-lg-6 mb-3">
                        <label>Phone</label>
                        <input type="text" name="institute_phone" class="form-control" placeholder="01XXXXXXXXX"
                            value="{{ old('institute_phone') }}">
                    </div>

                    {{-- Domain --}}
                    <div class="col-lg-6 mb-3">
                        <label>Domain</label>
                        <input type="text" name="institute_domain" class="form-control" placeholder="example.com"
                            value="{{ old('institute_domain') }}">
                    </div>

                    {{-- Type --}}
                    <div class="col-lg-6 mb-3">
                        <label>Institute Type</label>
                        <input type="text" name="institute_type" class="form-control"
                            placeholder="School / College / Coaching" value="{{ old('institute_type') }}">
                    </div>

                    {{-- Address --}}
                    <div class="col-lg-6 mb-3">
                        <label>Address</label>
                        <textarea name="institute_address" class="form-control" placeholder="Enter full address">{{ old('institute_address') }}</textarea>
                    </div>

                    {{-- Logo Upload --}}
                    <div class="col-lg-6 mb-3">
                        <label>Logo</label>
                        <input type="file" name="institute_logo" class="form-control" onchange="previewLogo(event)">
                        <img id="logoPreview" class="mt-2" style="max-height:80px; display:none;">
                    </div>

                </div>

                <hr>

                <h4 class="mb-4">Owner Information</h4>

                <div class="row">
                    {{-- Owner Name --}}
                    <div class="col-lg-6 mb-3">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="user_name" class="form-control @error('user_name') is-invalid @enderror"
                            placeholder="Owner full name" value="{{ old('user_name') }}" required>
                        @error('user_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Owner Email --}}
                    <div class="col-lg-6 mb-3">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="user_email" class="form-control" placeholder="owner@email.com"
                            value="{{ old('user_email') }}" required>
                    </div>

                    {{-- Owner Phone --}}
                    <div class="col-lg-6 mb-3">
                        <label>Phone</label>
                        <input type="text" name="user_phone" class="form-control" placeholder="01XXXXXXXXX"
                            value="{{ old('user_phone') }}">
                    </div>

                    {{-- Avatar Upload --}}
                    <div class="col-lg-6 mb-3">
                        <label>Avatar</label>
                        <input type="file" name="user_avatar" class="form-control" onchange="previewAvatar(event)">
                        <img id="avatarPreview" class="mt-2" style="max-height:80px; display:none;">
                    </div>

                    {{-- Password --}}
                    <div class="col-lg-6 mb-3">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-lg-6 mb-3">
                        <label>Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control"
                            placeholder="Confirm password" required>
                    </div>

                </div>

                <button class="btn btn-primary mt-4">
                    Create Institute
                </button>

            </form>

        </div>
    </div>

    {{-- ================= JS PREVIEW ================= --}}
    <script>
        function previewLogo(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('logoPreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function previewAvatar(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('avatarPreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endsection
