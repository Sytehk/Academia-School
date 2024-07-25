@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Exam Startup') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs" id="myTabsExamStartUp">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#examCode">{{ _lang('Exam Code') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#examGrade">{{ _lang('Exam Grade') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#examCreate">{{ _lang('Exam Create') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="examCode" class="tab-pane fade show active">
                    @include('examination::exam_startup.exam_code')
                </div>

                <div id="examGrade" class="tab-pane fade">
                    @include('examination::exam_startup.exam_grade')
                </div>

                <div id="examCreate" class="tab-pane fade">
                    @include('examination::exam_startup.exam_create')
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
                var tabLink = document.querySelector('#myTabsExamStartUp a[href="#' + activeTab + '"]');
                if (tabLink) {
                    var tabToActivate = new bootstrap.Tab(tabLink);
                    tabToActivate.show();
                }
            }

            // Handle tab click event
            var tabLinks = document.querySelectorAll('#myTabsExamStartUp a');

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

    <script>
        document.getElementById('select-all1').addEventListener('change', function() {
            const checkboxes1 = document.querySelectorAll('input[name="selected_codes[]"]');
            checkboxes1.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.getElementById('select-all11').addEventListener('change', function() {
            const checkboxes1 = document.querySelectorAll('input[name="short_code_ids[]"]');
            checkboxes1.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        function validateForm1() {
            const classSelect = document.getElementById('class_id_select1');
            const checkboxes1 = document.querySelectorAll('input[name="selected_codes[]"]:checked');

            // Check if a class is selected
            if (!classSelect.value) {
                alert('Please select a class.');
                return false;
            }

            // Check if at least one checkbox is selected
            if (checkboxes1.length === 0) {
                alert('Please select at least one exam code.');
                return false;
            }

            // If both conditions are met, allow form submission
            return true;
        }
    </script>

    <script>
        document.getElementById('select-all1222').addEventListener('change', function() {
            const checkboxes2 = document.querySelectorAll('input[name="checked_grades[]"]');
            checkboxes2.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.getElementById('select-all2').addEventListener('change', function() {
            const checkboxes2 = document.querySelectorAll('input[name="selected_grades[]"]');
            checkboxes2.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        function validateForm2() {
            const classSelect2 = document.getElementById('class_id_select2');
            const checkboxes2 = document.querySelectorAll('input[name="selected_grades[]"]:checked');

            // Check if a class is selected
            if (!classSelect2.value) {
                alert('Please select a class.');
                return false;
            }

            // Check if at least one checkbox is selected
            if (checkboxes2.length === 0) {
                alert('Please select at least one exam grade.');
                return false;
            }

            // If both conditions are met, allow form submission
            return true;
        }
    </script>
@endsection
