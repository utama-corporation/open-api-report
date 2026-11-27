<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap"
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

    .report-companyTitle {
        text-align: center;
        margin: 0 0 4px 0;
        font-size: 18px;
        font-weight: bold;
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

    .number-negative {
        color: #9c111d;
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

    .signature-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 28px 0 0 0;
        page-break-inside: avoid;
    }

    .signature-table td {
        width: 50%;
        text-align: center;
        vertical-align: top;
        padding: 0 2px;
        font-size: 10px;
    }

    .signature-space-row td {
        height: 20mm;
        padding: 0;
    }
    </style>
</head>

<body>
    @php
    $sections = $reportData['sections'] ?? [];
    $grandTotal = (float) ($reportData['grand_total_total'] ?? 0);
    $grandNet = (float) ($reportData['grand_total_net'] ?? 0);
    $headerTitle = 'Laporan Penjualan Harian COD & CBD';
    $headerSubtitle = trim((string) ($reportData['period_label'] ?? ''));
    $generatedByName = trim((string) ($reportData['printed_by'] ?? ''));
    $globalRow = 0;

    if (! function_exists('fmtAmount_custom_report_laporan_penjualan_harian_cod_cbd')) {
        function fmtAmount_custom_report_laporan_penjualan_harian_cod_cbd($value)
        {
            $v = (float) $value;
            if ($v < 0) {
                return '(' . number_format(abs($v), 0, '.', ',') . ')';
            }
            if ($v == 0.0) {
                return '-';
            }
            return number_format($v, 0, '.', ',');
        }
    }
    if (! function_exists('fmtPct_custom_report_laporan_penjualan_harian_cod_cbd')) {
        function fmtPct_custom_report_laporan_penjualan_harian_cod_cbd($value)
        {
            $v = (float) $value;
            if ($v == 0.0) {
                return '-';
            }
            return number_format($v, 2, '.', ',') . '%';
        }
    }
    @endphp

    <h1 class="report-title">{{ $headerTitle }}</h1>
    <p class="report-subtitle">{{ $headerSubtitle }}</p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Tanggal Invoice</th>
                <th style="width: 12%;">No. Invoice</th>
                <th style="width: 17%;">Nama Pelanggan</th>
                <th style="width: 10%;">Kota</th>
                <th style="width: 7%;">Keterangan</th>
                <th style="width: 7%;">Payment Term</th>
                <th style="width: 5%;">Umur</th>
                <th style="width: 10%;">Nilai Invoice</th>
                <th style="width: 7%;">Diskon</th>
                <th style="width: 9%;">Total Netto</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sections as $section)
                <tr class="section-row">
                    <td colspan="11">{{ $section['label'] ?? '' }}</td>
                </tr>

                @foreach ($section['rows'] ?? [] as $row)
                    @php $globalRow++; @endphp
                    <tr class="{{ $globalRow % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td class="nowrap">{{ $row['invoice_date_label'] ?? '' }}</td>
                        <td>{{ $row['invoice_number'] ?? '' }}</td>
                        <td>{{ $row['customer_name'] ?? '' }}</td>
                        <td>{{ $row['city'] ?? '' }}</td>
                        <td class="center">{{ $row['remark'] ?? '' }}</td>
                        <td class="center">{{ $row['payment_term'] ?? 0 }}</td>
                        <td class="center">{{ $row['age'] ?? 0 }}</td>
                        <td class="number nowrap {{ ($row['total'] ?? 0) < 0 ? 'number-negative' : '' }}">
                            {{ fmtAmount_custom_report_laporan_penjualan_harian_cod_cbd($row['total'] ?? 0) }}</td>
                        <td class="number nowrap">{{ fmtPct_custom_report_laporan_penjualan_harian_cod_cbd($row['disc_pct'] ?? 0) }}</td>
                        <td class="number nowrap {{ ($row['net_total'] ?? 0) < 0 ? 'number-negative' : '' }}">
                            {{ fmtAmount_custom_report_laporan_penjualan_harian_cod_cbd($row['net_total'] ?? 0) }}</td>
                        <td class="center">{{ $row['status'] ?? '' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr class="empty-row">
                    <td colspan="11">Tidak ada data.</td>
                </tr>
            @endforelse

            @if (count($sections) > 0)
                <tr class="grand-total-row">
                    <td colspan="6" class="center">Grand Total</td>
                    <td></td>
                    <td class="number nowrap">
                        {{ fmtAmount_custom_report_laporan_penjualan_harian_cod_cbd($grandTotal) }}</td>
                    <td></td>
                    <td class="number nowrap">
                        {{ fmtAmount_custom_report_laporan_penjualan_harian_cod_cbd($grandNet) }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>Diperiksa Oleh :</td>
            <td>Diketahui Oleh :</td>
        </tr>
        <tr class="signature-space-row">
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>(Kadiv. Accounting)</td>
            <td>(Kadept. F&amp;A)</td>
        </tr>
    </table>

    @include('ascends.shared.partials.report-footer')
</body>

</html>
