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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            page-break-inside: auto;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: middle;
            word-break: break-word;
            text-align: center;
        }

        th {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        td.center {
            text-align: center;
        }

        td.number-right {
            text-align: right;
            font-size: 9px;
            font-family: "Calibri", "DejaVu Sans", sans-serif;
            white-space: nowrap;
        }

        .row-odd td {
            background: #c9d1df;
        }

        .row-even td {
            background: #eef2f8;
        }

        .headers-row th {
            font-weight: bold;
            font-size: 11px;
            border-top: 0;
            border-bottom: 1px solid #000;
        }

        .totals-row td {
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #000;
        }

        .totals-row td.number-right {
            font-size: 9px;
        }

        .report-table {
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .report-table tbody tr.data-row td.data-cell {
            border-top: 0 !important;
            border-bottom: 0 !important;
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }

        .table-end-line td {
            border-top: 1px solid #000 !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            border-left: 0 !important;
            padding: 0 !important;
            height: 0 !important;
            line-height: 0 !important;
            background: #fff !important;
        }
    </style>
</head>

<body>
    @php
        $rowsData =
            isset($rows) && is_iterable($rows) ? (is_array($rows) ? $rows : collect($rows)->values()->all()) : [];
        $generatedByName = $generatedBy?->name ?? 'sistem';
        $generatedAtText = $generatedAt->copy()->locale('id')->translatedFormat('d-M-y H:i');

        $normalize = static fn(string $name): string => preg_replace('/[^a-z0-9]/', '', strtolower($name)) ?? '';

        $toFloat = static function ($value): ?float {
            if (is_numeric($value)) {
                return (float) $value;
            }

            if (!is_string($value)) {
                return null;
            }

            $normalized = trim($value);
            if ($normalized === '') {
                return null;
            }

            $normalized = str_replace(' ', '', $normalized);
            if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
                if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                    $normalized = str_replace('.', '', $normalized);
                    $normalized = str_replace(',', '.', $normalized);
                } else {
                    $normalized = str_replace(',', '', $normalized);
                }
            } elseif (str_contains($normalized, ',')) {
                $normalized = str_replace(',', '.', $normalized);
            }

            return is_numeric($normalized) ? (float) $normalized : null;
        };

        $findColumn = static function (array $columns, array $candidates) use ($normalize): ?string {
            $candidateSet = [];
            foreach ($candidates as $candidate) {
                $candidateSet[$normalize($candidate)] = true;
            }

            foreach ($columns as $column) {
                if (isset($candidateSet[$normalize((string) $column)])) {
                    return (string) $column;
                }
            }

            return null;
        };

        $formatMonthKey = static function ($value, $yearValue = null) use ($normalize, $endDate): ?string {
            $resolvedYear = null;

            if ($yearValue !== null && $yearValue !== '') {
                $yearRaw = trim((string) $yearValue);
                if (preg_match('/^\d{4}$/', $yearRaw) === 1) {
                    $resolvedYear = $yearRaw;
                }
            }

            if ($value === null || $value === '') {
                return null;
            }

            if (is_numeric($value)) {
                $monthNum = (int) $value;
                if ($monthNum >= 1 && $monthNum <= 12) {
                    $year = $resolvedYear ?? \Carbon\Carbon::parse((string) $endDate)->format('Y');

                    return sprintf('%s-%02d', $year, $monthNum);
                }
            }

            $raw = trim((string) $value);
            if (preg_match('/^\d{1,2}$/', $raw) === 1) {
                $monthNum = (int) $raw;
                if ($monthNum >= 1 && $monthNum <= 12) {
                    $year = $resolvedYear ?? \Carbon\Carbon::parse((string) $endDate)->format('Y');

                    return sprintf('%s-%02d', $year, $monthNum);
                }
            }

            $monthAliases = [
                'jan' => 1,
                'januari' => 1,
                'feb' => 2,
                'februari' => 2,
                'mar' => 3,
                'maret' => 3,
                'apr' => 4,
                'april' => 4,
                'mei' => 5,
                'may' => 5,
                'jun' => 6,
                'juni' => 6,
                'jul' => 7,
                'juli' => 7,
                'agu' => 8,
                'agustus' => 8,
                'aug' => 8,
                'sep' => 9,
                'sept' => 9,
                'september' => 9,
                'okt' => 10,
                'oct' => 10,
                'oktober' => 10,
                'nov' => 11,
                'november' => 11,
                'des' => 12,
                'dec' => 12,
                'desember' => 12,
            ];
            $normalized = $normalize($raw);
            if (isset($monthAliases[$normalized])) {
                $year = $resolvedYear ?? \Carbon\Carbon::parse((string) $endDate)->format('Y');

                return sprintf('%s-%02d', $year, $monthAliases[$normalized]);
            }

            try {
                return \Carbon\Carbon::parse((string) $value)->format('Y-m');
            } catch (\Throwable $exception) {
                return null;
            }
        };

        $formatNumber = static function (?float $value): string {
            if ($value === null || abs($value) < 0.0000001) {
                return '';
            }

            // Tanpa desimal, dibulatkan (1234.56 -> 1,235).
            return number_format($value, 0, '.', ',');
        };

        $columns = array_keys($rowsData[0] ?? []);
        $supplierColumn = $findColumn($columns, ['NmSupplier', 'NamaSupplier', 'Supplier', 'Nama Supplier']);
        $dateColumn = $findColumn($columns, ['DateCreate', 'Tanggal', 'Tgl', 'Date', 'Bulan']);
        $yearColumn = $findColumn($columns, ['Tahun', 'Year']);

        // Qty: TonBerat (didukung alias Jumlah/Qty/Ton dsb).
        $valueColumn = $findColumn($columns, ['TonBerat', 'KBTon', 'Tonase', 'Ton', 'Berat', 'TotalTon', 'Total', 'Qty', 'Jumlah']);
        if ($valueColumn === null) {
            foreach ($columns as $column) {
                $key = $normalize((string) $column);
                if (
                    in_array(
                        $key,
                        [
                            'datecreate',
                            'tanggal',
                            'tgl',
                            'bulan',
                            'supplier',
                            'nmsupplier',
                            'namasupplier',
                            'tahun',
                            'year',
                            'ranking',
                            'rank',
                            'harga',
                            'rp',
                            'nilai',
                            'totalrp',
                            'totalharga',
                            'hargatotal',
                            'hargabeli',
                        ],
                        true,
                    )
                ) {
                    continue;
                }

                foreach ($rowsData as $row) {
                    $num = $toFloat($row[$column] ?? null);
                    if ($num !== null) {
                        $valueColumn = (string) $column;
                        break 2;
                    }
                }
            }
        }

        // Rp: kolom Harga dari SP (didukung alias Rp/Nilai dsb).
        $rpColumn = $findColumn($columns, ['Harga', 'Rp', 'Nilai', 'TotalRp', 'TotalHarga', 'HargaTotal', 'HargaBeli', 'TotalNilai', 'Amount']);
        if ($rpColumn === null) {
            foreach ($columns as $column) {
                if ($valueColumn !== null && (string) $column === (string) $valueColumn) {
                    continue;
                }
                $key = $normalize((string) $column);
                if (
                    in_array(
                        $key,
                        [
                            'datecreate',
                            'tanggal',
                            'tgl',
                            'bulan',
                            'supplier',
                            'nmsupplier',
                            'namasupplier',
                            'tahun',
                            'year',
                            'ranking',
                            'rank',
                            'tonberat',
                            'kbton',
                            'tonase',
                            'ton',
                            'berat',
                            'totalton',
                            'qty',
                            'jumlah',
                        ],
                        true,
                    )
                ) {
                    continue;
                }

                foreach ($rowsData as $row) {
                    $num = $toFloat($row[$column] ?? null);
                    if ($num !== null) {
                        $rpColumn = (string) $column;
                        break 2;
                    }
                }
            }
        }

        $canPivot = $supplierColumn !== null && $dateColumn !== null && $valueColumn !== null;

        $pivotRows = [];
        $monthKeys = [];
        $grandByMonthQty = [];
        $grandByMonthRp = [];
        $grandTotalQty = 0.0;
        $grandTotalRp = 0.0;

        if ($canPivot) {
            foreach ($rowsData as $row) {
                $supplier = trim((string) ($row[$supplierColumn] ?? ''));
                $supplier = $supplier !== '' ? $supplier : 'Tanpa Supplier';
                $monthKey = $formatMonthKey(
                    $row[$dateColumn] ?? null,
                    $yearColumn !== null ? $row[$yearColumn] ?? null : null,
                );
                if ($monthKey === null) {
                    continue;
                }

                $qty = $toFloat($row[$valueColumn] ?? null) ?? 0.0;
                $rp = $rpColumn !== null ? ($toFloat($row[$rpColumn] ?? null) ?? 0.0) : 0.0;

                if (!isset($pivotRows[$supplier])) {
                    $pivotRows[$supplier] = [
                        'supplier' => $supplier,
                        'months' => [],
                        'total_qty' => 0.0,
                        'total_rp' => 0.0,
                    ];
                }

                if (!isset($pivotRows[$supplier]['months'][$monthKey])) {
                    $pivotRows[$supplier]['months'][$monthKey] = ['qty' => 0.0, 'rp' => 0.0];
                }

                $pivotRows[$supplier]['months'][$monthKey]['qty'] += $qty;
                $pivotRows[$supplier]['months'][$monthKey]['rp'] += $rp;
                $pivotRows[$supplier]['total_qty'] += $qty;
                $pivotRows[$supplier]['total_rp'] += $rp;

                $grandByMonthQty[$monthKey] = ($grandByMonthQty[$monthKey] ?? 0.0) + $qty;
                $grandByMonthRp[$monthKey] = ($grandByMonthRp[$monthKey] ?? 0.0) + $rp;
                $grandTotalQty += $qty;
                $grandTotalRp += $rp;
                $monthKeys[$monthKey] = true;
            }
        }

        $monthHeaders = [];
        $headerYearGroups = [];

        try {
            $periodStartMonth = \Carbon\Carbon::parse((string) $startDate)->startOfMonth();
            $periodEndMonth = \Carbon\Carbon::parse((string) $endDate)->startOfMonth();

            if ($periodStartMonth->lessThanOrEqualTo($periodEndMonth)) {
                $cursorMonth = $periodStartMonth->copy();

                while ($cursorMonth->lessThanOrEqualTo($periodEndMonth)) {
                    $monthKey = $cursorMonth->format('Y-m');
                    $monthHeaders[] = $monthKey;

                    $headerYear = $cursorMonth->format('Y');
                    $headerYearGroups[$headerYear] = ($headerYearGroups[$headerYear] ?? 0) + 1;

                    $cursorMonth->addMonth();
                }
            }
        } catch (\Throwable $exception) {
            $monthHeaders = array_keys($monthKeys);
            sort($monthHeaders);
        }

        if ($monthHeaders === [] && $monthKeys !== []) {
            $monthHeaders = array_keys($monthKeys);
            sort($monthHeaders);
        }

        if ($headerYearGroups === [] && $monthHeaders !== []) {
            foreach ($monthHeaders as $monthKey) {
                try {
                    $headerYear = \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('Y');
                } catch (\Throwable $exception) {
                    $headerYear = substr($monthKey, 0, 4);
                }

                $headerYearGroups[$headerYear] = ($headerYearGroups[$headerYear] ?? 0) + 1;
            }
        }

        // Min/Max/Avg dari nilai Qty bulanan yang tampil: bulan kosong tidak ikut dihitung.
        $statsForValues = static function (array $values): array {
            if ($values === []) {
                return ['min' => null, 'max' => null, 'avg' => null];
            }
            $sum = array_sum($values);
            return [
                'min' => min($values),
                'max' => max($values),
                'avg' => $sum / count($values),
            ];
        };

        $pivotRows = array_values($pivotRows);
        usort($pivotRows, static function (array $a, array $b): int {
            $totalCompare = ((float) ($b['total_qty'] ?? 0)) <=> ((float) ($a['total_qty'] ?? 0));
            if ($totalCompare !== 0) {
                return $totalCompare;
            }

            return strcmp((string) ($a['supplier'] ?? ''), (string) ($b['supplier'] ?? ''));
        });

        foreach ($pivotRows as $index => $pivotRow) {
            $monthlyQtys = [];
            foreach ($monthHeaders as $monthKey) {
                if (! isset($pivotRow['months'][$monthKey])) {
                    continue;
                }
                $monthlyQty = (float) ($pivotRow['months'][$monthKey]['qty'] ?? 0.0);
                if (abs($monthlyQty) < 0.0000001) {
                    continue;
                }
                $monthlyQtys[] = $monthlyQty;
            }
            $pivotRows[$index]['stats'] = $statsForValues($monthlyQtys);
        }

        $monthlyGrandQtys = [];
        foreach ($monthHeaders as $monthKey) {
            if (! isset($grandByMonthQty[$monthKey])) {
                continue;
            }
            $monthlyGrandQty = (float) ($grandByMonthQty[$monthKey] ?? 0.0);
            if (abs($monthlyGrandQty) < 0.0000001) {
                continue;
            }
            $monthlyGrandQtys[] = $monthlyGrandQty;
        }
        $grandStats = $statsForValues($monthlyGrandQtys);

        // Column widths proportional: Rp wider than Qty; Total and Min/Max/Avg
        // scale with month width so every value fits on one line.
        $monthCount = count($monthHeaders);
        $noWidthPct = 3;
        $supplierWidthPct = $monthCount >= 8 ? 14 : 18;
        $remainingPct = 100 - $noWidthPct - $supplierWidthPct;
        // Weights: 1 month pair = 1, Total = 0.8, each stat = 0.55.
        $denominator = $monthCount + 0.8 + (0.55 * 3);
        $pairWidthPct = $denominator > 0 ? $remainingPct / $denominator : 0;
        $qtyWidthPct = round($pairWidthPct * 0.44, 2);
        $rpWidthPct = max(1, round($pairWidthPct - $qtyWidthPct, 2));
        $totalWidthPct = round($pairWidthPct * 0.8, 2);
        $statWidthPct = round($pairWidthPct * 0.55, 2);
    @endphp

    <h1 class="report-title">Laporan Timeline KB - Bulanan (Rambung) MGNT</h1>
    <p class="report-subtitle">
        Periode : {{ \Carbon\Carbon::parse((string) $startDate)->locale('id')->translatedFormat('d-M-y') }} s/d
        {{ \Carbon\Carbon::parse((string) $endDate)->locale('id')->translatedFormat('d-M-y') }}
    </p>

    @if ($canPivot && $monthHeaders !== [])
        <table class="report-table">
            <thead>
                <tr class="headers-row">
                    <th rowspan="3" style="width: {{ $noWidthPct }}%;">No</th>
                    <th rowspan="3" style="width: {{ $supplierWidthPct }}%; text-align: center;">Nama Supplier</th>
                    @foreach ($headerYearGroups as $year => $yearMonthCount)
                        <th colspan="{{ $yearMonthCount * 2 }}" style="width: {{ round($pairWidthPct * $yearMonthCount, 2) }}%;">{{ $year }}</th>
                    @endforeach
                    <th rowspan="3" style="width: {{ $totalWidthPct }}%;">Total</th>
                    <th rowspan="3" style="width: {{ $statWidthPct }}%;">Min</th>
                    <th rowspan="3" style="width: {{ $statWidthPct }}%;">Max</th>
                    <th rowspan="3" style="width: {{ $statWidthPct }}%;">Avg</th>
                </tr>
                <tr class="headers-row">
                    @foreach ($monthHeaders as $monthKey)
                        <th colspan="2" style="width: {{ round($pairWidthPct, 2) }}%;">
                            {{-- Append -01: createFromFormat('Y-m') inherits today's day and 30-Feb rolls over to March. --}}
                            {{ \Carbon\Carbon::parse($monthKey.'-01')->locale('id')->translatedFormat('M') }}
                        </th>
                    @endforeach
                </tr>
                <tr class="headers-row">
                    @foreach ($monthHeaders as $monthKey)
                        <th style="width: {{ $qtyWidthPct }}%;">Qty</th>
                        <th style="width: {{ $rpWidthPct }}%;">Rp</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($pivotRows as $row)
                    @php $rowNumber = $loop->iteration; @endphp
                    <tr class="data-row {{ $rowNumber % 2 === 1 ? 'row-odd' : 'row-even' }}">
                        <td class="center data-cell">{{ $rowNumber }}</td>
                        <td class="data-cell" style="text-align: left;">{{ $row['supplier'] }}</td>
                        @foreach ($monthHeaders as $monthKey)
                            <td class="number-right data-cell">{{ $formatNumber($row['months'][$monthKey]['qty'] ?? null) }}</td>
                            <td class="number-right data-cell">{{ $formatNumber($row['months'][$monthKey]['rp'] ?? null) }}</td>
                        @endforeach
                        <td class="number-right data-cell" style="font-weight: bold">
                            {{ $formatNumber($row['total_qty'] ?? null) }}
                        </td>
                        <td class="number-right data-cell">{{ $formatNumber($row['stats']['min'] ?? null) }}</td>
                        <td class="number-right data-cell">{{ $formatNumber($row['stats']['max'] ?? null) }}</td>
                        <td class="number-right data-cell">{{ $formatNumber($row['stats']['avg'] ?? null) }}</td>
                    </tr>
                @empty
                    <tr class="data-row">
                        <td colspan="{{ count($monthHeaders) * 2 + 6 }}" class="center data-cell">Tidak ada data.</td>
                    </tr>
                @endforelse
                @if ($pivotRows !== [])
                    <tr class="totals-row">
                        <td colspan="2">Grand Total :</td>
                        @foreach ($monthHeaders as $monthKey)
                            <td class="number-right">{{ $formatNumber($grandByMonthQty[$monthKey] ?? null) }}</td>
                            <td class="number-right">{{ $formatNumber($grandByMonthRp[$monthKey] ?? null) }}</td>
                        @endforeach
                        <td class="number-right">{{ $formatNumber($grandTotalQty) }}</td>
                        <td class="number-right">{{ $formatNumber($grandStats['min'] ?? null) }}</td>
                        <td class="number-right">{{ $formatNumber($grandStats['max'] ?? null) }}</td>
                        <td class="number-right">{{ $formatNumber($grandStats['avg'] ?? null) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <table class="report-table">
            <thead>
                <tr class="headers-row">
                    <th style="width: 34px;">No</th>
                    @foreach ($columns as $column)
                        <th>{{ (string) $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rowsData as $row)
                    @php $rowNumber = $loop->iteration; @endphp
                    <tr class="data-row {{ $rowNumber % 2 === 1 ? 'row-odd' : 'row-even' }}">
                        <td class="center data-cell">{{ $rowNumber }}</td>
                        @foreach ($columns as $column)
                            <td class="center data-cell">{{ (string) ($row[$column] ?? '') }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr class="data-row">
                        <td colspan="{{ count($columns) + 1 }}" class="center data-cell">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @include('reports.partials.pdf-footer-table')
</body>

</html>
