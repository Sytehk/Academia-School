@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Absent Fine') }}
@endsection
<style>
    .dashed-line {
        border-top: 2px dashed #000;
        width: 40%;
        margin: 10px 0;
        display: inline-block;
    }

    .heading-container {
        text-align: center;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('absent-fine-report') }}" method="post">
                @csrf
                <div class="my-2 row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Class') }}</label>
                            <select name="class_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" onChange="getData(this.value);" required>
                                <option value="">{{ _lang('Select Class') }}</option>
                                {!! create_option('classes', 'id', 'class_name', $class_id) !!}
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Section') }}</label>
                            <select name="section_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" data-selected="{{ $section_id ?? '' }}" required>
                                <option value="">{{ _lang('Select Class First') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="my-2 row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="my-2 control-label">{{ _lang('From') }}</label>
                                <input type="date" class="form-control" name="from_date"
                                    value="{{ $from_date ?? old('from_date') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="my-2 control-label">{{ _lang('To') }}</label>
                                <input type="date" class="form-control" name="to_date"
                                    value="{{ $to_date ?? date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="my-5 row">
                    <div class="col-sm-6 d-flex align-items-end custom-margin">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary">{{ _lang('Search') }}</button>
                        </div>
                    </div>
                </div>
            </form>

            @if (isset($processedData) && count($processedData) > 0)
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <!--begin::Table head-->
                        <thead>
                            <!--begin::Table row-->
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase gs-0">
                                <th>{{ _lang('Roll') }}</th>
                                <th>{{ _lang('Name') }}</th>
                                <th>{{ _lang('Lab Fine') }}</th>
                                <th>{{ _lang('Quiz Fine') }}</th>
                                <th>{{ _lang('Attd % Fine') }}</th>
                                <th>{{ _lang('Total Fine') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sl = 1;
                            @endphp
                            @forelse ($fines as $data)
                                <tr>
                                    <td>
                                        {{ $data['roll'] }}
                                    </td>
                                    <td>
                                        {{ $data['first_name'] }}
                                    </td>
                                    <td>
                                        {{ format_currency($data['labFine']) }}
                                    </td>
                                    <td>
                                        {{ format_currency($data['quizFine']) }}
                                    </td>
                                    <td>
                                        {{ format_currency($data['attendanceFine']) }}
                                    </td>
                                    <td>
                                        {{ format_currency($data['totalFine']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-danger">No Student Found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase gs-0">
                                <td colspan="5">{{ _lang('Total') }}
                                </td>
                                <td colspan="1">
                                    {{ format_currency($totalFineAmount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

        </div>
    </div>



@endsection

@section('js-script')
    <script type="text/javascript">
        function getData(val) {
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();
            $.ajax({
                type: "POST",
                url: "{{ url('sections-section') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                beforeSend: function() {
                    $("#preloader").css("display", "block");
                },
                success: function(sections) {
                    $("#preloader").css("display", "none");
                    $('select[name=section_id]').html(sections);
                }
            });
        }
    </script>
@stop
