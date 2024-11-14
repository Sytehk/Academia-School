{{-- @if ($user->can('google_meet_panel') || $user->can('google_meet_index')) --}}
<!--begin:Menu item-->
<div data-kt-menu-trigger="click"
    class="menu-item menu-accordion
        {{ Request::is('google-meet*') ? 'here show' : '' }}">

    <!--begin:Menu link-->
    <span class="menu-link">
        <span class="menu-icon">

            <!-- Google Meet Icon -->
            <i class="fa-solid fa-video fs-2"></i>

        </span>

        <span class="menu-title">
            {{ _lang('Google Meet') }}
        </span>

        <span class="menu-arrow"></span>
    </span>
    <!--end:Menu link-->

    <!--begin:Menu sub-->
    <div class="menu-sub menu-sub-accordion">

        {{-- @if ($user->can('google_meet_index')) --}}
        <div class="menu-item">
            <a class="menu-link {{ Request::is('google-meet') || (Request::is('google-meet/*') && !Request::is('google-meet/create')) ? 'active' : '' }}"
                href="{{ route('google-meet.index') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('All Meetings') }}</span>
            </a>
        </div>
        {{-- @endif --}}

        {{-- @if ($user->can('google_meet_create')) --}}
        <div class="menu-item">
            <a class="menu-link {{ Request::is('google-meet/create') ? 'active' : '' }}"
                href="{{ route('google-meet.create') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('Create Meeting') }}</span>
            </a>
        </div>
        {{-- @endif --}}

    </div>
    <!--end:Menu sub-->

</div>
<!--end:Menu item-->
{{-- @endif --}}
