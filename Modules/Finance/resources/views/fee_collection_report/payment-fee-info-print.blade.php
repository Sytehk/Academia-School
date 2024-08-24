<!DOCTYPE html>
<html>

<head>

    <title>Payment Fee Report</title>

    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>

</head>

<body onload="window.print()">

    <h2>

        Payment Fee Report

        <br>

        {{ $from_date }} To {{ $to_date }}

    </h2>

    <table>

        <thead>

            <tr>

                <th>SL</th>

                <th>Invoice</th>

                <th>Student</th>

                <th>Roll</th>

                <th>Total Paid</th>

            </tr>

        </thead>

        <tbody>

            @php
                $total = 0;
            @endphp

            @foreach ($studentCollections as $item)
                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $item->invoice_id }}</td>

                    <td>
                        {{ $item->student?->first_name }}
                        {{ $item->student?->last_name }}
                    </td>

                    <td>
                        {{ $item->student?->studentSession?->roll }}
                    </td>

                    <td>
                        {{ format_currency($item->total_paid) }}
                    </td>

                </tr>

                @php
                    $total += $item->total_paid;
                @endphp
            @endforeach

        </tbody>

        <tfoot>

            <tr>

                <th colspan="4" style="text-align:right">
                    Total
                </th>

                <th>
                    {{ format_currency($total) }}
                </th>

            </tr>

        </tfoot>

    </table>

</body>

</html>
