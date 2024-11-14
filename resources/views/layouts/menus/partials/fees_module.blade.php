@if (
    $user->can('fees_management_startup') ||
        $user->can('fees_management_mapping') ||
        $user->can('fees_management_amount_config') ||
        $user->can('fees_management_date_config') ||
        $user->can('fees_management_fine_waiver') ||
        $user->can('fees_management_waiver') ||
        $user->can('fees_management_waiver_config') ||
        $user->can('fees_management_smart_collection') ||
        $user->can('fees_management_paid_info') ||
        $user->can('fees_management_unpaid_info'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('fee-head') ||
        Request::is('fees-mapping') ||
        Request::is('amount-config') ||
        Request::is('date-config') ||
        Request::is('waiver-config') ||
        Request::is('bank-accounts') ||
        Request::is('waivers') ||
        Request::is('attendance-waiver') ||
        Request::is('quick-collection') ||
        Request::is('quick-collection/*') ||
        Request::is('payment-fee-info') ||
        Request::is('unpaid-info') ||
        Route::is('waiver-config.index')
            ? 'here show'
            : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <i class="fa-solid fa-money-bill-wave fs-2"></i>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Fees Management') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">

            @if ($user->can('fees_management_startup'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('fee-head.index') ? 'active' : '' }}"
                        href="{{ route('fee-head.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Fees StartUp') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_mapping'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('fees-mapping.index') ? 'active' : '' }}"
                        href="{{ route('fees-mapping.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Fees Mapping') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_amount_config'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('amount-config.index') ? 'active' : '' }}"
                        href="{{ route('amount-config.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Amount Config') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_date_config'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('date-config') ? 'active' : '' }}"
                        href="{{ route('date-config.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Date Config') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_fine_waiver'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('attendance-waiver.index') ? 'active' : '' }}"
                        href="{{ route('attendance-waiver.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Fine Waiver') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


            @if ($user->can('fees_management_waiver'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('waivers.index') ? 'active' : '' }}"
                        href="{{ route('waivers.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Waiver') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_waiver_config'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('waiver-config.index') ? 'active' : '' }}"
                        href="{{ route('waiver-config.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Waiver Config') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_smart_collection'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('quick-collection.index') || Request::is('quick-collection/*') ? 'active' : '' }}"
                        href="{{ route('quick-collection.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Smart Collection') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_paid_info'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('payment-fee-info') ? 'active' : '' }}"
                        href="{{ route('payment-fee-info') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Paid Info') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('fees_management_unpaid_info'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('unpaid-info') ? 'active' : '' }}"
                        href="{{ route('unpaid-info') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Unpaid Info') }}</span>
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
