@if ($user->can('cms_index'))
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('admission-forms') ||
        Request::is('admission-forms/*') ||
        Request::is('pages') ||
        Request::is('pages/*') ||
        Request::is('banners') ||
        Request::is('banners/*') ||
        Request::is('about-us') ||
        Request::is('about-us/*') ||
        Request::is('faqs') ||
        Request::is('faqs/*') ||
        Request::is('gallery-images') ||
        Request::is('gallery-images/*') ||
        Request::is('mobile-app-sections') ||
        Request::is('mobile-app-sections/*') ||
        Request::is('why-choose-us') ||
        Request::is('why-choose-us/*') ||
        Request::is('policies') ||
        Request::is('policies/*') ||
        Request::is('ready-to-join-us') ||
        Request::is('ready-to-join-us/*') ||
        Request::is('testimonials') ||
        Request::is('testimonials/*') ||
        Request::is('institute-image-settings') ||
        Request::is('institute-image-settings/*')
            ? 'here show'
            : '' }}">

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
            <span class="menu-title">{{ _lang('CMS Management') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('admission-forms') || Request::is('admission-forms/*') ? 'active' : '' }}"
                    href="{{ route('admission-forms.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Admission Forms') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('pages') || Request::is('pages/*') ? 'active' : '' }}"
                    href="{{ route('pages.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Pages') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('banners') || Request::is('banners/*') ? 'active' : '' }}"
                    href="{{ route('banners.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Banners') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('about-us') || Request::is('about-us/*') ? 'active' : '' }}"
                    href="{{ route('about-us.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('About Us') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('faqs') || Request::is('faqs/*') ? 'active' : '' }}"
                    href="{{ route('faqs.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Faqs') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('gallery-images') || Request::is('gallery-images/*') ? 'active' : '' }}"
                    href="{{ route('gallery-images.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Gallery Images') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('mobile-app-sections') || Request::is('mobile-app-sections/*') ? 'active' : '' }}"
                    href="{{ route('mobile-app-sections.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Mobile App Sections') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('why-choose-us') || Request::is('why-choose-us/*') ? 'active' : '' }}"
                    href="{{ route('why-choose-us.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Why Choose Us') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('policies') || Request::is('policies/*') ? 'active' : '' }}"
                    href="{{ route('policies.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Policies') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('ready-to-join-us') || Request::is('ready-to-join-us/*') ? 'active' : '' }}"
                    href="{{ route('ready-to-join-us.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Ready to Join Us') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('testimonials') || Request::is('testimonials/*') ? 'active' : '' }}"
                    href="{{ route('testimonials.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Testimonials') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link {{ Request::is('institute-image-settings') || Request::is('institute-image-settings/*') ? 'active' : '' }}"
                    href="{{ route('institute-image-settings.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ _lang('Image Setting') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif
