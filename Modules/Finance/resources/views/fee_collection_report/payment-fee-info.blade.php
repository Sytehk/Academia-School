@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Payment fee info') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('payment-fee-info') }}" method="get">
                        @csrf

                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('From Date') }}</label>
                                    <input type="date" class="form-control" name="from_date"
                                        value="{{ $from_date ?? old('from_date') }}">
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('To Date') }}</label>
                                    <input type="date" class="form-control" name="to_date"
                                        value="{{ $to_date ?? date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Class') }}</label>
                                    <select name="class_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2">
                                        <option value="">{{ _lang('All Classes') }}</option>
                                        @foreach ($classes ?? [] as $class)
                                            <option value="{{ $class->id }}"
                                                {{ (int) ($classId ?? 0) === (int) $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Section') }}</label>
                                    <select name="section_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" data-selected="{{ $sectionId ?? '' }}">
                                        <option value="">{{ _lang('Select Class First') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group ">
                                    <button type="submit" class="btn btn-primary mt-4">{{ _lang('Search') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div>
        <div class="row">
            <div class="col-12">
                <div class="pull-right d-flex gap-2">

                    {{-- Print --}}
                    <a href="{{ route('payment-fee-info-print', array_filter([
                        'from_date' => $from_date,
                        'to_date' => $to_date,
                        'class_id' => $classId ?? null,
                        'section_id' => $sectionId ?? null,
                    ])) }}"
                        target="_blank" class="btn btn-primary btn-sm">

                        <i class="fa fa-print"></i>

                        {{ _lang('Print Report') }}

                    </a>

                    {{-- PDF --}}
                    <a href="{{ route('payment-fee-info-pdf', array_filter([
                        'from_date' => $from_date,
                        'to_date' => $to_date,
                        'class_id' => $classId ?? null,
                        'section_id' => $sectionId ?? null,
                    ])) }}"
                        class="btn btn-danger btn-sm">
                        <i class="fa fa-file-pdf"></i>
                        {{ _lang('Download PDF') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (isset($studentCollections) && !empty($studentCollections) && count($studentCollections) > 0)
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-default ">
                    <div class="card-header">
                        <span class="card-title">{{ _lang('Paid Info') }}</s>
                    </div>
                    <div class="card-body">

                        @php
                            $slNo = 1;
                        @endphp
                        <div class="card-border">
                            <div class="card-header py-3 d-flex justify-content-between">
                                <div class="pull-left">
                                    <p class="">Total Found: <strong>{{ count($studentCollections) }}</strong>
                                    </p>
                                </div>
                                <div class="pull-right">
                                    {{-- @if (isset($studentCollections))
                                        <form action="{{ route('payment-fee-info-pdf') }}" method="post"
                                            style="display: inline;">
                                            @csrf

                                            <input type="hidden" name="from_date" value="{{ $from_date }}">
                                            <input type="hidden" name="to_date" value="{{ $to_date }}">

                                            <button type="submit" class="btn btn-primary btn-sm pull-right margin_top_24">
                                                <i class="fa fa-print"></i>
                                                {{ _lang('Print Report') }}
                                            </button>
                                        </form>
                                    @endif --}}
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('SL NO.') }}</th>
                                            <th>{{ _lang('Student Roll') }}</th>
                                            <th>{{ _lang('Name') }}</th>
                                            <th>{{ _lang('Invoice ID') }}</th>
                                            <th>{{ _lang('Fee Head') }}</th>
                                            <th>{{ _lang('Fee Sub Head') }}</th>
                                            <th>{{ _lang('Total fine') }}</th>
                                            <th>{{ _lang('Total Paid') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalPaid = 0;
                                        @endphp
                                        @foreach ($studentCollections as $data)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>
                                                    {{ $data->student?->studentSession?->roll }}</td>
                                                <td>
                                                    @if ($data->student->user?->image != null)
                                                        <img src="{{ $data->student->user?->image ? asset('storage/users/' . $data->student->user?->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                                            class="w-75px me-3" alt="">
                                                    @else
                                                        <img src="{{ asset('images/no-image.png') }}" class="w-75px me-3">
                                                    @endif

                                                    {{ $data->student?->first_name }}
                                                    {{ $data->student?->last_name }}
                                                </td>
                                                <td>{{ $data->invoice_id }}</td>
                                                <td class="" style="word-break: break-word;">
                                                    @foreach ($data->details as $detail)
                                                        {{ $detail->feeHead->name }}
                                                        @if (!$loop->last)
                                                            ,
                                                        @endif
                                                    @endforeach

                                                    @if ($data->attendance_fine >= 1)
                                                        Attendance Fine,
                                                    @endif

                                                    @if ($data->quiz_fine >= 1)
                                                        Quiz Fine,
                                                    @endif

                                                    @if ($data->lab_fine >= 1)
                                                        Lab Fine,
                                                    @endif

                                                    @if ($data->tc_amount >= 1)
                                                        Tc
                                                    @endif
                                                </td>
                                                <td class="" style="word-break: break-word;">
                                                    @foreach ($data->details as $detail)
                                                        @foreach ($detail->subHeads as $subHeads)
                                                            {{ $subHeads->feeSubHead->name }},
                                                        @endforeach
                                                    @endforeach
                                                </td>
                                                <td class="" width="7%">
                                                    @foreach ($data->details as $detail)
                                                        {{ $detail->fine_payable }},
                                                    @endforeach
                                                </td>

                                                <td class="" width="7%">{{ $data->total_paid }}</td>
                                                @php
                                                    $totalPaid += $data->total_paid;
                                                @endphp
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    <tfoot style="background-color: #1f44eb; color: #fff;">
                                        <tr>
                                            <td colspan="7" style="text-align:right; color: white;">{{ _lang('Total') }}
                                            </td>
                                            <td colspan="1" style="text-align:left; color: white;">
                                                {{ $totalPaid }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection
