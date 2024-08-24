@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Fee head sub-head waiver') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs" id="myTabsFeeHead">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#feeHead">{{ _lang('Fee Head') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#feeSubHead">{{ _lang('Fee Sub Head') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#feeWaiver">{{ _lang('Fee Waiver') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="feeHead" class="tab-pane fade show active">
                    @include('finance::fees_page.fee_head')
                </div>

                <div id="feeSubHead" class="tab-pane fade">
                    @include('finance::fees_page.fee_sub_head')
                </div>

                <div id="feeWaiver" class="tab-pane fade">
                    @include('finance::fees_page.fee_waiver')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Retrieve the active tab from local storage
            var activeTab = localStorage.getItem('activeTab');

            // If there is an active tab, switch to it
            if (activeTab) {
                var tabLink = document.querySelector('#myTabsFeeHead a[href="#' + activeTab + '"]');
                if (tabLink) {
                    var tabToActivate = new bootstrap.Tab(tabLink);
                    tabToActivate.show();
                }
            }

            // Handle tab click event
            var tabLinks = document.querySelectorAll('#myTabsFeeHead a');

            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', function(e) {
                    // Get the href of the clicked tab
                    var activeTab = e.target.getAttribute('href').substring(1);

                    // Store the active tab in local storage
                    localStorage.setItem('activeTab', activeTab);
                });
            });
        });
    </script>
@endsection
