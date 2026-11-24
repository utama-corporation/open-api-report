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

    .cost-centre-row td {
        font-weight: bold;
        font-size: 10px;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        padding: 4px 2px;
    }

    .subtotal-row td,
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
        margin: 0;
    }

    .signature-table td {
        width: 33.333%;
        text-align: center;
        vertical-align: top;
        padding: 0 2px;
        font-size: 10px;
    }

    .signature-space-row td {
        height: 20mm;
        padding: 0;
    }

    .signature-gap-row td {
        height: 8mm;
        padding: 0;
    }
    </style>
</head>

<body>
    @php
    $accounts = $reportData['accounts'] ?? [];
    $grandTotalDebit = (float) ($reportData['grand_total_debit'] ?? 0);
    $grandTotalCredit = (float) ($reportData['grand_total_credit'] ?? 0);
    $grandTotalBalance = (float) ($reportData['grand_total_balance'] ?? 0);
    $headerCompany = trim((string) ($company ?? ($reportData['company'] ?? '')));
    $headerTitle = 'Laporan Biaya Lain-lain Dibayar Dimuka';
    $headerSubtitle = trim((string) ($reportData['period_label'] ?? ''));
    $generatedByName = trim((string) ($reportData['printed_by'] ?? ''));
    $footerDateTime = isset($generatedAt)
        ? \Carbon\Carbon::parse($generatedAt)->locale('id')->translatedFormat('d-M-y H:i')
        : now()->locale('id')->translatedFormat('d-M-y H:i');
    $globalRow = 0;

    if (! function_exists('fmtAmount_finance_outstanding_advance_payable_opname_kas')) {
        function fmtAmount_finance_outstanding_advance_payable_opname_kas($value)
        {
            $v = (float) $value;
            if ($v < 0) {
                return '(' . number_format(abs($v), 2, '.', ',') . ')';
            }
            if ($v == 0.0) {
                return '-';
            }
            return number_format($v, 2, '.', ',');
        }
    }
    @endphp

    <h1 class="report-companyTitle">{{ $headerCompany }}</h1>
    <h1 class="report-title">{{ $headerTitle }}</h1>
    <p class="report-subtitle">{{ $headerSubtitle }}</p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 14%;">No. Voucher</th>
                <th style="width: 36%;">Keterangan</th>
                <th style="width: 12%;">Debit</th>
                <th style="width: 12%;">Kredit</th>
                <th style="width: 18%;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($accounts as $account)
                <tr class="cost-centre-row">
                    <td colspan="6">Cost Centre : {{ $account['label'] ?? '' }}</td>
                </tr>

                @if (abs((float) ($account['opening_balance'] ?? 0)) >= 0.005)
                    @php $globalRow++; @endphp
                    <tr class="{{ $globalRow % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td></td>
                        <td></td>
                        <td>BEGINNING BALANCE</td>
                        <td class="number nowrap">-</td>
                        <td class="number nowrap">-</td>
                        <td class="number nowrap {{ ($account['opening_balance'] ?? 0) < 0 ? 'number-negative' : '' }}">
                            {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($account['opening_balance'] ?? 0) }}</td>
                    </tr>
                @endif

                @foreach ($account['rows'] ?? [] as $row)
                    @php $globalRow++; @endphp
                    <tr class="{{ $globalRow % 2 === 0 ? 'row-even' : 'row-odd' }}">
                        <td class="nowrap">{{ $row['date_label'] ?? '' }}</td>
                        <td>{{ $row['voucher_no'] ?? '' }}</td>
                        <td>{{ $row['description'] ?? '' }}</td>
                        <td class="number nowrap {{ ($row['debit'] ?? 0) < 0 ? 'number-negative' : '' }}">
                            {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($row['debit'] ?? 0) }}</td>
                        <td class="number nowrap {{ ($row['credit'] ?? 0) < 0 ? 'number-negative' : '' }}">
                            {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($row['credit'] ?? 0) }}</td>
                        <td class="number nowrap {{ ($row['balance'] ?? 0) < 0 ? 'number-negative' : '' }}">
                            {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($row['balance'] ?? 0) }}</td>
                    </tr>
                @endforeach

                <tr class="subtotal-row">
                    <td colspan="3">Sub Total</td>
                    <td class="number nowrap">
                        {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($account['total_debit'] ?? 0) }}</td>
                    <td class="number nowrap">
                        {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($account['total_credit'] ?? 0) }}</td>
                    <td class="number nowrap">
                        {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($account['ending_balance'] ?? 0) }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="6">Tidak ada data.</td>
                </tr>
            @endforelse

            @if (count($accounts) > 0)
                <tr class="grand-total-row">
                    <td colspan="3" class="center">TOTAL</td>
                    <td class="number nowrap">
                        {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($grandTotalDebit) }}</td>
                    <td class="number nowrap">
                        {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($grandTotalCredit) }}</td>
                    <td class="number nowrap">
                        {{ fmtAmount_finance_outstanding_advance_payable_opname_kas($grandTotalBalance) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <htmlpagefooter name="signedFooter">
        <table class="signature-table">
            <tr>
                <td>Dibukukan Oleh :</td>
                <td>Diperiksa Oleh :</td>
                <td>Disetujui Oleh :</td>
            </tr>
            <tr class="signature-space-row">
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>(Staff Kasir)</td>
                <td>(Kadiv. Accounting)</td>
                <td>(Kadept. F&amp;A)</td>
            </tr>
            <tr class="signature-gap-row">
                <td colspan="3"></td>
            </tr>
        </table>

        <table
            style="width: 100%; border-collapse: collapse; border-spacing: 0; table-layout: fixed; border: 0; margin: 0; padding: 0;">
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 40%;">
                <col style="width: 30%;">
            </colgroup>
            <tr>
                <td
                    style="border: 0; background: transparent; padding: 0; margin: 0; vertical-align: bottom; text-align: left; white-space: nowrap; font-family: 'Noto Serif', serif; font-size: 8px; font-style: italic; font-weight: normal;">
                    Dicetak oleh: {{ $generatedByName }} pada {{ $footerDateTime }}
                </td>
                <td
                    style="border: 0; background: transparent; padding: 0; margin: 0; vertical-align: bottom; text-align: center; white-space: nowrap; font-family: 'Noto Serif', serif; font-size: 8px; font-style: italic; font-weight: normal;">
                </td>
                <td
                    style="border: 0; background: transparent; padding: 0; margin: 0; vertical-align: bottom; text-align: right; white-space: nowrap; font-family: 'Noto Serif', serif; font-size: 8px; font-style: italic; font-weight: normal;">
                    Halaman {PAGENO} dari {nbpg}
                </td>
            </tr>
        </table>
    </htmlpagefooter>

    @include('ascends.shared.partials.report-footer')

    <sethtmlpagefooter name="signedFooter" value="on" />
</body>

</html>
