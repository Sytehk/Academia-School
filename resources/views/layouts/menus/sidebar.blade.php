@php
    $user = Auth::user();
@endphp

@if ($user->can('dashboard'))
    <div class="menu-item">
        <!--begin:Menu link-->
        <a class="menu-link {{ Route::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="2" width="9" height="9" rx="2" fill="currentColor" />
                        <rect opacity="0.3" x="13" y="2" width="9" height="9" rx="2"
                            fill="currentColor" />
                        <rect opacity="0.3" x="13" y="13" width="9" height="9" rx="2"
                            fill="currentColor" />
                        <rect opacity="0.3" x="2" y="13" width="9" height="9" rx="2"
                            fill="currentColor" />
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Dashboard') }}</span>
        </a>
        <!--end:Menu link-->
    </div>
@endif

@if (
    $user->can('student_information_student_index') ||
        $user->can('student_information_student_migration') ||
        $user->can('student_information_student_pullback') ||
        $user->can('student_information_migration_list') ||
        $user->can('student_information_all_student_list'))
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('students') || Request::is('students/*') || Request::is('students-bulk-file-upload') || Request::is('student-migration') || Request::is('student-migration-pushback') || Request::is('migrated-list') || Request::is('at-a-glance') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M16.95 18.9688C16.75 18.9688 16.55 18.8688 16.35 18.7688C15.85 18.4688 15.75 17.8688 16.05 17.3688L19.65 11.9688L16.05 6.56876C15.75 6.06876 15.85 5.46873 16.35 5.16873C16.85 4.86873 17.45 4.96878 17.75 5.46878L21.75 11.4688C21.95 11.7688 21.95 12.2688 21.75 12.5688L17.75 18.5688C17.55 18.7688 17.25 18.9688 16.95 18.9688ZM7.55001 18.7688C8.05001 18.4688 8.15 17.8688 7.85 17.3688L4.25001 11.9688L7.85 6.56876C8.15 6.06876 8.05001 5.46873 7.55001 5.16873C7.05001 4.86873 6.45 4.96878 6.15 5.46878L2.15 11.4688C1.95 11.7688 1.95 12.2688 2.15 12.5688L6.15 18.5688C6.35 18.8688 6.65 18.9688 6.95 18.9688C7.15 18.9688 7.35001 18.8688 7.55001 18.7688Z"
                            fill="currentColor"></path>
                        <path opacity="0.3"
                            d="M10.45 18.9687C10.35 18.9687 10.25 18.9687 10.25 18.9687C9.75 18.8687 9.35 18.2688 9.55 17.7688L12.55 5.76878C12.65 5.26878 13.25 4.8687 13.75 5.0687C14.25 5.1687 14.65 5.76878 14.45 6.26878L11.45 18.2688C11.35 18.6688 10.85 18.9687 10.45 18.9687Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Students Information') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('student_information_student_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('students') || Request::is('students/*') || Request::is('students-bulk-file-upload') ? 'active' : '' }}"
                        href="{{ route('students.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Students List') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


            @if ($user->can('student_information_student_migration'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('student-migration.index') ? 'active' : '' }}"
                        href="{{ route('student-migration.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Student Migration') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('student_information_student_pullback'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('student-migration-pushback.index') ? 'active' : '' }}"
                        href="{{ route('student-migration-pushback.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Migration Pushback') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('student_information_migration_list'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('migrated-list.index') || Request::is('migrated-list') ? 'active' : '' }}"
                        href="{{ route('migrated-list.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Migrated List') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('student_information_all_student_list'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('at-a-glance.index') ? 'active' : '' }}"
                        href="{{ route('at-a-glance.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('All Students view list') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

        </div>
        <!--end:Menu sub-->

    </div>
    <!--end:Menu item-->
@endif

@if (
    $user->can('staff_information_staff_attendance_view') ||
        $user->can('staff_information_teachers_index') ||
        $user->can('staff_information_teachers_create'))
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('teachers') || Request::is('teachers/*') || Request::is('teachers-bulk-file-upload') || Request::is('staffs') || Request::is('staffs/*') || Request::is('staffs-attendance') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M20 14H18V10H20C20.6 10 21 10.4 21 11V13C21 13.6 20.6 14 20 14ZM21 19V17C21 16.4 20.6 16 20 16H18V20H20C20.6 20 21 19.6 21 19ZM21 7V5C21 4.4 20.6 4 20 4H18V8H20C20.6 8 21 7.6 21 7Z"
                            fill="currentColor"></path>
                        <path opacity="0.3"
                            d="M17 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H17C17.6 2 18 2.4 18 3V21C18 21.6 17.6 22 17 22ZM10 7C8.9 7 8 7.9 8 9C8 10.1 8.9 11 10 11C11.1 11 12 10.1 12 9C12 7.9 11.1 7 10 7ZM13.3 16C14 16 14.5 15.3 14.3 14.7C13.7 13.2 12 12 10.1 12C8.10001 12 6.49999 13.1 5.89999 14.7C5.59999 15.3 6.19999 16 7.39999 16H13.3Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Staffs Information') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('staff_information_staff_attendance_view'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('staffs-attendance') ? 'active' : '' }}"
                        href="{{ url('staffs-attendance') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Staff Attendance') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('staff_information_teachers_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('teachers') || Request::is('teachers/*') || Request::is('teachers-bulk-file-upload') ? 'active' : '' }}"
                        href="{{ route('teachers.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Teachers List') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


            @if ($user->can('staff_information_teachers_create'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('staffs') || Request::is('staffs/*') ? 'active' : '' }}"
                        href="{{ route('staffs.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Staffs List') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

@if (
    $user->can('student_attendance_student_attendance_index') ||
        $user->can('student_attendance_exam_attendance_index') ||
        $user->can('student_attendance_exam_attendance_schedule') ||
        $user->can('student_attendance_student_attendance_report') ||
        $user->can('student_attendance_student_absent_report'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('student-attendance') || Request::is('exams-attendance') || Request::is('reports/student_attendance_report') || Request::is('reports/student_attendance_report/view') || Request::is('reports-student_attendance_date_to_date/view') || Request::is('reports-student_attendance_date_to_date') || Request::is('absent-fine-report') || Request::is('exams-schedule') || Request::is('qrattendance/scanner') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3"
                            d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                            fill="currentColor"></path>
                        <path
                            d="M8.70001 6C8.10001 5.7 7.39999 5.40001 6.79999 5.10001C7.79999 4.00001 8.90001 3 10.1 2.2C10.7 2.1 11.4 2 12 2C12.7 2 13.3 2.1 13.9 2.2C12 3.2 10.2 4.5 8.70001 6ZM12 8.39999C13.9 6.59999 16.2 5.30001 18.7 4.60001C18.1 4.00001 17.4 3.6 16.7 3.2C14.4 4.1 12.2 5.40001 10.5 7.10001C11 7.50001 11.5 7.89999 12 8.39999ZM7 20C7 20.2 7 20.4 7 20.6C6.2 20.1 5.49999 19.6 4.89999 19C4.59999 18 4.00001 17.2 3.20001 16.6C2.80001 15.8 2.49999 15 2.29999 14.1C4.99999 14.7 7 17.1 7 20ZM10.6 9.89999C8.70001 8.09999 6.39999 6.9 3.79999 6.3C3.39999 6.9 2.99999 7.5 2.79999 8.2C5.39999 8.6 7.7 9.80001 9.5 11.6C9.8 10.9 10.2 10.4 10.6 9.89999ZM2.20001 10.1C2.10001 10.7 2 11.4 2 12C2 12 2 12 2 12.1C4.3 12.4 6.40001 13.7 7.60001 15.6C7.80001 14.8 8.09999 14.1 8.39999 13.4C6.89999 11.6 4.70001 10.4 2.20001 10.1ZM11 20C11 14 15.4 9.00001 21.2 8.10001C20.9 7.40001 20.6 6.8 20.2 6.2C13.8 7.5 9 13.1 9 19.9C9 20.4 9.00001 21 9.10001 21.5C9.80001 21.7 10.5 21.8 11.2 21.9C11.1 21.3 11 20.7 11 20ZM19.1 19C19.4 18 20 17.2 20.8 16.6C21.2 15.8 21.5 15 21.7 14.1C19 14.7 16.9 17.1 16.9 20C16.9 20.2 16.9 20.4 16.9 20.6C17.8 20.2 18.5 19.6 19.1 19ZM15 20C15 15.9 18.1 12.6 22 12.1C22 12.1 22 12.1 22 12C22 11.3 21.9 10.7 21.8 10.1C16.8 10.7 13 14.9 13 20C13 20.7 13.1 21.3 13.2 21.9C13.9 21.8 14.5 21.7 15.2 21.5C15.1 21 15 20.5 15 20Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Student Attendance') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('student_attendance_student_attendance_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('student-attendance') ? 'active' : '' }}"
                        href="{{ url('student-attendance') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Student Attendance') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('student_attendance_exam_attendance_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('exams-attendance') ? 'active' : '' }}"
                        href="{{ url('exams-attendance') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Exam Attendance') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('student_attendance_exam_attendance_schedule'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('exams-schedule') ? 'active' : '' }}"
                        href="{{ url('exams-schedule') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Exam Schedule') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('student_attendance_student_attendance_report'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('reports-student_attendance_date_to_date') || Request::is('reports-student_attendance_date_to_date') || Request::is('reports-student_attendance_date_to_date/view') ? 'active' : '' }}"
                        href="{{ url('reports-student_attendance_date_to_date') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Attendance Report') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


            @if ($user->can('student_attendance_student_absent_report'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('absent-fine-report') ? 'active' : '' }}"
                        href="{{ url('absent-fine-report') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Absent Fine') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

@if ($user->can('student_qr_code_attendance_index'))
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('qrattendance/scanner') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <i class="fa-solid fa-qrcode fs-2"></i>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('QR Code Attendance') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('student_qr_code_attendance_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('qrattendance/scanner') ? 'active' : '' }}"
                        href="{{ url('qrattendance/scanner') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('QR Attendance') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif
        </div>
    </div>
@endif

@include('layouts.menus.partials.academic_configuration_module')

@include('layouts.menus.partials.fees_module')

@include('layouts.menus.partials.account_module')

@include('layouts.menus.partials.payroll_module')

@include('layouts.menus.partials.routine_module')

@include('layouts.menus.partials.library_module')

@include('layouts.menus.partials.exam_module')

@if (
    $user->can('layouts_certificates_general') ||
        $user->can('layouts_certificates_testimonial') ||
        $user->can('layouts_certificates_attendance_certificate') ||
        $user->can('layouts_certificates_hsc_recommendation_letter') ||
        $user->can('layouts_certificates_transfer') ||
        $user->can('layouts_certificates_abroad_letter') ||
        $user->can('layouts_certificates_character') ||
        $user->can('layouts_certificates_study') ||
        $user->can('layouts_certificates_bonafide') ||
        $user->can('layouts_certificates_migration') ||
        $user->can('certificate_templates_manage'))

    <!--begin:Menu item Layouts & Certificates-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('layout-cert') || Request::is('layout-cert/*') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <i class="fa-solid fa-certificate fs-2"></i>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Layout & Certificates') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">

            @if ($user->can('layouts_certificates_general'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'general-certificate' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=general-certificate') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('General Recommendation Letter') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_testimonial'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'testimonial' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=testimonial') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Testimonial') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_attendance_certificate'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'attendance-certificate' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=attendance-certificate') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Attendance Certificate') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_hsc_recommendation_letter'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'hsc-recommendation' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=hsc-recommendation') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('HSC Recommendation Letter') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_transfer'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'transfer-certificate' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=transfer-certificate') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Transfer Certificate') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_abroad_letter'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'abroad-recommendation' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=abroad-recommendation') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Abroad Letter') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_character'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'character-certificate' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=character-certificate') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Character Certificate') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_study'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'study-certificate' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=study-certificate') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Study Certificate') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_bonafide'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'bonafide-certificate' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=bonafide-certificate') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Bonafide Certificate') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('layouts_certificates_migration'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::query('type') === 'migration-certificate' ? 'active' : '' }}"
                        href="{{ url('layout-cert?type=migration-certificate') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Migration Certificate') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('certificate_templates_manage'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('certificate-templates') || Request::is('certificate-templates/*') ? 'active' : '' }}"
                        href="{{ route('certificate-templates.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Manage Templates') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif
        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

@if (
    $user->can('sms_notifications_templates') ||
        $user->can('sms_notifications_phone_book_category') ||
        $user->can('sms_notifications_phone_book') ||
        $user->can('sms_notifications_sent_sms') ||
        $user->can('sms_notifications_purchase_sms') ||
        $user->can('sms_notifications_sms_report'))

    <!--begin:Menu item SMS Module-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('phone-book') || Request::is('phone-book-category') || Request::is('sms-template') || Request::is('sms-template/*') || Request::is('sms-compose') || Request::is('exam-result-view') || Request::is('sms-purchase') || Request::is('sms-purchase/*') || Request::is('send-sms-report') || Request::is('send-sms-report/*') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <i class="fa-solid fa-comment-sms fs-2"></i>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('SMS Module') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('sms_notifications_templates'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('sms-template') || Request::is('sms-template/*') ? 'active' : '' }}"
                        href="{{ route('sms-template.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('SMS Template') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('sms_notifications_phone_book_category'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('phone-book-category') ? 'active' : '' }}"
                        href="{{ route('phone-book-category.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Phone Book Category') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('sms_notifications_phone_book'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('phone-book') ? 'active' : '' }}"
                        href="{{ route('phone-book.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Phone Book') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('sms_notifications_sent_sms'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('sms-compose') ? 'active' : '' }}"
                        href="{{ url('sms-compose') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('SMS Sent') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('exam_module_exam_result'))
                {{-- Exam Result SMS already exists on the Examination side (Exam Result ->
                    Send Result Notifications) but had no way to be found from the SMS
                    module (FEAT-009). Same page, same permission — just a second entry
                    point. --}}
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('exam-result-view') ? 'active' : '' }}"
                        href="{{ route('exam-result-view') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Exam Result SMS') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('sms_notifications_purchase_sms'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('sms-purchase') || Request::is('sms-purchase/*') ? 'active' : '' }}"
                        href="{{ route('sms-purchase.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Purchase SMS') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('sms_notifications_sms_report'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('send-sms-report') || Request::is('send-sms-report/*') ? 'active' : '' }}"
                        href="{{ route('send-sms-report.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('SMS Report') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif
        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

@if (
    $user->can('assign_shift') ||
        $user->can('assign_subject') ||
        $user->can('assign_class') ||
        $user->can('notice') ||
        $user->can('event') ||
        $user->can('contact_message') ||
        $user->can('user_activities') ||
        $user->can('id_card_student') ||
        $user->can('id_card_teacher') ||
        $user->can('id_card_staff'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('assign-class') ||
        Request::is('notices') ||
        Request::is('notices/*') ||
        Request::is('events') ||
        Request::is('events/*') ||
        Request::is('contact-message') ||
        Request::is('user-logs') ||
        Request::is('assign-shifts') ||
        Request::is('assign-subjects') ||
        Request::is('student-id-cards') ||
        Request::is('teacher-id-cards') ||
        Request::is('staff-id-cards')
            ? 'here show'
            : '' }}">

        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <i class="fa-solid fa-user-shield fs-2"></i>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Administrator') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('assign_shift'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('assign-shifts') ? 'active' : '' }}"
                        href="{{ url('assign-shifts') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Assign Shift') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('assign_subject'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('assign-subjects') ? 'active' : '' }}"
                        href="{{ url('assign-subjects') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Assign Subject') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('assign_class'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('assign-class') ? 'active' : '' }}"
                        href="{{ url('assign-class') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Assign Class') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('notice'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('notices') || Request::is('notices/*') ? 'active' : '' }}"
                        href="{{ route('notices.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Notice') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('event'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('events') || Request::is('events/*') ? 'active' : '' }}"
                        href="{{ route('events.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Events') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('contact_message'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('contact-message') ? 'active' : '' }}"
                        href="{{ url('contact-message') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Contact Message') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('user_activities'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('user-logs') ? 'active' : '' }}"
                        href="{{ url('user-logs') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('User Activities') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('id_card_student'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('student-id-cards') ? 'active' : '' }}"
                        href="{{ url('student-id-cards') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Student ID Cards') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('id_card_teacher'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('teacher-id-cards') ? 'active' : '' }}"
                        href="{{ url('teacher-id-cards') }}" target="_blank">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Teacher ID Cards') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('id_card_staff'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('staff-id-cards') ? 'active' : '' }}"
                        href="{{ url('staff-id-cards') }}" target="_blank">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Staff ID Cards') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif
        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

@include('layouts.menus.partials.system_module')

@if (
    $user->can('system_settings') ||
        $user->can('role_index') ||
        $user->can('user_index') ||
        $user->can('database_backup') ||
        $user->can('branches'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('administration/general_settings') ||
        Request::is('roles') ||
        Request::is('roles/*') ||
        Request::is('users') ||
        Request::is('users/*') ||
        Request::is('institutes') ||
        Request::is('institutes/*') ||
        Request::is('branches') ||
        Request::is('branches/*') ||
        Request::is('payment-gateways') ||
        Request::is('payment-gateways/*') ||
        Request::is('custom-domain') ||
        Request::is('administration-backup-database')
            ? 'here show'
            : '' }}">

        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3"
                            d="M4.05424 15.1982C8.34524 7.76818 13.5782 3.26318 20.9282 2.01418C21.0729 1.98837 21.2216 1.99789 21.3618 2.04193C21.502 2.08597 21.6294 2.16323 21.7333 2.26712C21.8372 2.37101 21.9144 2.49846 21.9585 2.63863C22.0025 2.7788 22.012 2.92754 21.9862 3.07218C20.7372 10.4222 16.2322 15.6552 8.80224 19.9462L4.05424 15.1982ZM3.81924 17.3372L2.63324 20.4482C2.58427 20.5765 2.5735 20.7163 2.6022 20.8507C2.63091 20.9851 2.69788 21.1082 2.79503 21.2054C2.89218 21.3025 3.01536 21.3695 3.14972 21.3982C3.28408 21.4269 3.42387 21.4161 3.55224 21.3672L6.66524 20.1802L3.81924 17.3372ZM16.5002 5.99818C16.2036 5.99818 15.9136 6.08615 15.6669 6.25097C15.4202 6.41579 15.228 6.65006 15.1144 6.92415C15.0009 7.19824 14.9712 7.49984 15.0291 7.79081C15.0869 8.08178 15.2298 8.34906 15.4396 8.55884C15.6494 8.76862 15.9166 8.91148 16.2076 8.96935C16.4986 9.02723 16.8002 8.99753 17.0743 8.884C17.3484 8.77046 17.5826 8.5782 17.7474 8.33153C17.9123 8.08486 18.0002 7.79485 18.0002 7.49818C18.0002 7.10035 17.8422 6.71882 17.5609 6.43752C17.2796 6.15621 16.8981 5.99818 16.5002 5.99818Z"
                            fill="currentColor"></path>
                        <path
                            d="M4.05423 15.1982L2.24723 13.3912C2.15505 13.299 2.08547 13.1867 2.04395 13.0632C2.00243 12.9396 1.9901 12.8081 2.00793 12.679C2.02575 12.5498 2.07325 12.4266 2.14669 12.3189C2.22013 12.2112 2.31752 12.1219 2.43123 12.0582L9.15323 8.28918C7.17353 10.3717 5.4607 12.6926 4.05423 15.1982ZM8.80023 19.9442L10.6072 21.7512C10.6994 21.8434 10.8117 21.9129 10.9352 21.9545C11.0588 21.996 11.1903 22.0083 11.3195 21.9905C11.4486 21.9727 11.5718 21.9252 11.6795 21.8517C11.7872 21.7783 11.8765 21.6809 11.9402 21.5672L15.7092 14.8442C13.6269 16.8245 11.3061 18.5377 8.80023 19.9442ZM7.04023 18.1832L12.5832 12.6402C12.7381 12.4759 12.8228 12.2577 12.8195 12.032C12.8161 11.8063 12.725 11.5907 12.5653 11.4311C12.4057 11.2714 12.1901 11.1803 11.9644 11.1769C11.7387 11.1736 11.5205 11.2583 11.3562 11.4132L5.81323 16.9562L7.04023 18.1832Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Master Configuration') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('system_settings'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('administration/general_settings') ? 'active' : '' }}"
                        href="{{ url('administration/general_settings') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('System Settings') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->

                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('custom-domain') ? 'active' : '' }}"
                        href="{{ route('custom-domain.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Custom Domain') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('role_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('roles') || Request::is('roles/*') ? 'active' : '' }}"
                        href="{{ route('roles.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('roles') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('user_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('users') || Request::is('users/*') ? 'active' : '' }}"
                        href="{{ route('users.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('users') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('branches'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('institutes') || Request::is('institutes/*') ? 'active' : '' }}"
                        href="{{ route('institutes.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('institutes') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('branches'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('branches') || Request::is('branches/*') ? 'active' : '' }}"
                        href="{{ route('branches.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('branches') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('system_settings'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('payment-gateways') || Request::is('payment-gateways/*') ? 'active' : '' }}"
                        href="{{ route('payment-gateways.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Payment Gateways') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif
        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

@include('layouts.menus.partials.cms_module')

@include('layouts.menus.partials.whatsapp_module')

@include('layouts.menus.partials.hostel')

@include('layouts.menus.partials.inventory')

@include('layouts.menus.partials.transport')

@include('layouts.menus.partials.google_meet')

@if (
    $user->can('ai_chat_index') ||
        $user->can('ai_content_writer_index') ||
        $user->can('ai_insights_index') ||
        $user->can('ai_settings_index'))

    <!--begin:Menu item AI Assistant-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('ai/*') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <i class="fa-solid fa-robot fs-2"></i>
            </span>
            <span class="menu-title">{{ _lang('AI Assistant') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">

            @if ($user->can('ai_chat_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('ai/chat') ? 'active' : '' }}" href="{{ route('ai.chat.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Chat') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('ai_content_writer_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('ai/writer') ? 'active' : '' }}" href="{{ route('ai.writer.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Content Writer') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('ai_insights_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('ai/insights') ? 'active' : '' }}" href="{{ route('ai.insights.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Data Insights') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('ai_settings_index'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('ai/settings') ? 'active' : '' }}" href="{{ route('ai.settings.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Settings') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif
        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link" href="#"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <span class="menu-icon">
            <i class="fa-solid fa-right-from-bracket fs-2"></i>
            <!--end::Svg Icon-->
        </span>
        <span class="menu-title">{{ _lang('Logout') }}</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <!--end:Menu link-->
</div>
