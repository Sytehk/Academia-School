{{-- Parent panel sidebar. Every link targets a `parent.*` route and is active
     via routeIs() so the highlight is exact. Data is scoped to the active child. --}}

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}"
        href="{{ route('parent.dashboard') }}">
        <span class="menu-icon"><i class="fa-solid fa-gauge-high fs-2"></i></span>
        <span class="menu-title">{{ _lang('Dashboard') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.profile') ? 'active' : '' }}"
        href="{{ route('parent.profile') }}">
        <span class="menu-icon"><i class="fa-solid fa-user fs-2"></i></span>
        <span class="menu-title">{{ _lang('Child Profile') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.attendance') ? 'active' : '' }}"
        href="{{ route('parent.attendance') }}">
        <span class="menu-icon"><i class="fa-solid fa-calendar-check fs-2"></i></span>
        <span class="menu-title">{{ _lang('Attendance') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.class-routine') ? 'active' : '' }}"
        href="{{ route('parent.class-routine') }}">
        <span class="menu-icon"><i class="fa-solid fa-table-cells fs-2"></i></span>
        <span class="menu-title">{{ _lang('Class Routine') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.exam-routine') ? 'active' : '' }}"
        href="{{ route('parent.exam-routine') }}">
        <span class="menu-icon"><i class="fa-solid fa-calendar-days fs-2"></i></span>
        <span class="menu-title">{{ _lang('Exam Routine') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.exam-results') ? 'active' : '' }}"
        href="{{ route('parent.exam-results') }}">
        <span class="menu-icon"><i class="fa-solid fa-award fs-2"></i></span>
        <span class="menu-title">{{ _lang('Exam Results') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.subjects') ? 'active' : '' }}"
        href="{{ route('parent.subjects') }}">
        <span class="menu-icon"><i class="fa-solid fa-book fs-2"></i></span>
        <span class="menu-title">{{ _lang('Subjects') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.assignments') ? 'active' : '' }}"
        href="{{ route('parent.assignments') }}">
        <span class="menu-icon"><i class="fa-solid fa-file-pen fs-2"></i></span>
        <span class="menu-title">{{ _lang('Assignments') }}</span>
    </a>
</div>

{{-- Fees --}}
<div data-kt-menu-trigger="click"
    class="menu-item menu-accordion {{ request()->routeIs('parent.fees.*') ? 'here show' : '' }}">
    <span class="menu-link">
        <span class="menu-icon"><i class="fa-solid fa-money-bill-wave fs-2"></i></span>
        <span class="menu-title">{{ _lang('Fees') }}</span>
        <span class="menu-arrow"></span>
    </span>
    <div class="menu-sub menu-sub-accordion">
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('parent.fees.unpaid') ? 'active' : '' }}"
                href="{{ route('parent.fees.unpaid') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('Unpaid Invoice') }}</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('parent.fees.history') ? 'active' : '' }}"
                href="{{ route('parent.fees.history') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('Payment History') }}</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('parent.fees.attendance-fine') ? 'active' : '' }}"
                href="{{ route('parent.fees.attendance-fine') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('Attendance Fine') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.library') ? 'active' : '' }}"
        href="{{ route('parent.library') }}">
        <span class="menu-icon"><i class="fa-solid fa-book-open-reader fs-2"></i></span>
        <span class="menu-title">{{ _lang('Library') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.behaviors') ? 'active' : '' }}"
        href="{{ route('parent.behaviors') }}">
        <span class="menu-icon"><i class="fa-solid fa-star fs-2"></i></span>
        <span class="menu-title">{{ _lang('Behaviour') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.notices') ? 'active' : '' }}"
        href="{{ route('parent.notices') }}">
        <span class="menu-icon"><i class="fa-solid fa-bullhorn fs-2"></i></span>
        <span class="menu-title">{{ _lang('Notice Board') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link {{ request()->routeIs('parent.events') ? 'active' : '' }}"
        href="{{ route('parent.events') }}">
        <span class="menu-icon"><i class="fa-solid fa-calendar-day fs-2"></i></span>
        <span class="menu-title">{{ _lang('Events') }}</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link" href="#"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <span class="menu-icon"><i class="fa-solid fa-right-from-bracket fs-2"></i></span>
        <span class="menu-title">{{ _lang('Logout') }}</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>
