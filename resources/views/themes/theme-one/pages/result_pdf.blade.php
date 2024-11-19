<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Result Sheet</title>

    <style>
        @page {
            size: A4;
            margin: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff;
            color: #111;
        }

        .container {
            position: relative;
            padding: 20px;
            border: 2px solid #e5e7eb;
        }

        /* HEADER */
        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            padding: 10px;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }

        /* WATERMARK */
        .watermark {
            position: absolute;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            z-index: 0;
        }

        .watermark img {
            width: 300px;
        }

        /* LAYOUT */
        .row {
            width: 100%;
            display: table;
            margin-bottom: 15px;
        }

        .col-2 {
            width: 18%;
            display: table-cell;
            vertical-align: top;
        }

        .col-10 {
            width: 82%;
            display: table-cell;
            vertical-align: top;
            padding-left: 15px;
        }

        /* IMAGE */
        .photo {
            width: 120px;
            height: 140px;
            border: 2px solid #ccc;
            object-fit: cover;
        }

        /* INFO TABLE */
        table.info {
            width: 100%;
            border-collapse: collapse;
        }

        table.info td {
            padding: 6px;
            font-size: 13px;
        }

        table.info td b {
            font-weight: 700;
        }

        /* MARK TABLE */
        table.marks {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.marks th,
        table.marks td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }

        table.marks th {
            background: #f1f5f9;
        }

        .text-left {
            text-align: left;
        }

        /* SUMMARY */
        .summary {
            margin-top: 20px;
        }

        .summary-box {
            width: 24%;
            display: inline-block;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .summary-box h2 {
            margin: 5px 0;
            font-size: 18px;
        }

        /* SIGNATURE */
        .signatures {
            margin-top: 40px;
            width: 100%;
        }

        .sig {
            width: 32%;
            display: inline-block;
            text-align: center;
            font-size: 12px;
        }

        .line {
            margin-top: 50px;
            border-top: 1px solid #000;
        }

        /* FOOTER */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            padding: 10px;
            background: #111;
            color: #fff;
        }

        @media print {
            .hide-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        {{-- WATERMARK --}}
        <div class="watermark">
            <img src="{{ !empty($institute?->logo) ? asset('storage/institute/'.$institute->logo) : asset('uploads/demo-school-logo.png') }}">
        </div>

        {{-- TITLE --}}
        <div class="title">
            ACADEMIC TRANSCRIPT & RESULT SHEET
        </div>

        {{-- STUDENT INFO --}}
        <div class="row">

            <div class="col-2">
                <img class="photo"
                    src="{{ $student->photo ? asset($student->photo) : 'https://ui-avatars.com/api/?name=' . $student->first_name }}">
            </div>

            <div class="col-10">

                <table class="info">

                    <tr>
                        <td><b>Name:</b> {{ $student->first_name }} {{ $student->last_name }}</td>
                        <td><b>Roll:</b> {{ $student->studentSession->roll ?? '' }}</td>
                    </tr>

                    <tr>
                        <td><b>Father:</b> {{ $student->father_name }}</td>
                        <td><b>Mother:</b> {{ $student->mother_name }}</td>
                    </tr>

                    <tr>
                        <td><b>Class:</b> {{ $student->studentSession->class->class_name ?? '' }}</td>
                        <td><b>Section:</b> {{ $student->studentSession->section->section_name ?? '' }}</td>
                    </tr>

                    <tr>
                        <td><b>Phone:</b> {{ $student->phone }}</td>
                        <td><b>Exam:</b> {{ $results->first()->exam->name ?? '' }}</td>
                    </tr>

                </table>

            </div>
        </div>

        {{-- MARK TABLE --}}
        <table class="marks">

            <thead>
                <tr>
                    <th>SL</th>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Grade</th>
                    <th>GPA</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($results as $i => $mark)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="text-left">{{ $mark->subject->subject_name }}</td>
                        <td>{{ $mark->total_marks }}</td>
                        <td>{{ $mark->grade }}</td>
                        <td>{{ $mark->grade_point }}</td>
                        <td>
                            @if ($mark->total_marks >= 33)
                                PASS
                            @else
                                FAIL
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>

        {{-- SUMMARY --}}
        <div class="summary">

            <div class="summary-box">
                <div>Total</div>
                <h2>{{ $summary['total_marks'] ?? 0 }}</h2>
            </div>

            <div class="summary-box">
                <div>Average</div>
                <h2>{{ $summary['average'] ?? 0 }}</h2>
            </div>

            <div class="summary-box">
                <div>Grade</div>
                <h2>{{ $summary['grade'] ?? 'F' }}</h2>
            </div>

            <div class="summary-box">
                <div>GPA</div>
                <h2>{{ $summary['gpa'] ?? 0 }}</h2>
            </div>

        </div>

        {{-- SIGNATURE --}}
        <div class="signatures">

            <div class="sig">
                <div class="line"></div>
                Class Teacher
            </div>

            <div class="sig">
                <div class="line"></div>
                Exam Controller
            </div>

            <div class="sig">
                <div class="line"></div>
                Principal
            </div>

        </div>

        {{-- FOOTER --}}
        <div class="footer">
            This is a computer generated result sheet.
        </div>

    </div>

</body>

</html>
