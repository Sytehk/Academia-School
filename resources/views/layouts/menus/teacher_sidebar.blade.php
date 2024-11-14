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

<!--begin:Menu item-->
<div data-kt-menu-trigger="click"
    class="menu-item menu-accordion {{ Request::is('student-attendance') || Request::is('exams-attendance') || Request::is('reports/student_attendance_report') || Request::is('reports/student_attendance_report/view') || Request::is('reports-student_attendance_date_to_date/view') || Request::is('reports-student_attendance_date_to_date') ? 'here show' : '' }}">
    <!--begin:Menu link-->
    <span class="menu-link">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Student Attendance') }}</span>
        <span class="menu-arrow"></span>
    </span>
    <!--end:Menu link-->
    <!--begin:Menu sub-->
    <div class="menu-sub menu-sub-accordion">
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

        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link {{ Request::is('teacher/qr-attendance') ? 'active' : '' }}"
                href="{{ url('teacher/qr-attendance') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ _lang('QR Attendance') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->

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

        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link {{ Request::is('student_attendance/delete') ? 'active' : '' }}"
                href="{{ route('student_attendance_delete') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ _lang('Attendance Delete') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    </div>
    <!--end:Menu sub-->
</div>
<!--end:Menu item-->

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Request::is('zoom') || Request::is('zoom/*') ? 'active' : '' }}"
        href="{{ route('zoom.index') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Zoom Meeting') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Request::is('google-meet') || Request::is('google-meet/*') ? 'active' : '' }}"
        href="{{ route('google-meet.index') }}">
        <span class="menu-icon">
            <span class="svg-icon svg-icon-2">
                <i class="fa-solid fa-video fs-2"></i>
            </span>
        </span>
        <span class="menu-title">{{ _lang('Google Meet') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('teacher/assignments') ? 'active' : '' }}"
        href="{{ url('teacher/assignments') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Assignment') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('syllabus') ? 'active' : '' }}" href="{{ url('syllabus') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Syllabus') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('teacher/class-schedule') ? 'active' : '' }}"
        href="{{ url('teacher/class-schedule') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Class Schedule') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('resources') ? 'active' : '' }}" href="{{ url('resources') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Resources') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('classlessons') ? 'active' : '' }}" href="{{ url('classlessons') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Class Lessons') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('prayers') ? 'active' : '' }}" href="{{ url('prayers') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Prayers') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('behaviors') ? 'active' : '' }}" href="{{ url('behaviors') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Behaviors') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Route::is('gamifications') ? 'active' : '' }}" href="{{ url('gamifications') }}">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
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
        <span class="menu-title">{{ _lang('Gamification') }}</span>
    </a>
    <!--end:Menu link-->
</div>

<!--begin:Menu item-->
<div data-kt-menu-trigger="click"
    class="menu-item menu-accordion {{ Request::is('semester-exam-settings-exam-startup') || Request::is('semester-exam-settings-mark-config') || Request::is('semester-exam-settings-mark-config/*') || Request::is('remarks-config') || Request::is('remarks-config/*') || Request::is('mark-input-section-wise') || Request::is('mark-input-section-wise-class') || Request::is('mark-input-section-wise-class/*') || Request::is('exam-result-view') ? 'here show' : '' }}">
    <!--begin:Menu link-->
    <span class="menu-link">
        <span class="menu-icon">
            <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
            <span class="svg-icon svg-icon-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20 7H3C2.4 7 2 6.6 2 6V3C2 2.4 2.4 2 3 2H20C20.6 2 21 2.4 21 3V6C21 6.6 20.6 7 20 7ZM7 9H3C2.4 9 2 9.4 2 10V20C2 20.6 2.4 21 3 21H7C7.6 21 8 20.6 8 20V10C8 9.4 7.6 9 7 9Z"
                        fill="currentColor"></path>
                    <path opacity="0.3"
                        d="M20 21H11C10.4 21 10 20.6 10 20V10C10 9.4 10.4 9 11 9H20C20.6 9 21 9.4 21 10V20C21 20.6 20.6 21 20 21Z"
                        fill="currentColor"></path>
                </svg>
            </span>
            <!--end::Svg Icon-->
        </span>
        <span class="menu-title">{{ _lang('Exam Module') }}</span>
        <span class="menu-arrow"></span>
    </span>
    <!--end:Menu link-->
    <!--begin:Menu sub-->
    <div class="menu-sub menu-sub-accordion">
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link {{ Request::is('mark-input-section-wise') || Request::is('mark-input-section-wise-class') || Request::is('mark-input-section-wise-class/*') ? 'active' : '' }}"
                href="{{ route('mark-input-section-wise') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ _lang('Mark Input') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->

        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link {{ Request::is('exam-result-view') ? 'active' : '' }}"
                href="{{ url('exam-result-view') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ _lang('Exam Result') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    </div>
    <!--end:Menu sub-->
</div>
<!--end:Menu item-->

<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link" href="#"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <span class="menu-icon">
            <i class="fa-solid fa-right-from-bracket fs-2"></i>
        </span>
        <span class="menu-title">{{ _lang('Logout') }}</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <!--end:Menu link-->
</div>
