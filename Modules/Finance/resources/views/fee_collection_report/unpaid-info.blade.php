@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Unpaid Fee Information') }}
@endsection

@section('content')

    {{-- ===================================================== --}}
    {{-- FILTER SECTION --}}
    {{-- ===================================================== --}}

    <div class="row">

        <div class="col-lg-12">

            <div class="card shadow-sm border-0 mb-5">

                <div class="card-header align-items-center border-0">

                    <h3 class="card-title fw-bold text-dark">
                        {{ _lang('Unpaid Fee Information') }}
                    </h3>

                </div>

                <div class="card-body">

                    <form action="{{ route('unpaid-info') }}" method="GET">

                        <div class="row g-4">
                            <div class="my-2 row">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label class="my-2 control-label">{{ _lang('Class') }}</label>
                                        <select name="class_id" class="form-select form-select-sm form-select-solid"
                                            data-control="select2" required>
                                            <option value="">{{ _lang('Select Class') }}</option>
                                            @foreach ($classes as $class)
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
                                        <label class="my-2 control-label">{{ _lang('Section') }}</label>
                                        <select name="section_id" class="form-select form-select-sm form-select-solid"
                                            data-control="select2" data-selected="{{ $sectionId ?? '' }}">
                                            <option value="">{{ _lang('Select Class First') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Search Button --}}
                            <div class="col-12">

                                <button type="submit" class="btn btn-primary px-10">

                                    <i class="fas fa-search me-2"></i>

                                    {{ _lang('Search') }}

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- TABLE SECTION --}}
    {{-- ===================================================== --}}

    <div class="row">

        <div class="col-lg-12">

            <div class="card shadow-sm border-0">

                <div class="card-header border-0">

                    <h3 class="card-title fw-bold text-dark">
                        {{ _lang('Student Due List') }}
                    </h3>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table align-middle table-row-bordered table-striped gy-4 gs-7">

                            <thead class="bg-light">

                                <tr class="fw-bold text-muted">

                                    <th width="5%">
                                        {{ _lang('SL') }}
                                    </th>

                                    <th width="20%">
                                        {{ _lang('Student') }}
                                    </th>

                                    <th width="8%">
                                        {{ _lang('Roll') }}
                                    </th>

                                    <th width="52%">
                                        {{ _lang('Due Details') }}
                                    </th>

                                    <th width="15%" class="text-end">
                                        {{ _lang('Total Due') }}
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse ($studentDatas as $key => $data)
                                    @if ((int) $data->student_status === 1)
                                        <tr>

                                            {{-- SL --}}
                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            {{-- Student --}}
                                            <td>

                                                <div class="d-flex align-items-center">

                                                    {{-- Image --}}
                                                    <div class="me-3">

                                                        @if ($data->image)
                                                            <img src="{{ asset('storage/users/' . $data->image) }}"
                                                                alt="" class="rounded-circle border" width="55"
                                                                height="55">
                                                        @else
                                                            <img src="{{ asset('images/no-image.png') }}" alt=""
                                                                class="rounded-circle border" width="55" height="55">
                                                        @endif

                                                    </div>

                                                    {{-- Name --}}
                                                    <div>

                                                        <div class="fw-bold text-dark">

                                                            {{ $data->name }}

                                                        </div>

                                                        <div class="text-muted small">

                                                            {{ $data->class_name ?? '' }}

                                                            @if ($data->section_name)
                                                                -
                                                                {{ $data->section_name }}
                                                            @endif

                                                        </div>

                                                    </div>

                                                </div>

                                            </td>

                                            {{-- Roll --}}
                                            <td>
                                                <span class="badge badge-light-primary">

                                                    {{ $data->roll }}

                                                </span>
                                            </td>

                                            {{-- Due Details --}}
                                            <td style="word-break: break-word;">

                                                @php
                                                    $hasDue = false;
                                                @endphp

                                                @foreach ($data->feeHeads as $feeHead)
                                                    @php
                                                        $subHeadData = count($feeHead->feeSubHeads);
                                                    @endphp

                                                    @if ($subHeadData > 0)
                                                        @php
                                                            $hasDue = true;
                                                        @endphp

                                                        <div class="mb-3">

                                                            <div class="fw-bold text-primary mb-1">

                                                                {{ $feeHead->name }}

                                                            </div>

                                                            @foreach ($feeHead->feeSubHeads as $subHead)
                                                                <span class="badge badge-light-danger me-2 mb-2 p-2">

                                                                    {{ $subHead->name }}

                                                                    ({{ format_currency($feeHead->amount[$subHead->id]['fee_and_fine_payable'] ?? 0) }})
                                                                </span>
                                                            @endforeach

                                                        </div>
                                                    @endif
                                                @endforeach

                                                @if (!$hasDue)
                                                    <span class="badge badge-light-success">

                                                        {{ _lang('No Due') }}

                                                    </span>
                                                @endif

                                            </td>

                                            {{-- Total Due --}}
                                            <td class="text-end">

                                                <span class="fw-bold text-danger fs-5">

                                                    {{ format_currency($data->total_paid_amount) }}

                                                </span>

                                            </td>

                                        </tr>
                                    @endif

                                @empty

                                    <tr>

                                        <td colspan="5" class="text-center py-10">

                                            <div class="text-muted">

                                                <img src="{{ asset('images/no-data.webp') }}" width="120"
                                                    class="mb-3">

                                                <h5>
                                                    {{ _lang('No unpaid data found') }}
                                                </h5>

                                            </div>

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

