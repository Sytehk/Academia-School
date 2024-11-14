@if (
    $user->can('library_management_book_category') ||
        $user->can('library_management_books') ||
        $user->can('library_management_members') ||
        $user->can('library_management_book_issue_search') ||
        $user->can('library_management_book_issue_report') ||
        $user->can('library_management_book_barcode_print'))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('book-categories') ||
        Request::is('book-categories/*') ||
        Request::is('books') ||
        Request::is('books/*') ||
        Request::is('books-bulk-upload') ||
        Request::is('librarymembers') ||
        Request::is('librarymembers/*') ||
        Request::is('books-ber-code') ||
        Request::is('books-ber-code/*') ||
        Request::is('bookissues') ||
        Request::is('bookissues-list') ||
        Request::is('bookissues-list/*') ||
        Request::is('bookissues/*/edit') ||
        Request::is('bookissues-filter-all') ||
        Request::is('books-ber-code-print') ||
        Request::is('books-ber-code-print/*') ||
        Request::is('books-ber-code-page')
            ? 'here show'
            : '' }}">
        <!--begin:Menu link-->
        <span class="menu-link">
            <span class="menu-icon">
                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M18 21.6C16.6 20.4 9.1 20.3 6.3 21.2C5.7 21.4 5.1 21.2 4.7 20.8L2 18C4.2 15.8 10.8 15.1 15.8 15.8C16.2 18.3 17 20.5 18 21.6ZM18.8 2.8C18.4 2.4 17.8 2.20001 17.2 2.40001C14.4 3.30001 6.9 3.2 5.5 2C6.8 3.3 7.4 5.5 7.7 7.7C9 7.9 10.3 8 11.7 8C15.8 8 19.8 7.2 21.5 5.5L18.8 2.8Z"
                            fill="currentColor"></path>
                        <path opacity="0.3"
                            d="M21.2 17.3C21.4 17.9 21.2 18.5 20.8 18.9L18 21.6C15.8 19.4 15.1 12.8 15.8 7.8C18.3 7.4 20.4 6.70001 21.5 5.60001C20.4 7.00001 20.2 14.5 21.2 17.3ZM8 11.7C8 9 7.7 4.2 5.5 2L2.8 4.8C2.4 5.2 2.2 5.80001 2.4 6.40001C2.7 7.40001 3.00001 9.2 3.10001 11.7C3.10001 15.5 2.40001 17.6 2.10001 18C3.20001 16.9 5.3 16.2 7.8 15.8C8 14.2 8 12.7 8 11.7Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </span>
            <span class="menu-title">{{ _lang('Library Management') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($user->can('library_management_book_category'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('book-categories') || Request::is('book-categories/*') ? 'active' : '' }}"
                        href="{{ url('book-categories') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Book Categories') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('library_management_books'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('books') || Request::is('books/*') || Request::is('books-bulk-upload') ? 'active' : '' }}"
                        href="{{ url('books') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ _lang('Books') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('library_management_members'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('librarymembers') || Request::is('librarymembers/*') ? 'active' : '' }}"
                        href="{{ url('librarymembers') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title"> {{ _lang('Members / Library ID') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('library_management_book_issue_search'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('books-ber-code') || Request::is('books-ber-code/*') || Request::is('books-ber-code-page') ? 'active' : '' }}"
                        href="{{ route('books.ber-code') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title"> {{ _lang('Books Issue') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('library_management_book_issue_report'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('bookissues') ||
                    Request::is('bookissues-list') ||
                    Request::is('bookissues-list/*') ||
                    Request::is('bookissues/*/edit')
                        ? 'active'
                        : '' }}"
                        href="{{ url('bookissues') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">
                            @php
                                $bookIssuesOver = Modules\Library\Models\BookIssue::where('status', 1)
                                    ->where('due_date', '<', now())
                                    ->count();
                            @endphp
                            {{ _lang('Book Issue Search') }}
                            <span class="text-danger"> ( {{ $bookIssuesOver }} )</span>
                        </span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->

                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('bookissues-filter-all') ? 'active' : '' }}"
                        href="{{ route('bookissues.filter') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title"> {{ _lang('Book Issues Report') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($user->can('library_management_book_barcode_print'))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{ Request::is('books-ber-code-print') || Request::is('books-ber-code-print/*') ? 'active' : '' }}"
                        href="{{ route('books.ber-code-print') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title"> {{ _lang('Barcode Books Print') }}</span>
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
