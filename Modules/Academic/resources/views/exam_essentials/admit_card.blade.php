<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Exam Admit Card</title>

    <style>
        * {
            box-sizing: border-box;
        }

        {{--
            Institutes print two admit cards per sheet by hand (a landscape page split
            into a left and right half, cut apart afterward) — this page now produces that
            layout directly instead of one card per full page (UI-006).
        --}}
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            width: 100%;
            margin: 0 auto;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #1a1a1a;
        }

        .card-page {
            display: flex;
            gap: 0;
            width: 100%;
            height: 190mm;
            page-break-after: always;
        }

        .card-page:last-child {
            page-break-after: auto;
        }

        .card-slot {
            flex: 1 1 50%;
            max-width: 50%;
            padding: 4mm;
            position: relative;
            overflow: hidden;
        }

        /* Cut line between the two halves */
        .card-slot:first-child {
            border-right: 1px dashed #999;
        }

        .admit-card {
            border: 4px double #14532d;
            border-radius: 6px;
            padding: 10px 14px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .card-header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #14532d;
            padding-bottom: 6px;
            margin-bottom: 6px;
            flex-shrink: 0;
        }

        .card-header .logo-cell,
        .card-header .school-cell,
        .card-header .photo-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .card-header .logo-cell {
            width: 60px;
        }

        .card-header .logo-cell img {
            width: 54px;
            height: 54px;
            object-fit: contain;
        }

        .card-header .school-cell {
            text-align: center;
            padding: 0 8px;
        }

        .card-header .school-cell h1 {
            font-size: 18px;
            margin: 0;
            color: #14532d;
            letter-spacing: 0.5px;
        }

        .card-header .school-cell p {
            font-size: 11px;
            margin: 2px 0 0 0;
        }

        .card-header .photo-cell {
            width: 78px;
            text-align: center;
        }

        .card-header .photo-cell img.student-photo {
            width: 66px;
            height: 78px;
            object-fit: cover;
            border: 1px solid #14532d;
        }

        .card-header .photo-cell .qr-wrap {
            margin-top: 4px;
        }

        .card-title-banner {
            text-align: center;
            background-color: #14532d;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 4px 0;
            margin-bottom: 8px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        /* Student info */
        .student-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 12px;
            flex-shrink: 0;
        }

        .student-info-table td {
            padding: 2px 4px;
        }

        .student-info-table td.label {
            width: 32%;
            font-weight: 600;
            color: #333;
        }

        .student-info-table td.sep {
            width: 2%;
        }

        /* Exam routine */
        .section-label {
            background-color: #f0f4f0;
            border: 1px solid #14532d;
            color: #14532d;
            font-weight: 700;
            font-size: 12px;
            padding: 3px 8px;
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .routine-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            flex: 1 1 auto;
            overflow: hidden;
        }

        .routine-table th,
        .routine-table td {
            border: 1px solid #14532d;
            text-align: center;
        }

        .routine-table th {
            background: #e9f0e9;
            color: #14532d;
        }

        /* Instructions */
        .instructions-box {
            border: 1px solid #999;
            border-radius: 3px;
            padding: 6px 8px;
            font-size: 10px;
            margin-bottom: 8px;
            flex-shrink: 0;
        }

        .instructions-box ol {
            margin: 3px 0 0 14px;
            padding: 0;
        }

        .instructions-box li {
            margin-bottom: 2px;
        }

        /* Signatures */
        .signature-row {
            display: table;
            width: 100%;
            margin-top: auto;
            flex-shrink: 0;
        }

        .signature-row .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            font-size: 10px;
            padding-top: 18px;
        }

        .signature-cell .sign-line {
            border-top: 1px solid #333;
            width: 85%;
            margin: 0 auto 3px auto;
        }

        .footer-credit {
            text-align: center;
            font-size: 8px;
            color: #888;
            margin-top: 4px;
            flex-shrink: 0;
        }
    </style>
</head>

<body>

    @foreach ($data->chunk(2) as $pair)
        <div class="card-page">
            @foreach ($pair as $single)
                @php
                    $compulsorySubjects = \Modules\Academic\Models\Subject::select('*', 'subjects.id AS id')
                        ->join('classes', 'classes.id', '=', 'subjects.class_id')
                        ->where('subjects.class_id', $single->class_id)
                        ->whereNull('subjects.group_id')
                        ->get();

                    $studentSubjects = \Modules\Academic\Models\Subject::select('*', 'subjects.id AS id')
                        ->join('classes', 'classes.id', '=', 'subjects.class_id')
                        ->where('subjects.class_id', (int) $single->class_id)
                        ->where('subjects.group_id', (int) $single->group_id)
                        ->get();

                    $subjects = $compulsorySubjects->merge($studentSubjects);

                    $examSchedule = \Illuminate\Support\Facades\DB::table('exam_schedules')->first();
                    $examName = null;
                    if ($examSchedule) {
                        $examName = \Illuminate\Support\Facades\DB::table('exams')
                            ->where('id', $examSchedule->exam_id)
                            ->first();
                    }

                    $routineRows = $subjects
                        ->map(function ($subject) {
                            $schedule = \Illuminate\Support\Facades\DB::table('exam_schedules')
                                ->where('subject_id', $subject->id)
                                ->first();

                            return [
                                'date' => isset($schedule->date) && $schedule->date
                                    ? \Carbon\Carbon::parse($schedule->date)->format('d/m/Y')
                                    : '',
                                'subject_name' => $subject->subject_name,
                                'subject_code' => $subject->subject_code,
                                'sort' => $schedule->date ?? '9999-99-99',
                            ];
                        })
                        ->sortBy('sort')
                        ->values();

                    // Compact the routine table's type size as the subject count grows so a
                    // class with 14-15 subjects still fits within one card slot instead of
                    // spilling onto a second page (UI-006). 8 or fewer subjects keeps the
                    // original comfortable size.
                    $subjectCount = $routineRows->count();
                    $routineFontSize = $subjectCount > 8 ? max(8, 12 - ($subjectCount - 8) * 0.5) : 12;
                    $routineCellPadding = $subjectCount > 8 ? max(2, 4 - ($subjectCount - 8) * 0.25) : 4;
                @endphp

                <div class="card-slot">
                    <!-- Single Admit-Card Start -->
                    <div class="admit-card">
                        <div class="card-header">
                            <div class="logo-cell">
                                <img src="{{ asset('/' . get_logo_file_path()) }}" alt="Logo">
                            </div>
                            <div class="school-cell">
                                <h1>{{ get_option('school_name') }}</h1>
                                <p>{{ get_option('address') }}</p>
                            </div>
                            <div class="photo-cell">
                                <img src="{{ asset('storage/users/' . $single->student?->user?->image) }}" alt="Photo"
                                    class="student-photo">
                                <div class="qr-wrap">
                                    @php
                                        $texts = "ID: $single->student_id\n";
                                        $texts .= 'Name: ' . $single->student->first_name . " \n";
                                    @endphp
                                    {!! QrCode::size(42)->encoding('UTF-8')->generate($texts) !!}
                                </div>
                            </div>
                        </div>

                        <div class="card-title-banner">ADMIT CARD</div>

                        <table class="student-info-table">
                            <tr>
                                <td class="label">Name</td>
                                <td class="sep">:</td>
                                <td><strong>{{ $single->student->first_name }}</strong></td>
                                <td class="label">Roll No.</td>
                                <td class="sep">:</td>
                                <td><strong>{{ $single->roll }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Student ID</td>
                                <td class="sep">:</td>
                                <td>{{ $single->student->id }}</td>
                                <td class="label">Registration No.</td>
                                <td class="sep">:</td>
                                <td>{{ $single->student_online_registration?->registration_number }}</td>
                            </tr>
                            <tr>
                                <td class="label">Class</td>
                                <td class="sep">:</td>
                                <td>{{ $single->class->class_name ?? '' }}</td>
                                <td class="label">Section</td>
                                <td class="sep">:</td>
                                <td>{{ $single->section->section_name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Group</td>
                                <td class="sep">:</td>
                                <td>{{ $single->group?->group_name }}</td>
                                <td class="label">Session</td>
                                <td class="sep">:</td>
                                <td>{{ $single->session->session }}</td>
                            </tr>
                            <tr>
                                <td class="label">Exam Name</td>
                                <td class="sep">:</td>
                                <td colspan="4"><strong>{{ $examName->name ?? '' }}</strong> &nbsp;
                                    (Exam Start Time: 10:00 AM)</td>
                            </tr>
                        </table>

                        <div class="section-label">Exam Routine</div>
                        <table class="routine-table" style="font-size: {{ $routineFontSize }}px;">
                            <thead>
                                <tr>
                                    <th style="width: 20%; padding: {{ $routineCellPadding }}px 6px;">Date</th>
                                    <th style="padding: {{ $routineCellPadding }}px 6px;">Subject</th>
                                    <th style="width: 20%; padding: {{ $routineCellPadding }}px 6px;">Subject Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($routineRows as $row)
                                    <tr>
                                        <td style="padding: {{ $routineCellPadding }}px 6px;">{{ $row['date'] }}</td>
                                        <td style="padding: {{ $routineCellPadding }}px 6px;">{{ $row['subject_name'] }}</td>
                                        <td style="padding: {{ $routineCellPadding }}px 6px;">{{ $row['subject_code'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="instructions-box">
                            <strong>Instructions:</strong>
                            <ol>
                                <li>Examinee must enter the exam hall at least 15 minutes before the exam starts.</li>
                                <li>No extra papers will be allowed except the admit and registration card.</li>
                                <li>Examinee must carry all necessary essentials for the exam, e.g. pen, pencil.</li>
                                <li>Authority reserves all rights to expel the examinee due to unfair means in the exam.</li>
                            </ol>
                        </div>

                        <div class="signature-row">
                            <div class="signature-cell">
                                <div class="sign-line"></div>
                                Class Teacher
                            </div>
                            <div class="signature-cell">
                                <div class="sign-line"></div>
                                Controller of Examinations
                            </div>
                            <div class="signature-cell">
                                <div class="sign-line"></div>
                                Principal
                            </div>
                        </div>

                    </div>
                    <!-- Single Admit-Card End -->
                </div>
            @endforeach
        </div>
    @endforeach

</body>

</html>
