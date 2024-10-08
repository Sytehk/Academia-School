@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="card-border">
        <div class="card-header py-3 d-flex justify-content-between">
            <div>
                <p class="pdf-page-title">
                    Unpaid Info
                </p>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                <thead>
                    <th>{{ _lang('Student ID') }}</th>
                    <th>{{ _lang('Student Name') }}</th>
                    <th>{{ _lang('Roll') }}</th>
                    <th>{{ _lang('Due Details') }}</th>
                    <th>{{ _lang('Total Due') }}</th>
                </thead>
                <tbody>
                    @foreach ($studentDatas as $data)
                        @php
                            $user = auth()->user();
                            $studentId = $user->student->id;
                        @endphp

                        @if ($data->id === $studentId)
                            <tr>
                                <td width="7%">{{ $data->roll }}</td>
                                <td width="10%">{{ $data->name }}</td>
                                <td width="7%">{{ $data->roll }}</td>
                                <td class="" style="word-break: break-word;">
                                    @foreach ($data->feeHeads as $feeHead)
                                        @php
                                            $subHeadData = count($feeHead->feeSubHeads);
                                        @endphp
                                        @if ($subHeadData > 0)
                                            <b>{{ $feeHead->name }}:</b>
                                        @endif
                                        @foreach ($feeHead->feeSubHeads as $subHead)
                                            {{ $subHead->name }}
                                            ({{ $feeHead->amount[$subHead->id]['fee_and_fine_payable'] }})
                                            ,
                                        @endforeach
                                    @endforeach
                                </td>
                                <td width="7%">{{ $data->total_due_amount }}</td>

                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
