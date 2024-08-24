@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Quick Collection') }}
@endsection

<style>
    /* Custom CSS to remove borders */
    .no-border tr,
    .no-border td {
        border: none !important;
    }

    .card {
        padding: 0px !important;
    }
</style>

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="">
                    <form method="post" action="#">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
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
                                        <label class="control-label my-2">{{ _lang('Student ID') }}</label>
                                        <input class="form-control" type="number" name="student_id"
                                            value="{{ $studentSession->student->id }}" placeholder="Student ID" required />
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <input type="checkbox" name="" class="col-lg-1 text-left">
                                    <div class="col-lg-9">
                                        <p>Attendance Fine</p>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <input type="checkbox" name="" class="col-lg-1 text-left">
                                    <div class="col-lg-9">
                                        <p>Previous Due</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <img class="">
                                        </div>
                                        <div class="card-body">
                                            <p>Student ID</p>
                                        </div>
                                        <div class="card-footer">
                                            @isset($studentSession->student->id)
                                                {{ $studentSession->student->id }}
                                            @endisset
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <table class="table no-border">
                                        <tr>
                                            <td>Student's Name</td>
                                            <td> <span>:</span>
                                                @isset($studentSession->student->first_name)
                                                    {{ $studentSession->student->first_name . ' ' . ($studentSession->student->last_name ?? '') }}
                                                @endisset
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Student Roll</td>
                                            <td> <span>:</span>
                                                @isset($studentSession->roll)
                                                    {{ $studentSession->roll }}
                                                @endisset
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Section</td>
                                            <td> <span>:</span>
                                                @isset($studentSession->section->section_name)
                                                    {{ $studentSession->section->section_name }}
                                                @endisset
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Group</td>
                                            <td> <span>:</span>
                                                @isset($studentSession->group->group_name)
                                                    {{ $studentSession->group->group_name }}
                                                @endisset
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Father's Name</td>
                                            <td> <span>:</span>
                                                @isset($studentSession->student->parent->parent_name)
                                                    {{ $studentSession->student->parent->parent_name }}
                                                @endisset
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Category</td>
                                            <td> <span>:</span>
                                                @isset($studentSession->student->studentCategory->name)
                                                    {{ $studentSession->student->studentCategory->name }}
                                                @endisset
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mobile No.</td>
                                            <td> <span>:</span>
                                                @isset($studentSession->student->phone)
                                                    {{ $studentSession->student->phone }}
                                                @endisset
                                            </td>
                                        </tr>
                                        <!-- Add more rows as needed -->
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="row" style="margin-top: 50px;">
                            <div class="col-lg-12">
                                <table class="table table-bordered">

                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Fee Head') }}</th>
                                            <th>{{ _lang('Select Fee Sub Head') }}</th>
                                            <th>{{ _lang('Total Due') }}</th>
                                            <th>{{ _lang('Total Paid') }}</th>
                                            <th>{{ _lang('Waiver') }}</th>
                                            {{-- loop --}}
                                            <th>{{ _lang('') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- @if (isset($feeMapFeeSubHeads))
                                @foreach ($feeMapFeeSubHeads as $feeMapFeeSubHead)
                                    <tr>
                                        <td>{{ $feeMapFeeSubHead->name }}</td>
                                        <td>
                                            <input type="hidden" name="fee_sub_head_id"
                                                value="{{ $feeMapFeeSubHead->fee_sub_head_id }}">
                                            <input type="date" class="form-control"
                                                name="payable_date[]" value="{{ old('payable_date') }}" required>
                                        </td>
                                        <td>
                                            <input type="date" class="form-control"
                                                name="fine_active_date[]" value="{{ old('fine_active_date') }}"
                                                required>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 50px;">
                            <div class="col-lg-6">
                                <div class="col-lg-6">
                                    <div class="card text-center border-danger">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="p-3">Attendance Fine <i class="fa fa-plus"></i> </h6>
                                        </div>
                                        <div class="card-body py-5">
                                            <p class="card-text">$ 0</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card text-center border-warning">
                                        <div class="card-header bg-warning text-white">
                                            <h6 class="p-3">Total Paid </h6>
                                        </div>
                                        <div class="card-body py-5">
                                            <p class="card-text">$ 0</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card text-center border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="p-3">Total Payable </h6>
                                        </div>
                                        <div class="card-body py-5">
                                            <p class="card-text">$ 0</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card text-center border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="p-3">Grand Due Amount </h6>
                                        </div>
                                        <div class="card-body py-5">
                                            <p class="card-text">$ 0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Payment Date') }}</label>
                                    <input class="form-control" type="date" name="date"
                                        value="{{ old('date') }}" />
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('Paid By') }}</label>
                                        <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                            name="payment_method_id">
                                            {!! create_option('payment_methods', 'id', 'name', old('payment_method_id')) !!}
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang(' Send SMS Notification?') }}</label>
                                        <br>
                                        <input type="radio" name="sms_sent">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <textarea class="form-control" type="text" name="note" cols="4" rows="8">{{ old('note') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <button type="submit"
                                        class="btn btn-success">{{ _lang('Process to Collection') }}</button>
                                </div>


                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
