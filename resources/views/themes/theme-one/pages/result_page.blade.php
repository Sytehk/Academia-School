@extends('themes.theme-one.layouts.app')

@section('content')

    <style>
        body {
            background: #edf2f7;
        }

        .result-wrapper {
            max-width: 1200px;
            margin: auto;
        }

        .board-card {
            background: #fff;
            border: 10px solid #0f172a;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .15);
        }

        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: .05;
            z-index: 1;
        }

        .watermark img {
            width: 500px;
        }

        .result-content {
            position: relative;
            z-index: 5;
        }

        .school-header {
            background: linear-gradient(135deg, #0f172a, #1e293b, #0f766e);
            color: white;
            padding: 30px;
        }

        .school-title {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .board-title {
            background: #0f766e;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 28px;
            font-weight: 800;
        }

        .info-table td {
            border: 1px solid #d1d5db;
            padding: 10px;
            font-size: 14px;
        }

        .mark-table th {
            background: #0f172a;
            color: white;
            padding: 12px;
            border: 1px solid #cbd5e1;
            font-size: 13px;
        }

        .mark-table td {
            border: 1px solid #cbd5e1;
            padding: 10px;
            font-size: 13px;
            text-align: center;
        }

        .summary-box {
            background: linear-gradient(135deg, #0f766e, #0f172a);
            color: white;
            border-radius: 16px;
            padding: 20px;
        }

        .signature-box {
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 60px;
            text-align: center;
            font-weight: 700;
        }

        .print-btn {
            background: #0f172a;
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
        }

        @media print {

            body {
                background: white;
            }

            .hide-print {
                display: none !important;
            }

            .board-card {
                border: none;
                box-shadow: none;
            }
        }
    </style>

    <section class="py-10">

        <div class="result-wrapper px-4">

            {{-- SEARCH FORM --}}
            <div class="hide-print bg-white rounded-3xl shadow-xl p-6 mb-8">

                <div class="text-center mb-6">
                    <h1 class="text-4xl font-black text-slate-800">
                        Exam Result Portal
                    </h1>
                    <p class="text-slate-500 mt-2">
                        Search Result by Class, Roll Number or QR Code
                    </p>
                </div>

                <form method="GET" class="grid md:grid-cols-4 gap-5">

                    <div>
                        <label class="font-semibold text-sm">Class *</label>
                        <select name="class_id" class="w-full border rounded-xl px-4 py-3 mt-2">
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ 1 == $class->id ? 'selected' : '' }}>
                                    {{ $class->class_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-semibold text-sm">Roll Number</label>
                        <input type="text" name="roll_no" value="{{ request('roll_no') ?? 1 }}"
                            class="w-full border rounded-xl px-4 py-3 mt-2" placeholder="Enter Roll Number">
                    </div>

                    <div>
                        <label class="font-semibold text-sm">QR Code</label>
                        <input type="text" name="qr_code" value="{{ request('qr_code') }}"
                            class="w-full border rounded-xl px-4 py-3 mt-2" placeholder="Enter QR Code">
                    </div>

                    <div class="flex items-end">
                        <button class="w-full bg-emerald-600 text-white py-3 rounded-xl font-bold">
                            Search Result
                        </button>
                    </div>

                </form>
            </div>

            @if ($student)
                {{-- <div class="text-center mb-6 hide-print">
                    <a href="{{ route('result.pdf', $student->id) }}"
                        style="
                                display:inline-block;
                                background:#2563eb;
                                color:#fff;
                                padding:10px 18px;
                                border-radius:10px;
                                font-weight:600;
                                text-decoration:none;
                        ">
                        ⬇ Download PDF Result
                    </a>
                </div> --}}

                <div class="board-card rounded-3xl">

                    {{-- WATERMARK --}}
                    <div class="watermark">
                        <img src="{{ !empty($institute?->logo) ? asset('storage/institute/'.$institute->logo) : asset('uploads/demo-school-logo.png') }}">
                    </div>

                    <div class="result-content">
                        {{-- RESULT TITLE --}}
                        <div class="board-title">
                            ACADEMIC TRANSCRIPT & RESULT SHEET
                        </div>


                        {{-- STUDENT INFO --}}
                        <div class="p-6">
                            <div class="grid grid-cols-12 gap-5 items-start">
                                {{-- PHOTO --}}
                                <div class="col-span-2">
                                    <img src="{{ $student->user?->image ? asset('storage/users/' . $student->user?->image) : 'https://ui-avatars.com/api/?name=' . $student->first_name }}"
                                        class="w-40 h-44 object-cover border-4 border-slate-200 rounded-xl">
                                </div>

                                {{-- DETAILS --}}
                                <div class="col-span-10">

                                    <table class="w-full info-table">
                                        <tr>
                                            <td width="20%"><b>Student Name</b></td>
                                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>

                                            <td width="20%"><b>Admission No</b></td>
                                            <td>{{ $student->admission_no ?? '2025001' }}</td>
                                        </tr>

                                        <tr>
                                            <td><b>Father's Name</b></td>
                                            <td>{{ $student->father_name ?? 'Mr. John Doe' }}</td>

                                            <td><b>Mother's Name</b></td>
                                            <td>{{ $student->mother_name ?? 'Mrs. Jane Doe' }}</td>
                                        </tr>

                                        <tr>
                                            <td><b>Class</b></td>
                                            <td>{{ $student->studentSession->class->class_name ?? 'N/A' }}</td>

                                            <td><b>Section</b></td>
                                            <td>{{ $student->studentSession->section->section_name ?? 'A' }}</td>
                                        </tr>

                                        <tr>
                                            <td><b>Roll Number</b></td>
                                            <td>{{ $student->studentSession->roll ?? '' }}</td>

                                            <td><b>Group</b></td>
                                            <td>{{ $student->studentSession->group->group_name ?? 'Science' }}</td>
                                        </tr>

                                        <tr>
                                            <td><b>Date of Birth</b></td>
                                            <td>{{ $student->dob ?? '2010-01-01' }}</td>

                                            <td><b>Session</b></td>
                                            <td>2025</td>
                                        </tr>

                                        <tr>
                                            <td><b>Institute</b></td>
                                            <td>Demo International School & College</td>

                                            <td><b>Exam</b></td>
                                            <td>{{ $results->first()->exam->name ?? 'Final Exam' }}</td>
                                        </tr>

                                    </table>

                                </div>

                            </div>

                        </div>


                        {{-- RESULT TABLE --}}
                        <div class="px-6 pb-6">

                            <table class="w-full mark-table border-collapse">

                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Subject</th>
                                        <th>Written</th>
                                        <th>MCQ</th>
                                        <th>Practical</th>
                                        <th>Total</th>
                                        <th>Grade</th>
                                        <th>GPA</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @php
                                        $i = 1;
                                    @endphp

                                    @foreach ($results as $mark)
                                        <tr>
                                            <td>{{ $i++ }}</td>

                                            <td class="text-left px-3">
                                                {{ $mark->subject->subject_name ?? 'Subject' }}
                                            </td>

                                            <td>{{ $mark->written_marks ?? rand(50, 80) }}</td>

                                            <td>{{ $mark->mcq_marks ?? rand(10, 20) }}</td>

                                            <td>{{ $mark->practical_marks ?? rand(10, 20) }}</td>

                                            <td>
                                                <b>{{ $mark->total_marks }}</b>
                                            </td>

                                            <td>
                                                {{ $mark->grade ?? 'A+' }}
                                            </td>

                                            <td>
                                                {{ $mark->grade_point ?? '5.00' }}
                                            </td>

                                            <td>
                                                @if ($mark->total_marks >= 33)
                                                    <span class="text-green-600 font-bold">PASSED</span>
                                                @else
                                                    <span class="text-red-600 font-bold">FAILED</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>


                        {{-- SUMMARY --}}
                        <div class="grid md:grid-cols-4 gap-5 px-6 pb-8">

                            <div class="summary-box">
                                <p class="text-sm opacity-80">Total Marks</p>
                                <h2 class="text-4xl font-black mt-2">
                                    {{ $summary['total_marks'] ?? 0 }}
                                </h2>
                            </div>

                            <div class="summary-box">
                                <p class="text-sm opacity-80">Average Marks</p>
                                <h2 class="text-4xl font-black mt-2">
                                    {{ $summary['average'] ?? 0 }}%
                                </h2>
                            </div>

                            <div class="summary-box">
                                <p class="text-sm opacity-80">Final Grade</p>
                                <h2 class="text-4xl font-black mt-2">
                                    {{ $summary['grade'] ?? 'F' }}
                                </h2>
                            </div>

                            <div class="summary-box">
                                <p class="text-sm opacity-80">GPA / Result</p>
                                <h2 class="text-3xl font-black mt-2">
                                    {{ $summary['gpa'] ?? 0 }}
                                    ({{ $summary['status'] ?? 'FAILED' }})
                                </h2>
                            </div>
                        </div>

                        {{-- REMARKS --}}
                        <div class="px-6 pb-8">

                            <div class="bg-slate-100 rounded-2xl p-5 border">

                                <h2 class="text-2xl font-black mb-3 text-slate-800">
                                    Teacher's Remarks
                                </h2>

                                <p class="text-slate-700 leading-8">
                                    The student showed excellent academic performance throughout the examination.
                                    He/She demonstrated discipline, punctuality, leadership and dedication in all subjects.
                                    We appreciate the student's effort and wish him/her continued success.
                                </p>

                            </div>

                        </div>


                        {{-- SIGNATURES --}}
                        <div class="grid grid-cols-3 gap-10 px-10 pb-14">

                            <div>
                                <div class="signature-box">
                                    Class Teacher Signature
                                </div>
                            </div>

                            <div>
                                <div class="signature-box">
                                    Exam Controller Signature
                                </div>
                            </div>

                            <div>
                                <div class="signature-box">
                                    Principal Signature
                                </div>
                            </div>

                        </div>


                        {{-- FOOTER --}}
                        <div class="bg-slate-900 text-white text-center py-4 text-sm">
                            This is a computer generated academic transcript.
                            Powered By School Management ERP System.
                        </div>

                    </div>

                </div>
            @endif

        </div>

    </section>

@endsection
