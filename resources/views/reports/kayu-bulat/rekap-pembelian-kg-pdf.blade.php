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

        .container-fluid {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            table-layout: fixed;
        }

        .report-table {
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 8px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 1px 2px;
            vertical-align: middle;
        }

        th {
            text-align: center;
            font-weight: 700;
            background: #fff;
        }

        td.center {
            text-align: center;
        }

        td.number {
            text-align: right;
            font-family: "Calibri", "DejaVu Sans", sans-serif;
            white-space: nowrap;
        }

        .headers-row th {
            font-weight: bold;
            font-size: 9px;
            border-top: 0;
            border-bottom: 1px solid #000;
        }

        .subheaders-row th {
            font-weight: bold;
            font-size: 8px;
            background: #eef2f8;
        }

        .col-year {
            width: 4.5%;
        }

        .col-year,
        .col-total {
            font-weight: bold;
        }

        .group-start {
            border-left: 2px solid #000 !important;
        }

        .totals-row td {
            font-weight: bold;
            font-size: 11px;
        }

        .row-odd td {
            background: #c9d1df;
        }

        .row-even td {
            background: #eef2f8;
        }

        .report-table tbody tr.data-row td.data-cell {
            border-top: 0 !important;
            border-bottom: 0 !important;
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }

        .report-table tbody tr.data-row td.data-cell.group-start {
            border-left: 2px solid #000 !important;
        }

        .report-table tbody tr.data-row+tr.data-row td.data-cell {
            border-top: 0 !important;
        }

        .chart-block {
            page-break-inside: avoid;
            margin: 10px 0 4px 0;
        }

        .chart-title {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin: 0 0 2px 0;
        }

        .chart-container {
            text-align: center;
            margin: 0 auto;
        }

        tfoot {
            display: table-footer-group;
        }
    </style>
</head>

<body>
    @php
        $data = is_array($reportData ?? null) ? $reportData : [];
        $yearRows = is_array($data['year_rows'] ?? null) ? $data['year_rows'] : [];
        $monthLabels = is_array($data['month_labels'] ?? null) ? $data['month_labels'] : [];
        $summary = is_array($data['summary'] ?? null) ? $data['summary'] : [];

        $fmtBerat = static fn (?float $value): string => $value === null || abs($value) < 0.0000001
            ? ''
            : number_format($value, 0, '.', ',');
        $fmtHarga = static fn (?float $value): string => $value === null || abs($value) < 0.0000001
            ? ''
            : number_format($value, 0, '.', ',');

        $startYear = (int) ($summary['start_year'] ?? 0);
        $endYear = (int) ($summary['end_year'] ?? 0);
        $dataCellStyle =
            'border-top:none;border-bottom:none;border-left:1px solid #000;border-right:1px solid #000;';
        $dataCellGroupStyle =
            'border-top:none;border-bottom:none;border-left:2px solid #000;border-right:1px solid #000;';
        $dataCellMonthStyle =
            'border-top:none;border-bottom:none;border-left:1px solid #000;border-right:1px solid #000;';

        $generatedByName = $generatedBy?->name ?? 'sistem';
        $generatedAtText = $generatedAt->copy()->locale('id')->translatedFormat('d-M-y H:i');

        // Charts are drawn as inline SVG because mPDF cannot execute JavaScript charts.
        $yearColors = [];
        $palette = ['#1f4e79', '#2e75b6', '#548235', '#bf8f00', '#c55a11', '#9c111d'];
        foreach ($yearRows as $index => $yearRow) {
            $yearColors[(int) ($yearRow['tahun'] ?? 0)] = $palette[$index % count($palette)];
        }

        $chartPoints = [];
        foreach ($yearRows as $yearRow) {
            $year = (int) ($yearRow['tahun'] ?? 0);
            foreach ($monthLabels as $month => $label) {
                $chartPoints[] = [
                    'year' => $year,
                    'month' => (int) $month,
                    'label' => $label . ' ' . substr((string) $year, -2),
                    'berat' => (float) ($yearRow['months'][$month] ?? 0.0),
                    'harga' => $yearRow['harga_months'][$month] ?? null,
                    'show_label' => ((int) $month - 1) % 3 === 0,
                ];
            }
        }

        $chartViewWidth = 1040;
        $chartMarginLeft = 52;
        $chartMarginRight = 14;
        $chartMarginTop = 26;
        $chartBottom = 184;
        $chartTop = $chartMarginTop;
        $chartPlotLeft = $chartMarginLeft;
        $chartPlotRight = $chartViewWidth - $chartMarginRight;
        $chartPlotWidth = $chartPlotRight - $chartPlotLeft;
        $chartPlotHeight = $chartBottom - $chartTop;
        $chartSlot = $chartPoints === [] ? 0 : $chartPlotWidth / count($chartPoints);
        $chartBarWidth = max(3.5, $chartSlot * 0.62);
        $chartBarOffset = max(0.0, ($chartSlot - $chartBarWidth) / 2);

        $chartScale = static function (float $maxValue, int $tickCount = 5): array {
            if ($maxValue <= 0) {
                $maxValue = 1.0;
            }

            $rough = $maxValue / max(1, $tickCount);
            $magnitude = 10 ** floor(log10($rough));
            $normalized = $rough / $magnitude;
            $multiplier = $normalized <= 1 ? 1 : ($normalized <= 2 ? 2 : ($normalized <= 2.5 ? 2.5 : ($normalized <= 5 ? 5 : 10)));
            $step = $multiplier * $magnitude;
            $max = ceil($maxValue / $step) * $step;

            return ['step' => $step, 'max' => $max, 'ticks' => (int) round($max / $step)];
        };

        $formatAxis = static function (float $value): string {
            if (abs($value - round($value)) < 0.0000001) {
                return number_format($value, 0, '.', ',');
            }

            return rtrim(rtrim(number_format($value, 1, '.', ','), '0'), '.');
        };

        $beratScale = $chartScale((float) max(array_column($chartPoints, 'berat') ?: [0.0]));
        $hargaScale = $chartScale((float) max(array_map(
            static fn (array $point): float => (float) ($point['harga'] ?? 0.0),
            $chartPoints,
        ) ?: [0.0]));

        $legendItemWidth = 64;
        $legendStartX = $chartPlotRight - (count($yearRows) * $legendItemWidth);
    @endphp

    <h1 class="report-title">Laporan Rekap Pembelian Kayu Bulat - Timbang KG (Ton)</h1>
    <p class="report-subtitle">
        @if ($startYear > 0 && $endYear > 0)
            Periode {{ $startYear }} s/d {{ $endYear }}
        @endif
    </p>

    <div class="container-fluid">
        <div class="table-responsive">
            <table class="table table-striped report-table">
                <thead>
                    <tr class="headers-row">
                        <th rowspan="2" class="col-year">Tahun</th>
                        @foreach ($monthLabels as $label)
                            <th colspan="2" class="month-header group-start">{{ $label }}</th>
                        @endforeach
                        <th colspan="2" class="group-start">Total</th>
                        <th colspan="2" class="group-start">Average</th>
                        <th colspan="2" class="group-start">Min</th>
                        <th colspan="2" class="group-start">Max</th>
                    </tr>
                    <tr class="subheaders-row">
                        @foreach ($monthLabels as $label)
                            <th class="group-start">Harga</th>
                            <th>Berat</th>
                        @endforeach
                        <th class="group-start">Harga</th>
                        <th>Berat</th>
                        <th class="group-start">Harga</th>
                        <th>Berat</th>
                        <th class="group-start">Harga</th>
                        <th>Berat</th>
                        <th class="group-start">Harga</th>
                        <th>Berat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($yearRows as $row)
                        <tr class="data-row {{ $loop->odd ? 'row-odd' : 'row-even' }}">
                            <td class="center data-cell col-year" style="{{ $dataCellStyle }}">{{ $row['tahun'] ?? '' }}
                            </td>
                            @foreach ($monthLabels as $month => $label)
                                <td class="number data-cell group-start" style="{{ $dataCellGroupStyle }}">
                                    {{ $fmtHarga($row['harga_months'][$month] ?? null) }}
                                </td>
                                <td class="number data-cell" style="{{ $dataCellMonthStyle }}">
                                    {{ $fmtBerat((float) ($row['months'][$month] ?? 0.0)) }}
                                </td>
                            @endforeach
                            <td class="number data-cell group-start col-total" style="{{ $dataCellGroupStyle }}">
                                {{ $fmtHarga($row['total_harga'] ?? null) }}
                            </td>
                            <td class="number data-cell col-total" style="{{ $dataCellMonthStyle }}">
                                {{ $fmtBerat((float) ($row['total'] ?? 0.0)) }}
                            </td>
                            <td class="number data-cell group-start" style="{{ $dataCellGroupStyle }}">
                                {{ $fmtHarga($row['harga_average'] ?? null) }}
                            </td>
                            <td class="number data-cell" style="{{ $dataCellMonthStyle }}">
                                {{ $fmtBerat($row['berat_average'] ?? null) }}
                            </td>
                            <td class="number data-cell group-start" style="{{ $dataCellGroupStyle }}">
                                {{ $fmtHarga($row['harga_min'] ?? null) }}
                            </td>
                            <td class="number data-cell" style="{{ $dataCellMonthStyle }}">
                                {{ $fmtBerat($row['berat_min'] ?? null) }}
                            </td>
                            <td class="number data-cell group-start" style="{{ $dataCellGroupStyle }}">
                                {{ $fmtHarga($row['harga_max'] ?? null) }}
                            </td>
                            <td class="number data-cell" style="{{ $dataCellMonthStyle }}">
                                {{ $fmtBerat($row['berat_max'] ?? null) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 1 + (count($monthLabels) * 2) + 8 }}" class="center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($chartPoints !== [])
            <div class="chart-block">
                <div class="chart-title">Grafik Berat Pembelian Kayu Bulat per Bulan (Ton)</div>
                <div class="chart-container">
                    <svg width="100%" viewBox="0 0 {{ $chartViewWidth }} 210" xmlns="http://www.w3.org/2000/svg"
                        style="font-family: 'Noto Serif', serif; font-size: 8px;">
                        @for ($i = 0; $i <= $beratScale['ticks']; $i++)
                            @php
                                $tickValue = $beratScale['step'] * $i;
                                $tickY = $chartBottom - ($tickValue / $beratScale['max']) * $chartPlotHeight;
                                $isZeroTick = $i === 0;
                            @endphp
                            <line x1="{{ $chartPlotLeft }}" y1="{{ $tickY }}"
                                x2="{{ $chartPlotRight }}" y2="{{ $tickY }}" stroke="#000"
                                stroke-width="{{ $isZeroTick ? 1 : 0.4 }}"
                                stroke-dasharray="{{ $isZeroTick ? 'none' : '3,3' }}" />
                            <text x="{{ $chartPlotLeft - 6 }}" y="{{ $tickY + 3 }}"
                                text-anchor="end" font-size="8">{{ $formatAxis($tickValue) }}</text>
                        @endfor

                        <line x1="{{ $chartPlotLeft }}" y1="{{ $chartTop }}" x2="{{ $chartPlotLeft }}"
                            y2="{{ $chartBottom }}" stroke="#000" stroke-width="1" />
                        <line x1="{{ $chartPlotLeft }}" y1="{{ $chartBottom }}" x2="{{ $chartPlotRight }}"
                            y2="{{ $chartBottom }}" stroke="#000" stroke-width="1" />
                        <text x="12" y="12" font-size="8" font-weight="bold">Ton</text>

                        @foreach ($chartPoints as $index => $point)
                            @php
                                $barHeight = $beratScale['max'] > 0
                                    ? ($point['berat'] / $beratScale['max']) * $chartPlotHeight
                                    : 0.0;
                                $barX = $chartPlotLeft + ($index * $chartSlot) + $chartBarOffset;
                                $barY = $chartBottom - $barHeight;
                            @endphp
                            <rect x="{{ $barX }}" y="{{ $barY }}" width="{{ $chartBarWidth }}"
                                height="{{ max($barHeight, 0.4) }}"
                                fill="{{ $yearColors[$point['year']] ?? '#4a6fa5' }}" stroke="#000"
                                stroke-width="0.25" />
                            @if ($point['show_label'])
                                <text x="{{ $chartPlotLeft + ($index * $chartSlot) + ($chartSlot / 2) }}"
                                    y="{{ $chartBottom + 12 }}" text-anchor="middle"
                                    font-size="7.5">{{ $point['label'] }}</text>
                            @endif
                        @endforeach

                        @foreach ($yearRows as $index => $yearRow)
                            @php $legendX = $legendStartX + ($index * $legendItemWidth); @endphp
                            <rect x="{{ $legendX }}" y="5" width="8" height="8"
                                fill="{{ $yearColors[(int) ($yearRow['tahun'] ?? 0)] ?? '#4a6fa5' }}"
                                stroke="#000" stroke-width="0.3" />
                            <text x="{{ $legendX + 12 }}" y="12"
                                font-size="8">{{ $yearRow['tahun'] ?? '' }}</text>
                        @endforeach
                    </svg>
                </div>
            </div>

            <div class="chart-block">
                <div class="chart-title">Grafik Harga Pembelian Kayu Bulat per Bulan</div>
                <div class="chart-container">
                    <svg width="100%" viewBox="0 0 {{ $chartViewWidth }} 210" xmlns="http://www.w3.org/2000/svg"
                        style="font-family: 'Noto Serif', serif; font-size: 8px;">
                        @for ($i = 0; $i <= $hargaScale['ticks']; $i++)
                            @php
                                $tickValue = $hargaScale['step'] * $i;
                                $tickY = $chartBottom - ($tickValue / $hargaScale['max']) * $chartPlotHeight;
                                $isZeroTick = $i === 0;
                            @endphp
                            <line x1="{{ $chartPlotLeft }}" y1="{{ $tickY }}"
                                x2="{{ $chartPlotRight }}" y2="{{ $tickY }}" stroke="#000"
                                stroke-width="{{ $isZeroTick ? 1 : 0.4 }}"
                                stroke-dasharray="{{ $isZeroTick ? 'none' : '3,3' }}" />
                            <text x="{{ $chartPlotLeft - 6 }}" y="{{ $tickY + 3 }}"
                                text-anchor="end" font-size="8">{{ $formatAxis($tickValue) }}</text>
                        @endfor

                        <line x1="{{ $chartPlotLeft }}" y1="{{ $chartTop }}" x2="{{ $chartPlotLeft }}"
                            y2="{{ $chartBottom }}" stroke="#000" stroke-width="1" />
                        <line x1="{{ $chartPlotLeft }}" y1="{{ $chartBottom }}" x2="{{ $chartPlotRight }}"
                            y2="{{ $chartBottom }}" stroke="#000" stroke-width="1" />
                        <text x="12" y="12" font-size="8" font-weight="bold">Harga</text>

                        @php $previousPoint = null; @endphp
                        @foreach ($chartPoints as $index => $point)
                            @php
                                $pointX = $chartPlotLeft + ($index * $chartSlot) + ($chartSlot / 2);
                                $pointHarga = $point['harga'];
                                $pointY = $pointHarga === null
                                    ? null
                                    : $chartBottom - (($pointHarga / $hargaScale['max']) * $chartPlotHeight);
                            @endphp
                            @if ($pointHarga !== null && $previousPoint !== null)
                                <line x1="{{ $previousPoint['x'] }}" y1="{{ $previousPoint['y'] }}"
                                    x2="{{ $pointX }}" y2="{{ $pointY }}" stroke="#9c111d"
                                    stroke-width="1.2" />
                            @endif
                            @if ($pointHarga !== null)
                                <rect x="{{ $pointX - 1.6 }}" y="{{ $pointY - 1.6 }}" width="3.2"
                                    height="3.2" fill="#9c111d" />
                            @endif
                            @if ($point['show_label'])
                                <text x="{{ $chartPlotLeft + ($index * $chartSlot) + ($chartSlot / 2) }}"
                                    y="{{ $chartBottom + 12 }}" text-anchor="middle"
                                    font-size="7.5">{{ $point['label'] }}</text>
                            @endif
                            @php
                                $previousPoint = $pointHarga === null
                                    ? null
                                    : ['x' => $pointX, 'y' => $pointY];
                            @endphp
                        @endforeach
                    </svg>
                </div>
            </div>
        @endif
    </div>

    @include('reports.partials.pdf-footer-table')
</body>

</html>
