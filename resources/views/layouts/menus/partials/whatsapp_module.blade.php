@if (
    $user->can('whats_app_setting_index') ||
        $user->can('whats_app_setting_create') ||
        $user->can('whats_app_setting_update') ||
        $user->can('whats_app_setting_delete') ||
        $user->can('whats_app_template_index') ||
        $user->can('whats_app_template_create') ||
        $user->can('whats_app_template_update') ||
        $user->can('whats_app_template_delete') ||
        $user->can('whats_app_log_index'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('whats-app-settings') ||
        Request::is('whats-app-settings/*') ||
        Request::is('whats-app-templates') ||
        Request::is('whats-app-templates/*') ||
        Request::is('whats-app-logs') ||
        Request::is('whats-app-logs/*')
            ? 'here show'
            : '' }}">

        <!-- Menu link -->
        <span class="menu-link">
            <span class="menu-icon">
                <span class="svg-icon svg-icon-2">

                    {{-- WhatsApp Icon --}}
                    <i class="fa-brands fa-whatsapp fs-2"></i>
                </span>
            </span>

            <span class="menu-title">
                {{ _lang('WhatsApp') }}
            </span>

            <span class="menu-arrow"></span>
        </span>

        <!-- Sub Menu -->
        <div class="menu-sub menu-sub-accordion">
            {{-- WhatsApp Settings --}}
            @if ($user->can('whats_app_setting_index'))
                <div class="menu-item">

                    <a class="menu-link {{ Request::is('whats-app-settings') || Request::is('whats-app-settings/*') ? 'active' : '' }}"
                        href="{{ route('whats-app-settings.index') }}">


                        <span class="menu-bullet">

                            <span class="bullet bullet-dot"></span>

                        </span>


                        <span class="menu-title">
                            {{ _lang('WhatsApp Settings') }}
                        </span>
                    </a>

                </div>
            @endif

            {{-- WhatsApp Templates --}}
            @if ($user->can('whats_app_template_index'))
                <div class="menu-item">

                    <a class="menu-link {{ Request::is('whats-app-templates') || Request::is('whats-app-templates/*') ? 'active' : '' }}"
                        href="{{ route('whats-app-templates.index') }}">

                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>

                        <span class="menu-title">
                            {{ _lang('WhatsApp Templates') }}
                        </span>
                    </a>
                </div>
            @endif

            {{-- WhatsApp Delivery Logs --}}
            @if ($user->can('whats_app_log_index'))
                <div class="menu-item">

                    <a class="menu-link {{ Request::is('whats-app-logs') || Request::is('whats-app-logs/*') ? 'active' : '' }}"
                        href="{{ route('whats-app-logs.index') }}">

                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>

                        <span class="menu-title">
                            {{ _lang('WhatsApp Logs') }}
                        </span>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
