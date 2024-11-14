@if (
    can_access_system('system.update.view') ||
        can_access_system('system.module.view') ||
        can_access_system('system.settings.manage'))

    <!--begin:Menu item System-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('system*') ? 'here show' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="fa-solid fa-server fs-2"></i>
            </span>
            <span class="menu-title">{{ _lang('System') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion">
            @if (can_access_system('system.update.view'))
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('system/information') ? 'active' : '' }}"
                        href="{{ route('system.information') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('System Information') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('system/update*') ? 'active' : '' }}"
                        href="{{ route('system.update.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('System Update') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('system/update/history*') ? 'active' : '' }}"
                        href="{{ route('system.update.history') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Update History') }}</span>
                    </a>
                </div>
            @endif

            @if (can_access_system('system.module.view'))
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('system/modules*') ? 'active' : '' }}"
                        href="{{ route('system.modules.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('Modules') }}</span>
                    </a>
                </div>
            @endif

            @if (can_access_system('system.settings.manage'))
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('system/settings*') ? 'active' : '' }}"
                        href="{{ route('system.settings.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">{{ _lang('System Settings') }}</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
    <!--end:Menu item System-->
@endif
