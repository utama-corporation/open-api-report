<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 14mm 10mm 14mm 10mm;
            footer: html_reportFooter;
        }

        body {
            margin: 0;
            font-family: "Noto Serif", serif;
            font-size: 10px;
            line-height: 1.15;
            color: #000;
        }

        .report-title {
            text-align: center;
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .report-subtitle {
            text-align: center;
            margin: 2px 0 20px 0;
            font-size: 12px;
            color: #636466;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: auto;
        }

        .data-table th,
        .data-table td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 1px 2px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data-table th {
            font-weight: bold;
            font-size: 10px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }

        .data-table td {
            font-size: 10px;
            border-top: none;
            border-bottom: none;
        }

        .center {
            text-align: center;
        }

        .number {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .row-odd td {
            background: #c9d1df;
        }

        .row-even td {
            background: #eef2f8;
        }

        .section-row td {
            font-weight: bold;
            font-size: 10px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 2px;
        }

        .grand-total-row td {
            font-weight: bold;
            font-size: 10px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 2px;
        }

        .empty-row td {
            text-align: center;
            font-style: italic;
            background: #c9d1df;
            font-weight: bold;
            color: #000;
            font-size: 11px;
            padding: 8px 4px;
            border-bottom: 1px solid #000;
        }

        tr {
            page-break-inside: avoid;
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
        $fmtInt = static fn(mixed $value): string => number_format((float) $value, 0, '.', ',');
        $fmt4 = static fn(mixed $value): string => number_format((float) $value, 4, '.', '');

        $sectionDefinitions = [
            'input_reproses' => [
                'title' => 'Input Reproses',
                'total_label' => 'Total Input Reproses',
            ],
            'output_reproses' => [
                'title' => 'Output Reproses',
                'total_label' => 'Total Output Reproses',
            ],
            'hasil_dari_reproses' => [
                'title' => 'Hasil Dari Reproses',
                'total_label' => 'Total Hasil Dari Reproses',
            ],
        ];

        $groupedRows = array_fill_keys(array_keys($sectionDefinitions), []);
        foreach ($rows as $row) {
            $key = (string) ($row['section_key'] ?? 'other');
            if (isset($groupedRows[$key])) {
                $groupedRows[$key][] = $row;
            }
        }

        $sectionTotals = [];
        foreach ($groupedRows as $key => $sectionRows) {
            $jmlhBatang = 0;
            $kubik = 0.0;
            foreach ($sectionRows as $row) {
                $jmlhBatang += (int) ($row['jmlh_batang'] ?? 0);
                $kubik += (float) ($row['kubik'] ?? 0.0);
            }
            $sectionTotals[$key] = [
                'jmlh_batang' => $jmlhBatang,
                'kubik' => $kubik,
            ];
        }

        $generatedByName = $generatedBy?->name ?? 'sistem';
        $generatedAtText = isset($generatedAt) && method_exists($generatedAt, 'copy')
            ? $generatedAt->copy()->locale('id')->translatedFormat('d-M-y H:i')
            : now()->locale('id')->translatedFormat('d-M-y H:i');
        $globalRow = 0;
    @endphp

    <h1 class="report-title">Laporan Proses Produksi Moulding Reproses Dan Hasil Dari Reproses</h1>
    <p class="report-subtitle">Dari {{ $startLabel }} s/d {{ $endLabel }}</p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 11%;">Tanggal</th>
                <th style="width: 13%;">No Label</th>
                <th style="width: 10%;">Tebal (mm)</th>
                <th style="width: 10%;">Lebar (mm)</th>
                <th style="width: 10%;">Panjang (ft)</th>
                <th style="width: 10%;">Jmlh Batang</th>
                <th style="width: 12%;">Kubik (m&sup3;)</th>
                <th style="width: 24%;">No. SPK Tujuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sectionDefinitions as $key => $section)
                <tr class="section-row">
                    <td colspan="8">{{ $section['title'] }}</td>
                </tr>

                @forelse ($groupedRows[$key] as $row)
                    @php $globalRow++; @endphp
                    <tr class="{{ $globalRow % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td class="nowrap center">{{ $fmtDate((string) ($row['tanggal'] ?? '')) }}</td>
                        <td class="center">{{ $row['no_label'] ?? '' }}</td>
                        <td class="number">{{ $fmtInt($row['tebal'] ?? 0) }}</td>
                        <td class="number">{{ $fmtInt($row['lebar'] ?? 0) }}</td>
                        <td class="number">{{ $fmtInt($row['panjang'] ?? 0) }}</td>
                        <td class="number">{{ $fmtInt($row['jmlh_batang'] ?? 0) }}</td>
                        <td class="number">{{ $fmt4($row['kubik'] ?? 0) }}</td>
                        <td class="center">{{ $row['no_spk_tujuan'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="8">Tidak ada data.</td>
                    </tr>
                @endforelse

                <tr class="grand-total-row">
                    <td colspan="5" class="center">{{ $section['total_label'] }}</td>
                    <td class="number">{{ $fmtInt($sectionTotals[$key]['jmlh_batang'] ?? 0) }}</td>
                    <td class="number">{{ $fmt4($sectionTotals[$key]['kubik'] ?? 0) }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @include('reports.partials.pdf-reference-footer', [
        'generatedByName' => $generatedByName,
        'generatedAtText' => $generatedAtText,
    ])
</body>

</html>