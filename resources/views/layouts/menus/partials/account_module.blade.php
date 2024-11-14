@if (
    $user->can('accounting_management_ledgers') ||
        $user->can('accounting_management_funds') ||
        $user->can('accounting_management_categories') ||
        $user->can('accounting_management_groups') ||
        $user->can('accounting_management_payment') ||
        $user->can('accounting_management_receipt') ||
        $user->can('accounting_management_contra') ||
        $user->can('accounting_management_journal') ||
        $user->can('accounting_management_fund_transfer') ||
        $user->can('accounting_management_chart_of_accounts') ||
        $user->can('accounting_report_balance_sheet') ||
        $user->can('accounting_report_trial_balance') ||
        $user->can('accounting_report_cash_flow_statement') ||
        $user->can('accounting_report_cash_flow_details') ||
        $user->can('accounting_report_cash_book_account') ||
        $user->can('accounting_report_ledger_book_account') ||
        $user->can('accounting_report_income_statement') ||
        $user->can('accounting_report_cash_summary'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('accounting-categories') ||
        Request::is('accounting-categories/*') ||
        Request::is('accounting-groups') ||
        Request::is('accounting-groups/*') ||
        Request::is('accounting-ledgers') ||
        Request::is('accounting-ledgers/*') ||
        Request::is('accounting-funds') ||
        Request::is('accounting-funds/*') ||
        Request::is('account-transaction-payment') ||
        Request::is('account-contra-transfers') ||
        Request::is('journal-transactions') ||
        Request::is('account-fund-transfers') ||
        Request::is('chart-of-accounts')
            ? 'here show'
            : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M20 19.725V18.725C20 18.125 19.6 17.725 19 17.725H5C4.4 17.725 4 18.125 4 18.725V19.725H3C2.4 19.725 2 20.125 2 20.725V21.725H22V20.725C22 20.125 21.6 19.725 21 19.725H20Z"
                            fill="currentColor"></path>
                        <path opacity="0.3"
                            d="M22 6.725V7.725C22 8.325 21.6 8.725 21 8.725H18C18.6 8.725 19 9.125 19 9.725C19 10.325 18.6 10.725 18 10.725V15.725C18.6 15.725 19 16.125 19 16.725V17.725H15V16.725C15 16.125 15.4 15.725 16 15.725V10.725C15.4 10.725 15 10.325 15 9.725C15 9.125 15.4 8.725 16 8.725H13C13.6 8.725 14 9.125 14 9.725C14 10.325 13.6 10.725 13 10.725V15.725C13.6 15.725 14 16.125 14 16.725V17.725H10V16.725C10 16.125 10.4 15.725 11 15.725V10.725C10.4 10.725 10 10.325 10 9.725C10 9.125 10.4 8.725 11 8.725H8C8.6 8.725 9 9.125 9 9.725C9 10.325 8.6 10.725 8 10.725V15.725C8.6 15.725 9 16.125 9 16.725V17.725H5V16.725C5 16.125 5.4 15.725 6 15.725V10.725C5.4 10.725 5 10.325 5 9.725C5 9.125 5.4 8.725 6 8.725H3C2.4 8.725 2 8.325 2 7.725V6.725L11 2.225C11.6 1.925 12.4 1.925 13.1 2.225L22 6.725ZM12 3.725C11.2 3.725 10.5 4.425 10.5 5.225C10.5 6.025 11.2 6.725 12 6.725C12.8 6.725 13.5 6.025 13.5 5.225C13.5 4.425 12.8 3.725 12 3.725Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Accounts Management') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">

            @if ($user->can('accounting_management_ledgers'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('accounting-ledgers') ? 'active' : '' }}"
                        href="{{ url('accounting-ledgers') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Ledger') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_funds'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('accounting-funds') ? 'active' : '' }}"
                        href="{{ url('accounting-funds') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Fund') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_categories'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('accounting-categories') ? 'active' : '' }}"
                        href="{{ url('accounting-categories') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Category') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_groups'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('accounting-groups') ? 'active' : '' }}"
                        href="{{ url('accounting-groups') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Group') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_payment'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('account-transaction-payment') && request()->query('type') !== 'receipt' ? 'active' : '' }}"
                        href="{{ url('account-transaction-payment') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Payment') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_receipt'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('account-transaction-payment') && request()->query('type') === 'receipt' ? 'active' : '' }}"
                        href="{{ url('account-transaction-payment?type=receipt') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Receipt') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_contra'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('account-contra-transfers') ? 'active' : '' }}"
                        href="{{ url('account-contra-transfers') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Contra') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_journal'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('journal-transactions') ? 'active' : '' }}"
                        href="{{ url('journal-transactions') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Journal') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_fund_transfer'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('account-fund-transfers') ? 'active' : '' }}"
                        href="{{ url('account-fund-transfers') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Fund Transfer') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_management_chart_of_accounts'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Route::is('chart-of-accounts.index') ? 'active' : '' }}"
                        href="{{ route('chart-of-accounts.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Chart Of Accounts') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->



    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('balance-sheet') ||
        Request::is('balance-sheet/*') ||
        Request::is('trial-balance') ||
        Request::is('trial-balance/*') ||
        Request::is('cash-flow-statement') ||
        Request::is('cash-flow-statement/*') ||
        Request::is('cash-flow-details') ||
        Request::is('cash-flow-details/*') ||
        Request::is('cash-book-account') ||
        Request::is('cash-book-account/*') ||
        Request::is('ledger-book-account') ||
        Request::is('ledger-book-account/*') ||
        Request::is('income-statement') ||
        Request::is('income-statement/*') ||
        Request::is('income-statement-details') ||
        Request::is('income-statement-details/*') ||
        Request::is('cash-summary') ||
        Request::is('cash-summary/*')
            ? 'here show'
            : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <i class="fa-solid fa-chart-line fs-2"></i>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Accounting Reports') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">

            @if ($user->can('accounting_report_balance_sheet'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('balance-sheet') ? 'active' : '' }}"
                        href="{{ url('balance-sheet') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Balance Sheet') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_report_trial_balance'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('trial-balance') ? 'active' : '' }}"
                        href="{{ url('trial-balance') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Trial Balance') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_report_cash_flow_statement'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('cash-flow-statement') ? 'active' : '' }}"
                        href="{{ url('cash-flow-statement') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Cash Flow Statement') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_report_cash_flow_details'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('cash-flow-details') ? 'active' : '' }}"
                        href="{{ url('cash-flow-details') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Cash Flow Details') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_report_cash_book_account'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('cash-book-account') ? 'active' : '' }}"
                        href="{{ url('cash-book-account') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Cash Book Account') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_report_ledger_book_account'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('ledger-book-account') ? 'active' : '' }}"
                        href="{{ url('ledger-book-account') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Ledger Book Account') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_report_income_statement'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('income-statement') ? 'active' : '' }}"
                        href="{{ url('income-statement') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Income Statement') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->

                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('income-statement-details') ? 'active' : '' }}"
                        href="{{ url('income-statement-details') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Income Statement Details') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('accounting_report_cash_summary'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('cash-summary') ? 'active' : '' }}"
                        href="{{ url('cash-summary') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Cash Summary') }}</span>
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
