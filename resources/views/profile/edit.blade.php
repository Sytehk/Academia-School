@extends('layouts.backend')

@section('content')
    <div class="container-fluid py-4">
        @php
            $demoService = app(\App\Services\DemoModeService::class);
            $profileRestricted = $demoService->isRestricted('profile_change') || $demoService->isRestricted('email_change');
            $passwordRestricted = $demoService->isRestricted('password_change');
        @endphp

        {{-- PAGE TITLE --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">{{ _lang('My Profile') }}</h2>
                <p class="text-muted mb-0">
                    {{ _lang('Manage your profile information and password security.') }}
                </p>
            </div>
        </div>

        <div class="row g-4">

            {{-- =========================================
                PROFILE INFORMATION CARD
            ========================================== --}}
            <div class="col-lg-7">

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                    {{-- CARD HEADER --}}
                    <div class="card-header bg-primary text-white py-3 border-0">
                        <h5 class="mb-0 fw-semibold p-5">
                            <i class="fas fa-user-circle me-2"></i>
                            {{ _lang('Profile Information') }}
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        @if ($profileRestricted)
                            <div class="alert alert-warning rounded-3">
                                <i class="fas fa-lock me-2"></i>
                                {{ _lang('Profile and email updates are disabled in demo mode.') }}
                            </div>
                        @endif

                        {{-- SUCCESS MESSAGE --}}
                        @if (session('status') == 'profile-updated')
                            <div class="alert alert-success rounded-3">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ _lang('Profile updated successfully.') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">

                            @csrf
                            @method('PATCH')

                            {{-- PROFILE IMAGE --}}
                            <div class="text-center mb-4">

                                @if (!empty($user->image))
                                    <img src="{{ asset('storage/users/' . $user->image) }}" alt="Profile Image"
                                        class="rounded-circle shadow border border-4 border-light mb-3"
                                        style="width: 130px; height: 130px; object-fit: cover;">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D6EFD&color=fff&size=200"
                                        alt="Profile Image" class="rounded-circle shadow border border-4 border-light mb-3"
                                        style="width: 130px; height: 130px; object-fit: cover;">
                                @endif

                                <div class="mt-2">
                                    <label class="btn btn-outline-primary rounded-pill px-4">
                                        <i class="fas fa-camera me-2"></i>
                                        {{ _lang('Change Photo') }}

                                        <input type="file" name="image" hidden>
                                    </label>
                                </div>

                                @error('image')
                                    <small class="text-danger d-block mt-2">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>

                            <div class="row">

                                {{-- NAME --}}
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">
                                        {{ _lang('Full Name') }}
                                    </label>

                                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                        class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror"
                                        placeholder="{{ _lang('Enter your full name') }}">

                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- EMAIL --}}
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">
                                        {{ _lang('Email Address') }}
                                    </label>

                                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                        class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror"
                                        placeholder="{{ _lang('Enter email address') }}">

                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                            </div>

                            {{-- SUBMIT --}}
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm"
                                    @disabled($profileRestricted)>

                                    <i class="fas fa-save me-2"></i>
                                    {{ _lang('Update Profile') }}
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>

            {{-- =========================================
                PASSWORD UPDATE CARD
            ========================================== --}}
            <div class="col-lg-5">

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                    {{-- CARD HEADER --}}
                    <div class="card-header bg-dark text-white py-3 border-0">
                        <h5 class="mb-0 fw-semibold p-5">
                            <i class="fas fa-lock me-2"></i>
                            {{ _lang('Change Password') }}
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        @if ($passwordRestricted)
                            <div class="alert alert-warning rounded-3">
                                <i class="fas fa-lock me-2"></i>
                                {{ _lang('Password changes are disabled in demo mode.') }}
                            </div>
                        @endif

                        {{-- SUCCESS MESSAGE --}}
                        @if (session('status') == 'password-updated')
                            <div class="alert alert-success rounded-3">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ _lang('Password updated successfully.') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('PUT')

                            {{-- CURRENT PASSWORD --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    {{ _lang('Current Password') }}
                                </label>

                                <input type="password" name="current_password"
                                    class="form-control form-control-lg rounded-3 @error('current_password') is-invalid @enderror"
                                    placeholder="{{ _lang('Current password') }}">

                                @error('current_password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- NEW PASSWORD --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    {{ _lang('New Password') }}
                                </label>

                                <input type="password" name="password"
                                    class="form-control form-control-lg rounded-3 @error('password') is-invalid @enderror"
                                    placeholder="{{ _lang('New password') }}">

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- CONFIRM PASSWORD --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    {{ _lang('Confirm Password') }}
                                </label>

                                <input type="password" name="password_confirmation"
                                    class="form-control form-control-lg rounded-3"
                                    placeholder="{{ _lang('Confirm password') }}">
                            </div>

                            {{-- SUBMIT --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg rounded-pill shadow-sm"
                                    @disabled($passwordRestricted)>

                                    <i class="fas fa-key me-2"></i>
                                    {{ _lang('Update Password') }}
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
