@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Mark Configuration') }}
@endsection
<style>
    .table_td_2 {
        text-align: center;
        width: 50px;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs" id="myTabsMarkConfig">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#generalExam">{{ _lang('General Exam') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#grandFinal">{{ _lang('Grand Final') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#markConfigView">{{ _lang('Mark Config View') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="generalExam" class="tab-pane fade show active">
                    @include('examination::mark_config.general_exam')
                </div>

                <div id="grandFinal" class="tab-pane fade">
                    @include('examination::mark_config.grand_final')
                </div>

                <div id="markConfigView" class="tab-pane fade">
                    @include('examination::mark_config.mark_config_view')
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
                var tabLink = document.querySelector('#myTabsMarkConfig a[href="#' + activeTab + '"]');
                if (tabLink) {
                    var tabToActivate = new bootstrap.Tab(tabLink);
                    tabToActivate.show();
                }
            }

            // Handle tab click event
            var tabLinks = document.querySelectorAll('#myTabsMarkConfig a');

            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', function(e) {
                    // Get the href of the clicked tab
                    var activeTab = e.target.getAttribute('href').substring(1);

                    // Store the active tab in local storage
                    localStorage.setItem('activeTab', activeTab);
                });
            });
        });


        function getSubject() {
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();
            $.ajax({
                type: "POST",
                url: "{{ url('exams/get_subject') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                success: function(sections) {
                    $('select[name=subject_id]').html(sections);
                }
            });
        }
    </script>
@endsection
