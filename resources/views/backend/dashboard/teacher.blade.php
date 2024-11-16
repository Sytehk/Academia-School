@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher Dashboard') }}
@endsection

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="row g-5 g-xl-8">

                <!-- Students -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted">Total Students</div>
                            <div class="fs-2hx fw-bolder text-primary">
                                {{ $students }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted">Today's Attendance</div>
                            <div class="fs-2hx fw-bolder text-dark">
                                {{ $today_attendance }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Present -->
                <div class="col-xl-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-success fw-bold">Present</div>
                            <div class="fs-2hx fw-bolder text-success">
                                {{ $present_students }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absent -->
                <div class="col-xl-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-danger fw-bold">Absent</div>
                            <div class="fs-2hx fw-bolder text-danger">
                                {{ $absent_students }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TODAY ROUTINE -->
            <div class="row mt-5">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Today's Class Routine</h3>
                        </div>

                        <div class="card-body">

                            @if (count($today_routines))
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Section</th>
                                            <th>Subject</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($today_routines as $routine)
                                            <tr>
                                                <td>{{ $routine->class->name ?? '' }}</td>
                                                <td>{{ $routine->section->name ?? '' }}</td>
                                                <td>{{ $routine->subject->name ?? '' }}</td>
                                                <td>
                                                    {{ $routine->start_time }} - {{ $routine->end_time }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-muted text-center">
                                    No classes scheduled today
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
