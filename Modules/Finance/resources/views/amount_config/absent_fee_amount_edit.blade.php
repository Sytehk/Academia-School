 @extends('layouts.backend')

 @section('page_title_breadcrumb')
     {{ _lang('Amount Fee Config') }}
 @endsection
 @section('content')
     <div class="card card-default">
         <div class="card-header">
             <span class="card-title">{{ _lang('Configure Absent Fine Form') }}</span>
         </div>

         <div class="">
             <div class="row">
                 <div class="col-lg-12">
                     <form method="post" class="" action="{{ route('absent-fines.update', $absentFine->id) }}">
                         @csrf
                         @method('PATCH')

                         <div class="col-lg-12">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Class') }}</label>
                                 <select name="class_id" class="form-select form-select-sm form-select-solid"
                                     data-control="select2">
                                     {!! create_option('classes', 'id', 'class_name', $absentFine->class_id) !!}
                                 </select>
                             </div>
                         </div>

                         <div class="col-lg-12">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Period') }}</label>
                                 <select name="period_id" class="form-select form-select-sm form-select-solid"
                                     data-control="select2">
                                     {!! create_option('periods', 'id', 'period', $absentFine->period_id) !!}
                                 </select>
                             </div>
                         </div>

                         <div class="col-lg-12">
                             <div class="form-group">
                                 <label class="control-label my-2">{{ _lang('Amount') }}</label>
                                 <input name="amount" class="form-control" value="{{ $absentFine->fee_amount }}" />
                             </div>
                         </div>

                         <div class="col-lg-12">
                             <div class="form-group">
                                 <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                             </div>
                         </div>
                     </form>
                 </div>
             </div>
         </div>
     </div>
 @endsection
