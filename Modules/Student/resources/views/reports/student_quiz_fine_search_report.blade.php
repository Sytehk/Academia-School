@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            @if (isset($processedData) && count($processedData) > 0)
                <div class="card card-default" data-collapsed="0">
                    <div class="card-header">
                        <span class="card-title">
                            {{ _lang('Student Quiz Fine Report') }}
                        </span>
                    </div>

                    <div class="table-responsive mt-5">
                        <table class="table table-bordered">
                            <thead>
                                <th>{{ _lang('SL NO') }}</th>
                                <th>{{ _lang('Section') }}</th>
                                <th>{{ _lang('Student Roll') }}</th>
                                <th>{{ _lang('Name') }}</th>
                                <th>{{ _lang('Attendance Absent') }}</th>
                                <th>{{ _lang('Total Days') }}</th>
                                <th>{{ _lang('Attendance Fine Per') }}</th>
                                <th>{{ _lang('Total') }}</th>
                            </thead>
                            <tbody>
                                @php
                                    $sl = 1;
                                    $totalFineAmount = 0;
                                    $months = [
                                        1 => 'Jan',
                                        2 => 'Feb',
                                        3 => 'Mar',
                                        4 => 'Apr',
                                        5 => 'May',
                                        6 => 'Jun',
                                        7 => 'Jul',
                                        8 => 'Aug',
                                        9 => 'Sep',
                                        10 => 'Oct',
                                        11 => 'Nov',
                                        12 => 'Dec',
                                    ];
                                @endphp

                                @forelse ($processedData as $data)
                                    <tr style="padding: 15px 0 !important;">
                                        <td style="text-align: center">{{ $sl++ }}</td>
                                        <td style="text-align: center">{{ $data['section_name'] }}</td>
                                        <td style="text-align: center">{{ $data['roll'] }}</td>
                                        <td style="text-align: center">{{ $data['student_name'] }}</td>
                                        <td style="text-align: center">
                                            @php
                                                $totalAbsent = 0;
                                            @endphp
                                            @foreach ($data['months'] as $monthNumber => $monthData)
                                                {{ $months[$monthNumber] }}
                                                ({{ $monthData['absent_count'] }})
                                                ,

                                                @php
                                                    $totalAbsent += $monthData['absent_count'];
                                                @endphp
                                            @endforeach
                                        </td>
                                        <td style="text-align: center">{{ $totalAbsent }}</td>
                                        <td style="text-align: center">
                                            {{ $data['attendance_fine'] }} TK
                                        </td>
                                        <td style="text-align: center">
                                            {{ $data['total_fine_amount'] }} TK
                                            @php
                                                $totalFineAmount += $data['total_fine_amount'];
                                            @endphp
                                        </td>
                                    </tr>
                                @empty
                                    <tr style="padding: 15px 0 !important;">
                                        <td colspan="2" class="text-danger">No Student Found</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot style="background-color: #696969; color: #fff;">
                                <tr>
                                    <td colspan="7" style="text-align:right; color: white;">{{ _lang('Total') }}</td>
                                    <td colspan="1" style="text-align:center; color: white;">{{ $totalFineAmount }} TK
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
