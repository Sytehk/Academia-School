@if ($user->can('transport_panel'))
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion
        {{ Request::is('buses*') || Request::is('drivers*') || Request::is('bus-routes*') || Request::is('bus-stops*') || Request::is('transport-members*') || Request::is('vehicle-types*') || Request::is('vehicle-categories*') || Request::is('helpers*') || Request::is('transport-dashboard*') || Request::is('transport-collections*') || Request::is('transport-reports*') ? 'here show' : '' }}">

        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">

                <!-- Transport Icon -->
                <i class="fa-solid fa-bus fs-2"></i>

            </span>

            <span class="menu-title">
                {{ _lang('Transport Management') }}
            </span>

            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">

            <!-- Buses -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('buses*') ? 'active' : '' }}" href="{{ route('buses.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Buses') }}</span>
                </a>
            </div>

            <!-- Drivers -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('drivers*') ? 'active' : '' }}" href="{{ route('drivers.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Drivers') }}</span>
                </a>
            </div>

            <!-- Bus Routes -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('bus-routes*') ? 'active' : '' }}"
                    href="{{ route('bus-routes.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Bus Routes') }}</span>
                </a>
            </div>

            <!-- Bus Stops -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('bus-stops*') ? 'active' : '' }}"
                    href="{{ route('bus-stops.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Bus Stops') }}</span>
                </a>
            </div>

            <!-- Transport Members -->
            <div class="menu-item">
                <a class="menu-link {{ Request::is('transport-members*') ? 'active' : '' }}"
                    href="{{ route('transport-members.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Transport Members') }}</span>
                </a>
            </div>

            @can('vehicle_type_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('vehicle-types*') ? 'active' : '' }}"
                        href="{{ route('vehicle-types.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Vehicle Types') }}</span>
                    </a>
                </div>
            @endcan

            @can('vehicle_category_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('vehicle-categories*') ? 'active' : '' }}"
                        href="{{ route('vehicle-categories.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Vehicle Categories') }}</span>
                    </a>
                </div>
            @endcan

            @can('helper_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('helpers*') ? 'active' : '' }}"
                        href="{{ route('helpers.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Helpers') }}</span>
                    </a>
                </div>
            @endcan

            <div class="menu-item">
                <a class="menu-link {{ Request::is('transport-dashboard*') ? 'active' : '' }}"
                    href="{{ route('transport-dashboard.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Transport Dashboard') }}</span>
                </a>
            </div>

            @can('transport_collection_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('transport-collections*') ? 'active' : '' }}"
                        href="{{ route('transport-collections.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Transport Fee Collection') }}</span>
                    </a>
                </div>
            @endcan

            @can('transport_report_index')
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('transport-reports*') ? 'active' : '' }}"
                        href="{{ route('transport-reports.collection') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Transport Reports') }}</span>
                    </a>
                </div>
            @endcan

        </div>
        <!--end:Menu sub-->

    </div>
    <!--end:Menu item-->
@endif
