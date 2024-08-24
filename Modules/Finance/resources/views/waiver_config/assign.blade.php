 <style>
     .modal-backdrop {
         position: fixed;
         top: 0;
         right: 0;
         bottom: 0;
         left: 0;
         z-index: -999 !important;
         background-color: #000;
     }
 </style>

 <div class="card card-default">
     <div class="card-header">
         <span class="card-title">{{ _lang('Waiver Config') }}</span>
     </div>

     <div class="">
         <div class="row">

             <div class="col-lg-12">
                 @if (\Session::has('success'))
                     <div class="alert alert-success">
                         <p>{{ \Session::get('success') }}</p>
                     </div>
                     <br />
                 @endif
                 <form method="get">
                     @csrf

                     <div class="row">
                         <div class="col-lg-6">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Academic Year') }}</label>
                                 <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                     name="academic_year" required>
                                     {!! create_option('academic_years', 'id', 'session', get_option('academic_year')) !!}
                                 </select>
                             </div>
                         </div>

                         <div class="col-lg-6">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Group') }}</label>
                                 <div class="">
                                     <select name="group" id="group"
                                         class="form-select form-select-sm form-select-solid" data-control="select2">
                                         {!! create_option('student_groups', 'id', 'group_name', isset($request->group) ?? $request->group) !!}
                                     </select>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <div class="row">
                         <div class="col-lg-6">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Class') }}</label>
                                 <select name="class_id" id="class_id"
                                     class="form-select form-select-sm form-select-solid" data-control="select2">
                                     <option value="">{{ _lang('Select Class') }}</option>
                                     {!! create_option('classes', 'id', 'class_name', request('class_id')) !!}
                                 </select>
                             </div>
                         </div>
                         <div class="col-lg-6">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Section') }}</label>
                                 <select name="section_id" id="section_id"
                                     class="form-select form-select-sm form-select-solid" data-control="select2"
                                     data-selected="{{ request('section_id') }}">
                                     <option value="">{{ _lang('Select Class First') }}</option>
                                 </select>
                             </div>
                         </div>
                     </div>

                     <div class="row">
                         <div class="col-lg-6">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Student Category') }}</label>
                                 <select name="student_category_id" id="student_category"
                                     class="form-select form-select-sm form-select-solid" data-control="select2">
                                     {!! create_option(
                                         'student_categories',
                                         'id',
                                         'name',
                                         request('student_category_id'),
                                     ) !!}
                                 </select>
                             </div>
                         </div>
                     </div>

                     <div class="form-group">
                         <div class="col-lg-12">
                             <button type="submit" class="btn btn-primary mt-5">{{ _lang('Search') }}</button>
                         </div>
                     </div>
                 </form>
             </div>


             @if (isset($students) && count($students) > 0)
                 <div class="col-lg-12">
                     <div class="">
                         <form action="{{ route('waiver-config.store') }}" method="post">
                             @csrf

                             <table class="table table-bordered">
                                 <thead>
                                     <tr>
                                         <th>
                                             <input type="checkbox" id="select-all">
                                         </th>
                                         <th>{{ _lang('SL NO.') }}</th>
                                         <th>{{ _lang('Roll No') }}</th>
                                         <th>{{ _lang('Name') }}</th>
                                         <th>{{ _lang('Group') }}</th>
                                         <th>{{ _lang('Category') }}</th>
                                     </tr>
                                 </thead>

                                 <tbody>
                                     @foreach ($students as $key => $student)
                                         <tr>
                                             <th scope="row">
                                                 <input type="checkbox" class="waiver-checkbox"
                                                     value="{{ $student->id }}" name="students[{{ $key }}]">
                                             </th>
                                             <th scope="row">
                                                 {{ $loop->index + 1 }}
                                             </th>
                                             <td>{{ $student->roll }}<input type="number" hidden
                                                     value="{{ $student->roll }}" name="roll_no[{{ $key }}]">
                                             </td>
                                             <td>{{ $student->name }} <input type="text" hidden
                                                     value="{{ $student->name }}"
                                                     name="students_name[{{ $key }}]"></td>
                                             <td>{{ $student->group_name }}</td>
                                             <td>
                                                 {{ $student->studentCategory->name }}
                                             </td>


                                         </tr>
                                     @endforeach
                                 </tbody>

                                 <div class="tfoot">
                                     <tr>
                                         <td>
                                             <div class="col-lg-12">
                                                 <button id="processButton" type="button" class="btn btn-primary"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#exampleModal">{{ _lang('Process') }}</button>
                                             </div>
                                         </td>
                                     </tr>
                                 </div>
                             </table>

                             {{-- Modal Start --}}
                             <!-- Modal Start -->
                             <div class="modal fade" id="exampleModal" tabindex="-1"
                                 aria-labelledby="exampleModalLabel" aria-hidden="true">
                                 <div class="modal-dialog">
                                     <div class="modal-content">
                                         <div class="modal-header">
                                             <h4 class="modal-title" id="exampleModalLabel">Assign Waiver</h4>
                                             <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                 aria-label="Close"></button>
                                         </div>
                                         <div class="modal-body">
                                             <div class="row">
                                                 <div class="col-lg-6">
                                                     <div class="form-group">
                                                         <label class="control-label">{{ _lang('Fee Head') }}</label>
                                                         <select name="fee_head_id" id="fee_head_id"
                                                             class="form-select form-select-sm form-select-solid"
                                                             data-control="select2">
                                                             {!! create_option('fee_heads', 'id', 'name', old('name')) !!}
                                                         </select>
                                                     </div>
                                                 </div>
                                                 <div class="col-lg-6">
                                                     <div class="form-group">
                                                         <label
                                                             class="control-label">{{ _lang('Waiver Category') }}</label>
                                                         <select name="wavier_id" id="wavier"
                                                             class="form-select form-select-sm form-select-solid"
                                                             data-control="select2">
                                                             {!! create_option('waivers', 'id', 'waiver',
                                                             isset($request->wavier_id) ?!! $request->wavier_id) }}
                                                         </select>
                                                     </div>
                                                 </div>
                                                 <div class="col-lg-12">
                                                     <div class="form-group">
                                                         <label
                                                             class="control-label">{{ _lang('Waiver Amount') }}</label>
                                                         <input type="number" class="form-control"
                                                             name="waiver_amount" value="0" required>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                         <div class="modal-footer">
                                             <button type="button" class="btn btn-danger"
                                                 data-bs-dismiss="modal">Close</button>
                                             <button type="submit" class="btn btn-success mt-5">Save</button>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <!-- Modal End -->
                             {{-- Modal End --}}

                         </form>

                     </div>
                 </div>
             @endif
         </div>
     </div>
 </div>

 @section('js-script')
     <script>
         $(document).ready(function() {
             $("#select-all").click(function() {
                 var isChecked = $(this).prop("checked");
                 $(".waiver-checkbox").prop("checked", isChecked);
             });

             $(".waiver-checkbox").click(function() {
                 var allChecked = $(".waiver-checkbox:checked").length === $(".waiver-checkbox").length;
                 $("#select-all").prop("checked", allChecked);
             });
         });
     </script>
 @endsection
