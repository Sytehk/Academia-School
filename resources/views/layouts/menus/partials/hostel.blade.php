@if ($user->can('hostel_panel'))
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion
    {{ Request::is('hostels*') ||
    Request::is('hostel-categories*') ||
    Request::is('hostel-members*') ||
    Request::is('hostel-buildings*') ||
    Request::is('hostel-floors*') ||
    Request::is('beds*') ||
    Request::is('rooms*') ||
    Request::is('room-members*') ||
    Request::is('meals*') ||
    Request::is('meal-plans*') ||
    Request::is('meal-entries*') ||
    Request::is('hostel-bills*') ||
    Request::is('hostel-leaves*') ||
    Request::is('hostel-dashboard*') ||
    Request::is('hostel-seat-map*') ||
    Request::is('hostel-collections*') ||
    Request::is('hostel-reports*')
        ? 'here show'
        : '' }}">

        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">

                <!-- Hostel Icon -->
                <i class="fa-solid fa-building fs-2"></i>

            </span>

            <span class="menu-title">
                {{ _lang('Hostel Management') }}
            </span>

            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->


        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">


            <!-- Hostel -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostels*') ? 'active' : '' }}" href="{{ route('hostels.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Hostels') }}</span>
                </a>
            </div>


            <!-- Categories -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostel-categories*') ? 'active' : '' }}"
                    href="{{ route('hostel-categories.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Hostel Categories') }}</span>
                </a>
            </div>


            <!-- Hostel Members -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostel-members*') ? 'active' : '' }}"
                    href="{{ route('hostel-members.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Hostel Members') }}</span>
                </a>
            </div>

            <!-- Buildings -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostel-buildings*') ? 'active' : '' }}"
                    href="{{ route('hostel-buildings.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Buildings') }}</span>
                </a>
            </div>

            <!-- Floors -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostel-floors*') ? 'active' : '' }}"
                    href="{{ route('hostel-floors.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Floors') }}</span>
                </a>
            </div>

            <!-- Rooms -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('rooms*') ? 'active' : '' }}" href="{{ route('rooms.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Rooms') }}</span>
                </a>
            </div>

            <!-- Beds -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('beds*') ? 'active' : '' }}" href="{{ route('beds.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Beds') }}</span>
                </a>
            </div>


            <!-- Room Members -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('room-members*') ? 'active' : '' }}"
                    href="{{ route('room-members.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Room Members') }}</span>
                </a>
            </div>


            <!-- Meals -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('meals*') ? 'active' : '' }}" href="{{ route('meals.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Meals') }}</span>
                </a>
            </div>


            <!-- Meal Plans -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('meal-plans*') ? 'active' : '' }}"
                    href="{{ route('meal-plans.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Meal Plans') }}</span>
                </a>
            </div>


            <!-- Meal Entries -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('meal-entries*') ? 'active' : '' }}"
                    href="{{ route('meal-entries.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Meal Entries') }}</span>
                </a>
            </div>


            <!-- Hostel Bills -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostel-bills*') ? 'active' : '' }}"
                    href="{{ route('hostel-bills.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Hostel Bills') }}</span>
                </a>
            </div>

            @can('hostel_leave_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('hostel-leaves*') ? 'active' : '' }}"
                        href="{{ route('hostel-leaves.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Hostel Leaves') }}</span>
                    </a>
                </div>
            @endcan

            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostel-dashboard*') ? 'active' : '' }}"
                    href="{{ route('hostel-dashboard.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Hostel Dashboard') }}</span>
                </a>
            </div>

            <div class="menu-item">
                <a class="menu-link {{ Request::is('hostel-seat-map*') ? 'active' : '' }}"
                    href="{{ route('hostel-seat-map.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Seat Map') }}</span>
                </a>
            </div>

            @can('hostel_collection_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('hostel-collections*') ? 'active' : '' }}"
                        href="{{ route('hostel-collections.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Hostel Fee Collection') }}</span>
                    </a>
                </div>
            @endcan

            @can('hostel_report_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('hostel-reports*') ? 'active' : '' }}"
                        href="{{ route('hostel-reports.collection') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Hostel Reports') }}</span>
                    </a>
                </div>
            @endcan

        </div>
        <!--end:Menu sub-->

    </div>
    <!--end:Menu item-->
@endif
