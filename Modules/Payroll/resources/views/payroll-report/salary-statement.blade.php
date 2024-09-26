@extends('layouts.backend')
<style>
    .payroll_salary_input {
        width: 100px !important;
        border: none !important;
    }

    .editable-input {
        border: 1px solid #3498db;
        background-color: #fff;
        color: #333;
    }

    .readonly-input {
        border: none;
        background-color: transparent;
        color: #777;
    }

    .table th {
        width: 90px;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span class="panel-title">{{ _lang('Salary Statement ') }}</span>
                </div>

                <div class="panel-body">
                    <div class="row" style="margin-bottom: 15px; margin-left: 0px;">
                        <form method="get" action="{{ route('salary-statement-report') }}">
                            <div class="col-md-2">
                                <select class="form-control" name="year">
                                    <option value="">--Select Year--</option>
                                    <option {{ isset($year) && $year == '2020' ? 'selected' : '' }} value="2020">2020
                                    </option>
                                    <option {{ isset($year) && $year == '2021' ? 'selected' : '' }} value="2021">2021
                                    </option>
                                    <option {{ isset($year) && $year == '2022' ? 'selected' : '' }} value="2022">2022
                                    </option>
                                    <option {{ isset($year) && $year == '2023' ? 'selected' : '' }} value="2023">2023
                                    </option>
                                    <option {{ isset($year) && $year == '2024' ? 'selected' : '' }} value="2024">2024
                                    </option>
                                    <option {{ isset($year) && $year == '2025' ? 'selected' : '' }} value="2025">2025
                                    </option>
                                    <option {{ isset($year) && $year == '2026' ? 'selected' : '' }} value="2026">2026
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select class="form-control select2" name="month">
                                    <option value="">--Select Month--</option>
                                    <option {{ isset($month) && $month == '1' ? 'selected' : '' }} value="1">Jan
                                    </option>
                                    <option {{ isset($month) && $month == '2' ? 'selected' : '' }} value="2">Feb
                                    </option>
                                    <option {{ isset($month) && $month == '3' ? 'selected' : '' }} value="3">Mar
                                    </option>
                                    <option {{ isset($month) && $month == '4' ? 'selected' : '' }} value="4">Apr
                                    </option>
                                    <option {{ isset($month) && $month == '5' ? 'selected' : '' }} value="5">May
                                    </option>
                                    <option {{ isset($month) && $month == '6' ? 'selected' : '' }} value="6">Jun
                                    </option>
                                    <option {{ isset($month) && $month == '7' ? 'selected' : '' }} value="7">Jul
                                    </option>
                                    <option {{ isset($month) && $month == '8' ? 'selected' : '' }} value="8">Aug
                                    </option>
                                    <option {{ isset($month) && $month == '9' ? 'selected' : '' }} value="9">Sep
                                    </option>
                                    <option {{ isset($month) && $month == '10' ? 'selected' : '' }} value="10">Oct
                                    </option>
                                    <option {{ isset($month) && $month == '11' ? 'selected' : '' }} value="11">Nov
                                    </option>
                                    <option {{ isset($month) && $month == '12' ? 'selected' : '' }} value="12">Dec
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success">{{ _lang('Search') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
@endsection
