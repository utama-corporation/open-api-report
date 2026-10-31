<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            margin: 14mm 8mm 14mm 8mm;
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
            margin: 0 0 16px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }

        .meta-layout,
        .meta-block {
            width: 100%;
            table-layout: fixed;
        }

        .meta-layout {
            margin: 0 0 10px;
        }

        .meta-layout td,
        .meta-block td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .meta-block td {
            padding-bottom: 2px;
        }

        .meta-label {
            width: 94px;
            white-space: nowrap;
        }

        .meta-separator {
            width: 8px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        .report-table {
            border: 1px solid #000;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
        }

        th {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            background: #fff;
        }

        .headers-row th {
            border-top: 0;
            border-bottom: 1px solid #000;
        }

        .row-odd td {
            background: #c9d1df;
        }

        .row-even td {
            background: #eef2f8;
        }

        .data-row td.data-cell {
            border-top: none !important;
            border-bottom: none !important;
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }

        .data-row.group-separator td.data-cell {
            border-top: 1px solid #000 !important;
        }

        .data-row.before-total td.data-cell,
        .data-row.row-last td.data-cell {
            border-bottom: 1px solid #000 !important;
        }

        .number,
        .center {
            text-align: center;
            white-space: nowrap;
            font-family: "Calibri", "DejaVu Sans", sans-serif;
        }

        .tebal-cell {
            text-align: center;
            vertical-align: middle;
        }

        .total-row td {
            border: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
        }

        .returned-materials-table {
            width: 75%;
            margin: 6px 0 0 3%;
        }

        .note-layout,
        .signature-layout {
            width: 100%;
            table-layout: fixed;
        }

        .note-layout {
            margin-top: 8px;
        }

        .note-layout td,
        .signature-layout td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .signature-layout {
            margin-top: 28mm;
        }

        .signature-cell {
            text-align: center;
            white-space: nowrap;
        }

        .signature-line {
            white-space: nowrap;
            font-family: "Calibri", "DejaVu Sans", sans-serif;
        }
    </style>
</head>

<body>
    @php
        $data = is_array($reportData ?? null) ? $reportData : [];
        $header = is_array($data['header'] ?? null) ? $data['header'] : [];
        $lengths = is_array($data['length_columns'] ?? null) ? $data['length_columns'] : [];
        $tebalGroups = is_array($data['tebal_groups'] ?? null) ? $data['tebal_groups'] : [];
        $summary = is_array($data['summary'] ?? null) ? $data['summary'] : [];
        $returnedMaterials = is_array($data['returned_materials'] ?? null) ? $data['returned_materials'] : [];
        $returnedRows = is_array($returnedMaterials['rows'] ?? null) ? $returnedMaterials['rows'] : [];
        $returnedSummary = is_array($returnedMaterials['summary'] ?? null) ? $returnedMaterials['summary'] : [];
        $tonSuratJalan = $header['ton_surat_jalan'] ?? null;
        $formatDate = static function ($value): string {
            if ($value === null || trim((string) $value) === '') {
                return '-';
            }

            try {
                return \Carbon\Carbon::parse((string) $value)->locale('id')->isoFormat('DD-MMM-YY');
            } catch (\Throwable) {
                return (string) $value;
            }
        };
        $formatNumber = static fn($value, int $decimals = 0): string => number_format((float) $value, $decimals, '.', ',');
        $formatSize = static function ($value): string {
            $formatted = number_format((float) $value, 4, '.', '');

            return rtrim(rtrim($formatted, '0'), '.');
        };
        $displayValue = static function ($value): string {
            $text = trim((string) ($value ?? ''));

            return $text !== '' ? $text : '-';
        };

        $lengthMap = [];
        foreach ([2, 2.5, 3, 3.5, 4] as $standardLength) {
            $key = $formatSize($standardLength);
            $lengthMap[$key] = ['key' => $key, 'label' => $key, 'raw_panjang' => (float) $standardLength];
        }
        foreach ($lengths as $column) {
            $rawLength = (float) ($column['raw_panjang'] ?? $column['label'] ?? 0);
            $key = (string) ($column['key'] ?? $formatSize($rawLength));
            $lengthMap[$key] = [
                'key' => $key,
                'label' => (string) ($column['label'] ?? $key),
                'raw_panjang' => $rawLength,
            ];
        }
        $columns = array_values($lengthMap);
        usort($columns, static fn(array $left, array $right): int => $left['raw_panjang'] <=> $right['raw_panjang']);

        $columnCount = max(1, count($columns));
        $dimensionWidth = 10;
        $uomWidth = 7;
        $pcsWidth = 13;
        $totalWidth = 13;
        $lengthWidth = (100 - ($dimensionWidth * 2) - $uomWidth - $pcsWidth - $totalWidth) / $columnCount;
        $totalDataRows = array_sum(array_map(static fn(array $group): int => count($group['rows'] ?? []), $tebalGroups));
        $lineCounter = 0;
    @endphp

    <h1 class="report-title">Laporan Penerimaan ST - Pembelian (Ton)</h1>

    <table class="meta-layout">
        <tbody>
            <tr>
                <td style="width: 33%; padding-right: 8px;">
                    <table class="meta-block">
                        <tbody>
                            <tr>
                                <td class="meta-label">No. Penerimaan ST</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['no_penerimaan_st'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Tgl Laporan</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $formatDate($header['tgl_laporan'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Tgl Masuk</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $formatDate($header['tgl_masuk'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Surat Tanah</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['surat_tanah'] ?? null) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 34%; padding: 0 8px;">
                    <table class="meta-block">
                        <tbody>
                            <tr>
                                <td class="meta-label">Supplier</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['supplier'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">No. Truk</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['no_truk'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">No. Plat</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['no_plat'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Kondisi</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['kondisi'] ?? null) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 33%; padding-left: 8px;">
                    <table class="meta-block">
                        <tbody>
                            <tr>
                                <td class="meta-label">No. Suket</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['no_suket'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Jenis Kayu</td>
                                <td class="meta-separator">:</td>
                                <td>{{ $displayValue($header['jenis_kayu'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Ton Surat Jalan</td>
                                <td class="meta-separator">:</td>
                                <td class="number">
                                    {{ $tonSuratJalan === null ? '-' : $formatNumber($tonSuratJalan, 4) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="report-table">
        <colgroup>
            <col style="width: {{ $dimensionWidth }}%;">
            <col style="width: {{ $dimensionWidth }}%;">
            <col style="width: {{ $uomWidth }}%;">
            @foreach ($columns as $column)
                <col style="width: {{ number_format($lengthWidth, 4, '.', '') }}%;">
            @endforeach
            <col style="width: {{ $pcsWidth }}%;">
            <col style="width: {{ $totalWidth }}%;">
        </colgroup>
        <thead>
            <tr class="headers-row">
                <th rowspan="2">Tebal</th>
                <th rowspan="2">Lebar</th>
                <th rowspan="2">@</th>
                <th colspan="{{ count($columns) }}">Panjang</th>
                <th rowspan="2">Jmlh Pcs</th>
                <th rowspan="2">Total</th>
            </tr>
            <tr class="headers-row">
                @foreach ($columns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($tebalGroups as $tebalGroupIndex => $tebalGroup)
                @php $tebalRows = is_array($tebalGroup['rows'] ?? null) ? $tebalGroup['rows'] : []; @endphp
                @foreach ($tebalRows as $detailRow)
                    @php
                        $lineCounter++;
                        $isLastDataRow = $lineCounter === $totalDataRows;
                    @endphp
                    <tr
                        class="data-row {{ $lineCounter % 2 === 1 ? 'row-odd' : 'row-even' }} {{ $tebalGroupIndex > 0 && $loop->first ? 'group-separator' : '' }} {{ $isLastDataRow ? 'before-total' : '' }}">
                        @if ($loop->first)
                            <td class="data-cell tebal-cell" rowspan="{{ count($tebalRows) }}">
                                {{ $formatSize($tebalGroup['tebal'] ?? 0) }}</td>
                        @endif
                        <td class="data-cell center">{{ $formatSize($detailRow['lebar'] ?? 0) }}</td>
                        <td class="data-cell center">{{ $displayValue($detailRow['uom'] ?? null) }}</td>
                        @foreach ($columns as $column)
                            @php $pcs = (int) ($detailRow['cells'][$column['key']] ?? 0); @endphp
                            <td class="data-cell number">{{ $pcs > 0 ? $formatNumber($pcs) : '' }}</td>
                        @endforeach
                        <td class="data-cell number">{{ $formatNumber($detailRow['total_pcs'] ?? 0) }}</td>
                        <td class="data-cell number">{{ $formatNumber($detailRow['total_ton'] ?? 0, 4) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr class="data-row row-odd row-last">
                    <td class="data-cell center" colspan="{{ count($columns) + 5 }}">Tidak ada data.</td>
                </tr>
            @endforelse

            @if ($tebalGroups !== [])
                <tr class="total-row">
                    <td colspan="3" class="center">Total</td>
                    @foreach ($columns as $column)
                        @php $pcs = (int) ($summary['totals_by_length'][$column['key']] ?? 0); @endphp
                        <td class="number">{{ $pcs > 0 ? $formatNumber($pcs) : '' }}</td>
                    @endforeach
                    <td class="number">{{ $formatNumber($summary['total_pcs'] ?? 0) }}</td>
                    <td class="number">{{ $formatNumber($summary['total_ton'] ?? 0, 4) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="note-layout">
        <tbody>
            <tr>
                <td>Note : {!! nl2br(e($displayValue($header['note'] ?? null))) !!}</td>
            </tr>
        </tbody>
    </table>

    @if ($returnedRows !== [])
        <table class="report-table returned-materials-table">
            <colgroup>
                <col style="width: 6%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
                <col style="width: 11%;">
                <col style="width: 10%;">
                <col style="width: 22%;">
                <col style="width: 21%;">
                <col style="width: 12%;">
            </colgroup>
            <thead>
                <tr class="headers-row">
                    <th>No</th>
                    <th>Tebal</th>
                    <th>Lebar</th>
                    <th>Panjang</th>
                    <th>Pcs</th>
                    <th>UOMTblLebar</th>
                    <th>UOMPanjang</th>
                    <th>Ton</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($returnedRows as $returnedRow)
                    <tr class="data-row returned-material-row {{ $loop->odd ? 'row-odd' : 'row-even' }}{{ $loop->last ? ' before-total' : '' }}">
                        <td class="data-cell number">{{ $formatNumber($returnedRow['no'] ?? 0) }}</td>
                        <td class="data-cell center">{{ ($returnedRow['tebal'] ?? null) === null ? '-' : $formatSize($returnedRow['tebal']) }}</td>
                        <td class="data-cell center">{{ ($returnedRow['lebar'] ?? null) === null ? '-' : $formatSize($returnedRow['lebar']) }}</td>
                        <td class="data-cell center">{{ ($returnedRow['panjang'] ?? null) === null ? '-' : $formatSize($returnedRow['panjang']) }}</td>
                        <td class="data-cell number">{{ $formatNumber($returnedRow['pcs'] ?? 0) }}</td>
                        <td class="data-cell center">{{ $displayValue($returnedRow['uom_tbl_lebar'] ?? null) }}</td>
                        <td class="data-cell center">{{ $displayValue($returnedRow['uom_panjang'] ?? null) }}</td>
                        <td class="data-cell number">{{ ($returnedRow['ton'] ?? null) === null ? '-' : $formatNumber($returnedRow['ton'], 4) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row returned-materials-total">
                    <td colspan="4" class="center">Total</td>
                    <td class="number">{{ $formatNumber($returnedSummary['total_pcs'] ?? 0) }}</td>
                    <td colspan="2"></td>
                    <td class="number">{{ $formatNumber($returnedSummary['total_ton'] ?? 0, 4) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <table class="signature-layout">
        <tbody>
            <tr>
                @foreach (['Dibuat', 'Diperiksa', 'Diketahui'] as $approvalLabel)
                    <td class="signature-cell">{{ $approvalLabel }} : <span class="signature-line">__________________</span>
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    @include('reports.partials.pdf-footer-table')
</body>

</html>
