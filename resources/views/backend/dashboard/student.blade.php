@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Dashboard') }}
@endsection

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <!-- TOP STATS -->
            <div class="row g-5 g-xl-8">

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted">Total Attendance</div>
                            <div class="fs-2hx fw-bolder text-primary">
                                {{ $attendance }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-success">Present</div>
                            <div class="fs-2hx fw-bolder text-success">
                                {{ $present }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-danger">Absent</div>
                            <div class="fs-2hx fw-bolder text-danger">
                                {{ $absent }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TODAY STATUS -->
            <div class="row mt-5">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body text-center">

                            <h4>Today's Attendance</h4>

                            @if ($today_attendance)
                                <span class="badge bg-{{ $today_attendance->attendance == 1 ? 'success' : 'danger' }}">
                                    {{ $today_attendance->attendance == 1 ? 'Present' : 'Absent' }}
                                </span>
                            @else
                                <span class="badge bg-secondary">Not Marked</span>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <!-- ⭐ CLASS ROUTINE SECTION -->
            <div class="row mt-5">
                <div class="col-xl-12">

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Today's Class Routine</h3>
                        </div>

                        <div class="card-body">

                            @if (count($today_routines) > 0)
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Section</th>
                                            <th>Subject</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($today_routines as $routine)
                                            <tr>
                                                <td>{{ $routine->class->name ?? '' }}</td>
                                                <td>{{ $routine->section->name ?? '' }}</td>
                                                <td>{{ $routine->subject->name ?? '' }}</td>
                                                <td>{{ $routine->start_time }}</td>
                                                <td>{{ $routine->end_time }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            @else
                                <div class="text-center text-muted">
                                    No classes scheduled today
                                </div>
                            @endif

                        </div>

                    </div>

                </div>
            </div>

            <!-- ACADEMIC SECTION -->
            <div class="row mt-5">

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted">Fees Due</div>
                            <div class="fs-2hx fw-bolder text-warning">
                                {{ $fees_due }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>Upcoming Exams</h5>

                            @forelse($upcoming_exams as $exam)
                                <div class="border-bottom py-2">
                                    <strong>{{ $exam->name }}</strong><br>
                                    <small>{{ $exam->exam_date }}</small>
                                </div>
                            @empty
                                <p class="text-muted">No upcoming exams</p>
                            @endforelse

                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>Assignments</h5>

                            @forelse($assignments as $assignment)
                                <div class="border-bottom py-2">
                                    <strong>{{ $assignment->title }}</strong><br>
                                    <small>{{ $assignment->created_at->format('d M Y') }}</small>
                                </div>
                            @empty
                                <p class="text-muted">No assignments</p>
                            @endforelse

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
