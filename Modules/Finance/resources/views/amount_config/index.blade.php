@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Amount Fee Config') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs" id="myTabsAmountConfig10">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#FEEAMOUNT">{{ _lang('FEE AMOUNT') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#ABSENTFINEAMOUNT">{{ _lang('ABSENT FINE AMOUNT') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="FEEAMOUNT" class="tab-pane fade show active">
                    @include('finance::amount_config.fee_amount')
                </div>

                <div id="ABSENTFINEAMOUNT" class="tab-pane fade">
                    @include('finance::amount_config.absent_fee_amount')
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
                var tabLink = document.querySelector('#myTabsAmountConfig10 a[href="#' + activeTab + '"]');
                if (tabLink) {
                    var tabToActivate = new bootstrap.Tab(tabLink);
                    tabToActivate.show();
                }
            }

            // Handle tab click event
            var tabLinks = document.querySelectorAll('#myTabsAmountConfig10 a');

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
