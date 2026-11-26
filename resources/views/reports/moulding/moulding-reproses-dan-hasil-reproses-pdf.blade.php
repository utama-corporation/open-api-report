<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            margin: 14mm 10mm 14mm 10mm;
            footer: html_reportFooter;
        }

        body {
            margin: 0;
            font-family: "Noto Serif", serif;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
        }

        .report-title {
            margin: 0;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }

        .report-subtitle {
            margin: 2px 0 20px 0;
            text-align: center;
            font-size: 12px;
            color: #636466;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-bottom: 1px solid #000;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 10px;
            line-height: 1.15;
        }

        th {
            background: #fff;
            font-weight: bold;
            text-align: center;
        }

        tbody td {
            border-top: 0;
            border-bottom: 0;
        }

        tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }

        .row-odd td {
            background: #c9d1df;
        }

        .row-even td {
            background: #eef2f8;
        }

        .center {
            text-align: center;
        }

        .number {
            text-align: right;
            white-space: nowrap;
            font-family: "Calibri", "DejaVu Sans", sans-serif;
        }

        .empty-row td {
            text-align: center;
            font-style: italic;
            font-weight: bold;
            color: #9c111d;
            background: #c9d1df;
        }
    </style>
</head>

<body>
    @php
        $data = is_array($reportData ?? null) ? $reportData : [];
        $rows = is_array($data['rows'] ?? null) ? $data['rows'] : [];
        $startLabel = \Carbon\Carbon::parse((string) ($data['start_date'] ?? ''))->locale('id')->isoFormat('DD-MMM-YY');
        $endLabel = \Carbon\Carbon::parse((string) ($data['end_date'] ?? ''))->locale('id')->isoFormat('DD-MMM-YY');

        $fmtDate = static fn(string $value): string => $value === ''
            ? ''
            : \Carbon\Carbon::parse($value)->locale('id')->isoFormat('DD-MMM-YY');
        $fmt4 = static fn(mixed $value): string => number_format((float) $value, 4, '.', '') . ' m³';

        $generatedByName = $generatedBy?->name ?? 'sistem';
        $generatedAtText = isset($generatedAt) && method_exists($generatedAt, 'copy')
            ? $generatedAt->copy()->locale('id')->translatedFormat('d-M-y H:i')
            : now()->locale('id')->translatedFormat('d-M-y H:i');
    @endphp

    <h1 class="report-title">Laporan Proses Produksi Moulding Reproses Dan Hasil Dari Reproses</h1>
    <p class="report-subtitle">Dari {{ $startLabel }} s/d {{ $endLabel }}</p>

    <table>
        <colgroup>
            <col style="width: 16%;">
            <col style="width: 28%;">
            <col style="width: 28%;">
            <col style="width: 28%;">
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Input Reproses</th>
                <th>Output Reproses</th>
                <th>Hasil Dari Reproses</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="{{ $loop->odd ? 'row-odd' : 'row-even' }}">
                    <td class="center">{{ $fmtDate((string) ($row['tanggal'] ?? '')) }}</td>
                    <td class="number">{{ $fmt4($row['input_reproses'] ?? 0) }}</td>
                    <td class="number">{{ $fmt4($row['output_reproses'] ?? 0) }}</td>
                    <td class="number">{{ $fmt4($row['hasil_dari_reproses'] ?? 0) }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="4">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('reports.partials.pdf-reference-footer', [
        'generatedByName' => $generatedByName,
        'generatedAtText' => $generatedAtText,
    ])
</body>

</html>
