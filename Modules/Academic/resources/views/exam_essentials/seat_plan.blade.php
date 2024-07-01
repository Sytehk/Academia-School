<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Seat Plan</title>
    <style>
        body {
            height: auto;
            /* Set the height for A4 - 297mm */
            width: 210mm;
            margin: 0 auto;
            padding: 0;
            outline: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }

        th,
        td {
            border: 2px solid #ccc;
            margin: 0;
            padding: 5px;
        }

        th {
            text-align: center;
            border: 1px solid #ccc;
        }

        .left {
            text-align: left;
            width: 75%;
        }

        .right {
            text-align: right;
            width: 25%;
        }

        img {
            max-width: 100%;
            height: auto;
            padding: 0;
        }

        .cutting-line {
            border: 2px dashed black;
        }

        @page {
            page-break-after: always;
        }

        .w_50 {
            width: 50%;
        }

        .table_1 {
            border: 2px solid black;
            width: 100%;
        }

        .table_1_td_1 {
            text-align: center;
            border: none;
            margin: 0px;
            padding: 0;
            text-align: center;
            border: none;
            margin: 0px;
            padding: 0;
        }

        .table_1_td_1 h4 {
            margin: 0px;
            padding: 0;
            font-size: 16px;
        }

        .table_1_td_1 p {
            margin: 0px;
            padding: 0;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .table_1_td_2 {
            text-align: center;
            background-color: #ccc;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
        }

        .table_1_td_3 {
            border: 1px solid #ccc;
            padding: 5px;
            border: 1px solid #ccc;
            padding: 5px;
        }

        .table_1_td_4 {
            width: 30%;
            float: left;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .table_1_td_5 {
            width: 5%;
            float: left;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .table_1_td_6 {
            width: 65%;
            float: right;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .student_img {
            width: 70px;
            height: 60px;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .para1 {
            margin: 0;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <!-- Single row start -->
    <table>
        @php
            $sessionChunks = array_chunk($data->toArray(), 2);
        @endphp

        @foreach ($sessionChunks as $chunk)
            <tr>
                @foreach ($chunk as $single)
                    <td class="cutting-line w_50">
                        <table class="table_1">
                            <thead>
                                <tr>
                                    <td colspan="2" class="table_1_td_1">
                                        <h4>
                                            {{ get_option('school_name') }}
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="table_1_td_1">
                                        <p>{{ get_option('address') }}</p>
                                    </td>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td colspan="2" class="table_1_td_2">
                                        Exam Seat Plan
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td class="left" class="table_1_td_3">
                                        <div>
                                            <p class="table_1_td_4">
                                                Name
                                            </p>
                                            <p class="table_1_td_5">:
                                            </p>
                                            <p class="table_1_td_6">
                                                {{ Illuminate\Support\Str::limit($single['student']['first_name'], 20) }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="table_1_td_4">
                                                Student ID</p>
                                            <p class="table_1_td_5">:
                                            </p>
                                            <p class="table_1_td_6">
                                                {{ $single['student']['id'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="table_1_td_4">
                                                Year/Session</p>
                                            <p class="table_1_td_5">:
                                            </p>
                                            <p class="table_1_td_6">
                                                {{ $single['session']['session'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="table_1_td_4">
                                                Exam
                                                Name</p>
                                            <p class="table_1_td_5">:
                                            </p>
                                            <p class="table_1_td_6">
                                                @php
                                                    $examSchedule = DB::table('exam_schedules')->first();
                                                    if ($examSchedule) {
                                                        $examName = DB::table('exams')
                                                            ->where('id', $examSchedule->exam_id)
                                                            ->first();
                                                    }

                                                @endphp
                                                @if (isset($examSchedule))
                                                    {{ $examName->name ?? '' }}
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="table_1_td_4">
                                                Group
                                            </p>
                                            <p class="table_1_td_5">:
                                            </p>
                                            <p class="table_1_td_6">
                                                {{ optional($single['group'] ?? null)->group_name ?? '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="table_1_td_4">
                                                Section
                                            </p>
                                            <p class="table_1_td_5">:
                                            </p>
                                            <p class="table_1_td_6">
                                                {{ $single['section']['section_name'] }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="right" class="table_1_td_3">
                                        <img src="{{ asset('storage/users/' . $single['student']['user']['image']) }}"
                                            alt="" class="student_img">

                                        <p class="para_1">
                                            <strong>Roll no:</strong><br>
                                            <strong>{{ $single['roll'] }}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                @endforeach
            </tr>
        @endforeach

    </table>
    <!-- Single row end -->





</body>

</html>
