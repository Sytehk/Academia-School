@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Modern School Dashboard') }}
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome-6/all.min.css') }}" />
    @include('partials.local-assets.scripts', ['chartjs' => true])

    <style>
        :root {
            --primary: #3b82f6;
            --primary-2: #6366f1;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
            --cyan: #06b6d4;

            --bg-body: #f4f7fb;
            --bg-card: #ffffff;
            --bg-card-2: #f8fafc;

            --text-main: #111827;
            --text-muted: #6b7280;

            --border-color: rgba(15, 23, 42, 0.08);
            --shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
        }

        html[data-bs-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #111827;
            --bg-card-2: #1e293b;

            --text-main: #ffffff;
            --text-muted: #94a3b8;

            --border-color: rgba(255, 255, 255, 0.08);
            --shadow: 0 15px 40px rgba(0, 0, 0, .45);
        }

        body {
            background: var(--bg-body) !important;
            color: var(--text-main) !important;
            font-family: Inter, sans-serif;
        }

        .dashboard-wrapper {
            padding: 20px;
        }

        .dashboard-card {
            background: var(--bg-card);
            border-radius: 22px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: .3s ease;
            height: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
        }

        .dashboard-card .card-body {
            padding: 22px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 2px;
            color: var(--text-main);
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 13px;
        }

        .hero-card {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            border-radius: 26px;
            overflow: hidden;
            position: relative;
            padding: 35px;
            box-shadow: 0 20px 50px rgba(59, 130, 246, .25);
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            right: -100px;
            top: -100px;
            background: rgba(255, 255, 255, .08);
        }

        .hero-card * {
            color: white !important;
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 32px;
            font-weight: 800;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            padding: 22px;
            color: white;
            min-height: 145px;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .12);
            border-radius: 50%;
            right: -50px;
            top: -50px;
        }

        .stat-blue {
            background: linear-gradient(135deg, #2563eb, #60a5fa);
        }

        .stat-purple {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
        }

        .stat-cyan {
            background: linear-gradient(135deg, #0891b2, #67e8f9);
        }

        .stat-green {
            background: linear-gradient(135deg, #059669, #34d399);
        }

        .stat-content {
            position: relative;
            z-index: 2;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 18px;
        }

        .stat-number {
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            opacity: .9;
        }

        .chart-box {
            position: relative;
            height: 340px;
        }

        .chart-box-sm {
            position: relative;
            height: 280px;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calendar-table th,
        .calendar-table td {
            border: 1px solid var(--border-color);
            padding: 14px;
            min-width: 120px;
            height: 80px;
            vertical-align: top;
            color: var(--text-main);
        }

        .event-box {
            border-radius: 12px;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .event-purple {
            background: rgba(139, 92, 246, .15);
            border: 1px solid #8b5cf6;
            color: #8b5cf6;
        }

        .event-blue {
            background: rgba(59, 130, 246, .15);
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .event-green {
            background: rgba(16, 185, 129, .15);
            border: 1px solid #10b981;
            color: #10b981;
        }

        .notice-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 14px;
        }

        .notice-warning {
            background: rgba(245, 158, 11, .12);
        }

        .notice-success {
            background: rgba(16, 185, 129, .12);
        }

        .notice-danger {
            background: rgba(239, 68, 68, .12);
        }

        .activity-item {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .activity-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            flex-shrink: 0;
        }

        .custom-table {
            width: 100%;
        }

        .custom-table tr {
            border-bottom: 1px solid var(--border-color);
        }

        .custom-table td {
            padding: 14px 10px;
            color: var(--text-main);
        }

        .badge-modern {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-success {
            background: rgba(16, 185, 129, .15);
            color: #10b981;
        }

        .badge-danger {
            background: rgba(239, 68, 68, .15);
            color: #ef4444;
        }

        .badge-warning {
            background: rgba(245, 158, 11, .15);
            color: #f59e0b;
        }

        @media(max-width: 992px) {
            .hero-title {
                font-size: 24px;
            }

            .chart-box {
                height: 260px;
            }

            .calendar-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>

    <div class="dashboard-wrapper">

        <x-system-update-status />

        {{-- HEADER --}}
        <div class="hero-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="hero-title">
                        Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋
                    </div>

                    <p class="mb-0 mt-2 opacity-75">
                        Track real-time school statistics and key performance metrics.
                    </p>
                </div>

                <div>
                    <button class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-plus me-2"></i>
                        Quick Action
                    </button>
                </div>
            </div>
        </div>

        {{-- TOP STATS --}}
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="stat-card stat-blue">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>

                        <div class="stat-number">
                            {{ $data['admins'] ?? 0 }}+
                        </div>

                        <div class="stat-label">
                            Total Admin
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card stat-purple">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>

                        <div class="stat-number">
                            {{ $data['students'] ?? 0 }}+
                        </div>

                        <div class="stat-label">
                            Total Students
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card stat-cyan">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>

                        <div class="stat-number">
                            {{ $data['teachers'] ?? 0 }}+
                        </div>

                        <div class="stat-label">
                            Total Teachers
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card stat-green">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>

                        <div class="stat-number">
                            {{ $data['staffs'] ?? 0 }}+
                        </div>

                        <div class="stat-label">
                            Total Staffs
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- CHARTS ROW --}}
        <div class="row g-4 mb-4">

            {{-- ATTENDANCE SUMMARY --}}
            <div class="col-xl-8">
                <div class="dashboard-card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div>
                                <div class="section-title">Attendance Summary</div>
                                <div class="section-subtitle">
                                    Monitor student attendance trends and daily participation.
                                </div>
                            </div>
                        </div>

                        <div class="chart-box">
                            <canvas id="attendanceSummaryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STUDENT GENDER --}}
            <div class="col-xl-4">
                <div class="dashboard-card h-100">
                    <div class="card-body">

                        <div class="section-title">Students</div>
                        <div class="section-subtitle mb-4">
                            Total Student Gender Distribution
                        </div>

                        <div class="chart-box-sm">
                            <canvas id="studentGenderChart"></canvas>
                        </div>

                        <div class="row mt-4 text-center">
                            <div class="col-6">
                                <h4 class="fw-bold mb-1">
                                    {{ $data['student_info']['total_male_students'] ?? 0 }}
                                </h4>

                                <div class="text-muted">
                                    Male ({{ $data['student_info']['male_percentage'] ?? 0 }}%)
                                </div>
                            </div>

                            <div class="col-6">
                                <h4 class="fw-bold mb-1">
                                    {{ $data['student_info']['total_female_students'] ?? 0 }}
                                </h4>

                                <div class="text-muted">
                                    Female ({{ $data['student_info']['female_percentage'] ?? 0 }}%)
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- FEES + INCOME --}}
        <div class="row g-4 mb-4">

            <div class="col-xl-12">
                <div class="dashboard-card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div>
                                <div class="section-title">Fees Collection Overview</div>
                                <div class="section-subtitle">
                                    Track collected and pending fees over time.
                                </div>
                            </div>
                        </div>

                        <div class="chart-box">
                            <canvas id="feesCollectionChart"></canvas>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="dashboard-card">
                    <div class="card-body">

                        <div class="section-title">
                            Income & Expenses Overview
                        </div>

                        <div class="section-subtitle mb-4">
                            Track the institution financial performance.
                        </div>

                        <div class="chart-box">
                            <canvas id="incomeExpenseChart"></canvas>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- CALENDAR + NOTICE + ACTIVITY --}}
        <div class="row g-4 mb-4">

            <div class="col-xl-8">
                <div class="dashboard-card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div class="section-title">
                                Live Class
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Topic</th>
                                        <th>Teacher</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Start Time</th>
                                        <th>Join</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($data['meetings'] as $key => $meeting)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>

                                            <td>{{ $meeting->topic }}</td>

                                            <td>{{ $meeting->teacher->name ?? '-' }}</td>

                                            <td>{{ $meeting->class->name ?? '-' }}</td>

                                            <td>{{ $meeting->section->name ?? '-' }}</td>

                                            <td>
                                                {{ \Carbon\Carbon::parse($meeting->start_time)->format('d M Y h:i A') }}
                                            </td>

                                            <td>
                                                @if ($meeting->join_url)
                                                    <a href="{{ $meeting->join_url }}" target="_blank"
                                                        class="btn btn-sm btn-primary">
                                                        Join
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No live classes found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="row g-4">

                    <div class="col-12">
                        <div class="dashboard-card">
                            <div class="card-body">

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <div class="section-title">
                                            Notice Board
                                        </div>

                                        <div class="section-subtitle">
                                            Latest announcements
                                        </div>
                                    </div>
                                </div>

                                @forelse($data['notices'] as $notice)
                                    <div
                                        class="notice-item
                                            @if ($loop->index % 3 == 0) notice-warning
                                            @elseif($loop->index % 3 == 1) notice-success
                                            @else notice-danger @endif
                                        ">
                                        <div>
                                            {{ $notice->title }}
                                        </div>

                                        <div class="text-muted small">
                                            {{ \Carbon\Carbon::parse($notice->date)->format('d M') }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">
                                        No notices available
                                    </div>
                                @endforelse

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

        const textColor = isDark ? '#e5e7eb' : '#374151';
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';

        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

        // ATTENDANCE SUMMARY
        new Chart(document.getElementById('attendanceSummaryChart'), {
            type: 'bar',
            data: {
                labels: @json($data['attendance_chart']['labels']),
                datasets: [{
                        label: 'Present',
                        data: @json($data['attendance_chart']['present']),
                        backgroundColor: '#3b82f6',
                        borderRadius: 12,
                        barThickness: 18
                    },
                    {
                        label: 'Absent',
                        data: @json($data['attendance_chart']['absent']),
                        backgroundColor: '#dbeafe',
                        borderRadius: 12,
                        barThickness: 18
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // STUDENT GENDER
        new Chart(document.getElementById('studentGenderChart'), {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [
                        {{ $data['student_info']['total_male_students'] ?? 0 }},
                        {{ $data['student_info']['total_female_students'] ?? 0 }}
                    ],
                    backgroundColor: ['#3b82f6', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '72%',
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // FEES COLLECTION
        new Chart(document.getElementById('feesCollectionChart'), {
            type: 'bar',
            data: {
                labels: @json($data['fees_chart']['labels']),
                datasets: [{
                        label: 'Collected',
                        data: @json($data['fees_chart']['collected']),
                        backgroundColor: '#3b82f6',
                        borderRadius: 10
                    },
                    {
                        label: 'Outstanding',
                        data: @json($data['fees_chart']['pending']),
                        backgroundColor: '#f59e0b',
                        borderRadius: 10
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // INCOME EXPENSE
        new Chart(document.getElementById('incomeExpenseChart'), {
            type: 'line',
            data: {
                labels: @json($data['income_expense_chart']['labels']),
                datasets: [{
                        label: 'Total Income',
                        data: @json($data['income_expense_chart']['income']),
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,.15)',
                        fill: true,
                        tension: .4
                    },
                    {
                        label: 'Total Expenses',
                        data: @json($data['income_expense_chart']['expense']),
                        borderColor: '#f43f5e',
                        backgroundColor: 'rgba(244,63,94,.08)',
                        fill: true,
                        tension: .4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
@endsection
