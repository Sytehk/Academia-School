 @extends('layouts.backend')

 @section('page_title_breadcrumb')
     {{ _lang('Amount Fee Config') }}
 @endsection
 @section('content')


     <div class="card card-default">
         <div class="card-header">
             <span class="card-title">{{ _lang('Amount Fee Mapping') }}</span>
         </div>

         <div class="">
             <form method="post" action="{{ route('fees.update', $fee->id) }}">
                 @csrf
                 @method('PATCH')

                 <div class="row">
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Class') }}</label>
                             <select name="class_id" class="form-select form-select-sm form-select-solid"
                                 data-control="select2">

                                 {!! create_option('classes', 'id', 'class_name', $fee->class_id) !!}
                             </select>
                         </div>

                         <div class="form-group mb-5">
                             <label class="control-label my-2">{{ _lang('Section') }}</label>
                             <select name="section_id" class="form-select form-select-sm form-select-solid"
                                 data-control="select2" data-selected="{{ old('section_id', $fee->section_id) }}">
                                 <option value="">{{ _lang('Select Class First') }}</option>
                             </select>
                         </div>

                         <div class="form-group mt-5">
                             <label class="control-label my-2">{{ _lang('Group') }}</label>
                             <select name="group_id" id="group" class="form-select form-select-sm form-select-solid"
                                 data-control="select2">

                                 {!! create_option('student_groups', 'id', 'group_name', $fee->group_id) !!}
                             </select>
                         </div>

                         <div class="form-group mt-5">
                             <label class="control-label my-2">{{ _lang('Student Category') }}</label>
                             <select name="student_category_id" id="student_category"
                                 class="form-select form-select-sm form-select-solid" data-control="select2">

                                 {!! create_option('student_categories', 'id', 'name', $fee->student_category_id) !!}
                             </select>
                         </div>

                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Fee Head') }}</label>

                             <select name="fee_head_id" class="form-select form-select-sm form-select-solid"
                                 data-control="select2">
                                 {!! create_option('fee_heads', 'id', 'name', $fee->fee_head_id) !!}
                             </select>
                         </div>

                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Fee Amount') }}</label>
                             <input name="fee_amount" class="form-control" value="{{ $fee->fee_amount }}" />
                         </div>

                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Fine Amount') }}</label>
                             <input name="fine_amount" class="form-control" value="{{ $fee->fine_amount }}" />
                         </div>

                         <div class="col-lg-12">
                             <div class="form-group">
                                 <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                             </div>
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="">
                             <div class="card card-body">
                                 <h4 class="p-0 m-0 pb-4 border-bottom mb-4">Fee Fund Distribution</h4>

                                 <label class="control-label my-2">{{ _lang('Fund') }}</label>

                                 <select name="fund_id" id="fund_id" class="form-select form-select-sm form-select-solid"
                                     data-control="select2" required>
                                     <option value="">{{ _lang('--Select Fund--') }}</option>
                                     {!! create_option('accounting_funds', 'id', 'name', $fee->fund_id) !!}
                                 </select>
                             </div>
                         </div>
                     </div>
                 </div>
             </form>
         </div>
     </div>
 @endsection

 @section('js-script')
     <script>
         $(window).on('load', function() {
             $("#section").next().find("ul li").css("display", "none");
             $(document).on("change", "#class", function() {
                 $("#section").next().find("ul li").css("display", "none");
                 var class_id = $(this).val();
                 $('#section option[data-class="' + class_id + '"]').each(function() {
                     var section_id = $(this).val();
                     $("#section").next().find("ul li[data-value='" + section_id + "']").css(
                         "display", "block");
                 });
             });
         });
     </script>
 @stop
