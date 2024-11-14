@if (
    $user->can('routine_management_syllabuses') ||
        $user->can('routine_management_assignments') ||
        $user->can('routine_management_class_routine') ||
        $user->can('routine_management_exam_routine') ||
        $user->can('routine_management_admit_seat_plan'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('class_routines') || Request::is('class_routines/*') || Request::is('reports/student_id_card') || Request::is('reports/student_id_card/view') || Request::is('reports/class_routine/view') || Request::is('syllabus') || Request::is('syllabus/*') || Request::is('assignments') || Request::is('assignments/*') || Request::is('exam-essentials') || Request::is('exam-routines') || Request::is('exam-routines/*') ? 'here show' : '' }}">

        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M8.7 4.19995L4 6.30005V18.8999L8.7 16.8V19L3.1 21.5C2.6 21.7 2 21.4 2 20.8V6C2 5.4 2.3 4.89995 2.9 4.69995L8.7 2.09998V4.19995Z"
                            fill="currentColor"></path>
                        <path
                            d="M15.3 19.8L20 17.6999V5.09992L15.3 7.19989V4.99994L20.9 2.49994C21.4 2.29994 22 2.59989 22 3.19989V17.9999C22 18.5999 21.7 19.1 21.1 19.3L15.3 21.8998V19.8Z"
                            fill="currentColor"></path>
                        <path opacity="0.3" d="M15.3 7.19995L20 5.09998V17.7L15.3 19.8V7.19995Z" fill="currentColor">
                        </path>
                        <path opacity="0.3"
                            d="M8.70001 4.19995V2L15.4 5V7.19995L8.70001 4.19995ZM8.70001 16.8V19L15.4 22V19.8L8.70001 16.8Z"
                            fill="currentColor"></path>
                        <path opacity="0.3" d="M8.7 16.8L4 18.8999V6.30005L8.7 4.19995V16.8Z" fill="currentColor">
                        </path>
                    </svg>
                </span>

                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Routine Management') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('routine_management_syllabuses'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('syllabus') || Request::is('syllabus/*') ? 'active' : '' }}"
                        href="{{ route('syllabus.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Syllabus') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('routine_management_assignments'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('assignments') || Request::is('assignments/*') ? 'active' : '' }}"
                        href="{{ route('assignments.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Assignments') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('routine_management_class_routine'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('class_routines') || Request::is('class_routines/*') ? 'active' : '' }}"
                        href="{{ url('class_routines') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Class Routine') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('routine_management_exam_routine'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('exam-routines') || Request::is('exam-routines/*') ? 'active' : '' }}"
                        href="{{ url('exam-routines') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Exam Routine') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('routine_management_admit_seat_plan'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('exam-essentials') ? 'active' : '' }}"
                        href="{{ route('exam-essentials.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Admit & Seat Plan') }}</span>
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
