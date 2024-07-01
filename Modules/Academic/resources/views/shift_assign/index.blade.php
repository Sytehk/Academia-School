@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Shift Assign') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="p-5 border table-responsive">
                <div class="card-header">
                    <div class="card-title pull-left">
                        {{ _lang('Assign Shift') }}
                    </div>

                    <div class="pull-right">
                        <a href="{{ route('shift.index') }}" target="_blank" class="btn btn-sm btn-primary">
                            {{ _lang('New Shift') }}
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="">
                    <form action="{{ route('assign-shifts.store') }}" class="form-horizontal" method="post">
                        @csrf
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="my-2 control-label">{{ _lang('Teacher') }}</label>
                                <select name="teacher_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>

                                    {!! create_option('teachers', 'id', 'name', old('teacher_id')) !!}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="mb-2 control-label d-block">
                                    {{ _lang('Shift') }}
                                </label>

                                <select name="shift_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>

                                    {!! create_option('shifts', 'id', 'name', old('shift_id')) !!}
                                </select>
                            </div>
                        </div>
                        <div>
                            <button type="submit" id="save" class="btn btn-primary">{{ _lang('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="p-5 border table-responsive">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Assign Shift') }}
                    </div>
                </div>
                <div class="mt-5 table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Teacher') }}</th>
                            <th>{{ _lang('Shift') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($assign_shifts as $data)
                                <tr>
                                    <td>
                                        {{ $data->teacher_name }}
                                    </td>
                                    <td>
                                        {{ $data->shift_name }}
                                    </td>
                                    <td>
                                        <form action="{{ route('assign-shifts.destroy', $data->id) }}" method="post">
                                            <a href="{{ route('assign-shifts.edit', $data->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm show_confirm"><i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script type="text/javascript">
        // function getData(val) {
        // 	var _token=$('input[name=_token]').val();
        // 	var class_id=$('select[name=class_id]').val();
        // 	$.ajax({
        // 		type: "POST",
        // 		url: "{{ url('sections-section') }}",
        // 		data:{_token:_token,class_id:class_id},
        // 		success: function(sections){
        // 			$('select[name=section_id]').html(sections);
        // 		}
        // 	});
        // }

        // $("#search_form").validate({
        // 	submitHandler: function(form) {
        // 		search();
        // 		return false;
        // 	},invalidHandler: function(form, validator) {},
        // 	  errorPlacement: function(error, element) {}
        // });


        // $(document).on('click','#assign_teacher',function(){
        //     $(this).prop("disabled",true);
        // 	$.ajax({
        // 		type: "POST",
        // 		url: $("#assign_teacher_form").attr("action"),
        // 		data: $("#assign_teacher_form").serialize(),
        //         beforeSend: function(){
        // 			$("#assign_teacher").html('<i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i>');
        // 		},success: function(data){
        // 			var json = JSON.parse(JSON.stringify(data));
        // 			if(json['result']=="success"){
        // 				search();
        // 				Command: toastr["success"](json['message']);
        // 			}
        // 			$("#assign_teacher").html('Save');
        // 			$("#assign_teacher").attr('disabled',false);
        // 		}
        // 	});
        // 	return false;
        // });


        // function search(){
        // 	$.ajax({
        // 		type: "POST",
        // 		url: "{{ url('assignsubjects/search') }}",
        // 		data: $("#search_form").serialize(),
        // 		success: function(data){
        // 			$('#list').html(data);
        // 			$(".select2").select2();
        // 		}
        // 	});
        // }
    </script>
@stop
