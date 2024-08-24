@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Waiver Config') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs setting-tab" id="myTabsWaiverConfig" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="assign-tab" data-bs-toggle="tab" data-bs-target="#ASSIGN"
                        type="button" role="tab" aria-controls="ASSIGN" aria-selected="true">
                        {{ _lang('ASSIGN') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="waiverconfiglist-tab" data-bs-toggle="tab"
                        data-bs-target="#WAIVERCONFIGLIST" type="button" role="tab" aria-controls="WAIVERCONFIGLIST"
                        aria-selected="false">
                        {{ _lang('WAIVER CONFIG LIST') }}
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-3" id="myTabsWaiverConfigContent">
                <div class="tab-pane fade show active" id="ASSIGN" role="tabpanel" aria-labelledby="assign-tab">
                    @include('finance::waiver_config.assign')
                </div>
                <div class="tab-pane fade" id="WAIVERCONFIGLIST" role="tabpanel" aria-labelledby="waiverconfiglist-tab">
                    @include('finance::waiver_config.assign_list')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Retrieve the active tab from local storage
            let activeTab = localStorage.getItem('activeTab');

            // If there is an active tab, switch to it
            if (activeTab) {
                const tab = document.querySelector(`button[data-bs-target="#${activeTab}"]`);
                if (tab) {
                    const bootstrapTab = new bootstrap.Tab(tab);
                    bootstrapTab.show();
                }
            }

            // Handle tab click event
            document.querySelectorAll('#myTabsWaiverConfig button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    // Get the data-bs-target of the clicked tab
                    const activeTab = e.target.getAttribute('data-bs-target').substr(1);

                    // Store the active tab in local storage
                    localStorage.setItem('activeTab', activeTab);
                });
            });
        });
    </script>
@endsection
