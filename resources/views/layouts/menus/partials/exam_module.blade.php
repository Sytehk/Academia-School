@if (
    $user->can('exam_module_exam_start_up') ||
        $user->can('exam_module_mark_config') ||
        $user->can('exam_module_remarks_config') ||
        $user->can('exam_module_mark_input') ||
        $user->can('exam_module_exam_result') ||
        $user->can('exam_module_manage_online_exams') ||
        $user->can('exam_module_manage_assessment_domains'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('semester-exam-settings-exam-startup') || Request::is('semester-exam-settings-mark-config') || Request::is('semester-exam-settings-mark-config/*') || Request::is('remarks-config') || Request::is('remarks-config/*') || Request::is('mark-input-section-wise') || Request::is('mark-input-section-wise-class') || Request::is('mark-input-section-wise-class/*') || Request::is('exam-result-view') || Request::is('grand-final-result') || Request::is('grand-final-result/*') || Request::is('tabulation-sheet*') || Request::is('merit-list-sheet*') || Request::is('empty-mark-sheet*') || Request::is('online-exams*') || Request::is('assessment-domains*') || Request::is('assessment-entry*') || Request::is('result-card-settings*') ? 'here show' : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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

            @if ($user->can('exam_module_exam_start_up'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('semester-exam-settings-exam-startup') ? 'active' : '' }}"
                        href="{{ url('semester-exam-settings-exam-startup') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Exam StartUp') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('exam_module_mark_config'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('semester-exam-settings-mark-config') || Request::is('semester-exam-settings-mark-config/*') ? 'active' : '' }}"
                        href="{{ url('semester-exam-settings-mark-config') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Mark Config') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('exam_module_remarks_config'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('remarks-config') ? 'active' : '' }}"
                        href="{{ route('remarks-config.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Remarks Config') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('exam_module_mark_input'))
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
            @endif

            @if ($user->can('exam_module_exam_result'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('exam-result-view') ? 'active' : '' }}"
                        href="{{ url('exam-result-view') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Exam Result') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('grand-final-result') || Request::is('grand-final-result/*') ? 'active' : '' }}"
                        href="{{ route('grand-final-result.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Grand Final Result') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('tabulation-sheet*') ? 'active' : '' }}"
                        href="{{ route('tabulation-sheet') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Tabulation / Broad Sheet') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('merit-list-sheet*') ? 'active' : '' }}"
                        href="{{ route('merit-list-sheet') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Merit List Sheet') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('result-card-settings*') ? 'active' : '' }}"
                        href="{{ route('result-card-settings.edit') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Result Card Settings') }}</span>
                    </a>
                </div>
            @endif

            @if ($user->can('exam_module_mark_input'))
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('empty-mark-sheet*') ? 'active' : '' }}"
                        href="{{ route('empty-mark-sheet') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Empty Mark Sheet') }}</span>
                    </a>
                </div>
            @endif

            @if ($user->can('exam_module_manage_assessment_domains') || $user->can('exam_module_exam_start_up'))
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('assessment-domains*') ? 'active' : '' }}"
                        href="{{ route('assessment-domains.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Assessment Domains') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('assessment-entry*') ? 'active' : '' }}"
                        href="{{ route('assessment-entry.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Domain Assessment Entry') }}</span>
                    </a>
                </div>
            @endif

            @if ($user->can('exam_module_manage_online_exams') || $user->can('exam_module_exam_start_up'))
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('online-exams*') ? 'active' : '' }}"
                        href="{{ route('online-exams.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Online Examination') }}</span>
                    </a>
                </div>
            @endif

        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif
