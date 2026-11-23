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

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        .page-block {
            page-break-after: always;
        }

        .page-block.last-page {
            page-break-after: auto;
        }

        .report-title {
            margin: 0 0 10px 0;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
        }

        .meta-grid {
            margin-bottom: 4px;
        }

        .meta-grid td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .meta-pane-left {
            width: 40%;
            padding-right: 6px;
        }

        .meta-pane-middle {
            width: 30%;
            padding: 0 6px;
        }

        .meta-pane-right {
            width: 30%;
            padding-left: 6px;
        }

        .meta-table td {
            border: 0;
            padding: 2px 0;
            vertical-align: top;
            font-size: 10px;
        }

        .meta-label {
            width: 72px;
            font-weight: bold;
        }

        .meta-sep {
            width: 10px;
            text-align: center;
        }

        .summary-table {
            margin-top: 16px;
            border: 1px solid #000;
        }

        .summary-table td {
            border: 0;
            padding: 2px 4px;
            text-align: left;
            vertical-align: top;
            font-size: 10px;
        }

        .summary-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary-label {
            width: 18%;
            font-weight: bold;
        }

        .summary-table .meta-sep {
            width: 3%;
        }

        .summary-value {
            width: 29%;
        }

        .section-title-grid {
            margin: 10px 0 2px 0;
        }

        .section-title-grid td {
            border: 0;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            padding: 0 0 2px 0;
        }

        .split-grid td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .split-grid .left-pane {
            width: 50%;
            padding-right: 8px;
        }

        .split-grid .right-pane {
            width: 50%;
            padding-left: 8px;
        }

        .section-heading {
            margin: 0 0 2px 0;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.1;
        }

        .detail-table {
            border: 1px solid #000;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 10px;
        }

        .detail-table thead th {
            text-align: center;
            font-weight: bold;
            line-height: 1.1;
        }

        .detail-table tbody td {
            border-top: 0;
            border-bottom: 0;
            vertical-align: middle;
        }

        .detail-table tbody tr:nth-child(odd) td {
            background: #eef2f8;
        }

        .detail-table tbody tr:nth-child(even) td {
            background: #cfd8e6;
        }

        .detail-table tfoot td {
            font-weight: bold;
            font-size: 11px;
        }

        .center {
            text-align: center;
        }

        .number {
            text-align: right;
            white-space: nowrap;
            font-family: "Calibri", "DejaVu Sans", sans-serif;
        }

        .total-label {
            text-align: center;
        }

        .rendemen-line {
            margin: 6px 0 0 0;
            font-size: 11px;
        }
    </style>
</head>

<body>
    @php
        $meta = $report['meta'] ?? [];
        $inputRows = $report['input_rows'] ?? [];
        $outputRows = $report['output_rows'] ?? [];
        $repairRows = $report['repair_rows'] ?? [];
        $afkirRows = $report['afkir_rows'] ?? [];
        $inputReprosesRows = $report['input_reproses_rows'] ?? [];
        $outputReprosesRows = $report['output_reproses_rows'] ?? [];
        $totals = $report['totals'] ?? [];
        $generatedByName = $generatedBy->name ?? 'sistem';

        $fmtInt = static fn($value): string => $value === null ? '' : number_format((float) $value, 0, '.', ',');
        $fmt4 = static fn($value): string => $value === null ? '' : number_format((float) $value, 4, '.', '');
        $fmt2 = static fn($value): string => $value === null ? '' : number_format(round((float) $value, 2), 2, '.', '');
        $fmtPercent = static fn($value): string => $value === null
            ? '-'
            : number_format((float) $value, 2, '.', '') . '%';

        $emptyTotals = ['count' => 0, 'jmlh_batang' => 0, 'kubik' => 0];
        $inputTotals = $totals['input'] ?? $emptyTotals;
        $outputTotals = $totals['output'] ?? $emptyTotals;
        $repairTotals = $totals['repair'] ?? $emptyTotals;
        $afkirTotals = $totals['afkir'] ?? $emptyTotals;
        $inputReprosesTotals = $totals['input_reproses'] ?? $emptyTotals;
        $outputReprosesTotals = $totals['output_reproses'] ?? $emptyTotals;
        $rendemen = $totals['rendemen'] ?? null;
        $rendemenReproses = $totals['rendemen_reproses'] ?? null;

        // Tinggi layout berurutan (mm): header halaman, overhead tiap pasangan tabel,
        // tinggi baris, baris rendemen, dan Rangkuman.
        $pageHeightMm = 263;
        $pageHeaderHeightMm = 28;
        $pairOverheadMm = 21;
        $rowHeightMm = 4.9;
        $rendemenHeightMm = 6.5;
        $summaryHeightMm = 27;
        $sectionGapMm = 3;

        $mainRendemen = [
            'label' => 'Rendemen',
            'numerator' => $fmt4($outputTotals['kubik'] ?? 0) . ' + ' . $fmt4($repairTotals['kubik'] ?? 0),
            'denominator' => $fmt4($inputTotals['kubik'] ?? 0),
            'percent' => $fmtPercent($rendemen),
        ];
        $reprosesRendemen = ($inputReprosesRows !== [] || $outputReprosesRows !== [])
            ? [
                'label' => 'Rendemen Reproses',
                'numerator' => $fmt4($outputReprosesTotals['kubik'] ?? 0),
                'denominator' => $fmt4($inputReprosesTotals['kubik'] ?? 0),
                'percent' => $fmtPercent($rendemenReproses),
            ]
            : null;

        $blocks = [
            [
                'titles' => ['Input', 'Output'],
                'headings' => [
                    'Input : ' . ($meta['input_label'] ?? 'INPUT'),
                    'Output : ' . ($meta['output_label'] ?? 'OUTPUT'),
                ],
                'left_rows' => $inputRows,
                'right_rows' => $outputRows,
                'left_totals' => $inputTotals,
                'right_totals' => $outputTotals,
                'left_empty' => 'Data input tidak tersedia.',
                'right_empty' => 'Data output tidak tersedia.',
                'rendemen' => $mainRendemen,
            ],
            [
                'titles' => ['Repair', 'Afkir'],
                'headings' => ['Repair', 'Afkir'],
                'left_rows' => $repairRows,
                'right_rows' => $afkirRows,
                'left_totals' => $repairTotals,
                'right_totals' => $afkirTotals,
                'left_empty' => 'Data repair tidak tersedia.',
                'right_empty' => 'Data afkir tidak tersedia.',
                'rendemen' => null,
            ],
            [
                'titles' => ['Input Reproses', 'Output Reproses'],
                'headings' => ['Input Reproses', 'Output Reproses'],
                'left_rows' => $inputReprosesRows,
                'right_rows' => $outputReprosesRows,
                'left_totals' => $inputReprosesTotals,
                'right_totals' => $outputReprosesTotals,
                'left_empty' => 'Data input reproses tidak tersedia.',
                'right_empty' => 'Data output reproses tidak tersedia.',
                'rendemen' => $reprosesRendemen,
            ],
        ];

        $pages = [];
        $currentPage = ['blocks' => [], 'summary' => false];
        $usedHeightMm = $pageHeaderHeightMm;

        $flushPage = static function () use (&$pages, &$currentPage, &$usedHeightMm, $pageHeaderHeightMm): void {
            if ($currentPage['blocks'] !== [] || $currentPage['summary']) {
                $pages[] = $currentPage;
            }
            $currentPage = ['blocks' => [], 'summary' => false];
            $usedHeightMm = $pageHeaderHeightMm;
        };

        foreach ($blocks as $block) {
            $leftRemaining = array_values($block['left_rows']);
            $rightRemaining = array_values($block['right_rows']);
            $firstChunk = true;
            $leftTotalShown = false;
            $rightTotalShown = false;

            if ($leftRemaining === [] && $rightRemaining === []) {
                $blockHeight = $pairOverheadMm + $rowHeightMm + $sectionGapMm
                    + ($block['rendemen'] !== null ? $rendemenHeightMm : 0);
                if ($usedHeightMm + $blockHeight > $pageHeightMm) {
                    $flushPage();
                }
                $currentPage['blocks'][] = [
                    'titles' => $block['titles'],
                    'headings' => $block['headings'],
                    'left_rows' => [],
                    'right_rows' => [],
                    'left_totals' => $block['left_totals'],
                    'right_totals' => $block['right_totals'],
                    'left_empty' => $block['left_empty'],
                    'right_empty' => $block['right_empty'],
                    'show_left_empty' => true,
                    'show_right_empty' => true,
                    'show_left_total' => true,
                    'show_right_total' => true,
                    'rendemen' => $block['rendemen'],
                ];
                $usedHeightMm += $blockHeight;
                continue;
            }

            while ($leftRemaining !== [] || $rightRemaining !== []) {
                $available = $pageHeightMm - $usedHeightMm;
                $maxRows = (int) floor(($available - $pairOverheadMm) / $rowHeightMm);

                if ($maxRows < 1) {
                    $flushPage();
                    continue;
                }

                $maxSide = max(count($leftRemaining), count($rightRemaining));
                $take = min($maxRows, $maxSide);

                // Kalau chunk ini menghabiskan blok, sisakan ruang untuk baris rendemen.
                $willComplete = $take >= count($leftRemaining) && $take >= count($rightRemaining);
                if ($willComplete && $block['rendemen'] !== null) {
                    $maxRowsWithLine = (int) floor(($available - $pairOverheadMm - $rendemenHeightMm) / $rowHeightMm);
                    if ($maxRowsWithLine < 1) {
                        $flushPage();
                        continue;
                    }
                    $take = min($take, $maxRowsWithLine);
                }

                $leftChunk = array_splice($leftRemaining, 0, $take);
                $rightChunk = array_splice($rightRemaining, 0, $take);
                $isLastLeft = $leftRemaining === [];
                $isLastRight = $rightRemaining === [];
                $completes = $isLastLeft && $isLastRight;

                $showLeftTotal = ! $leftTotalShown && $isLastLeft && ($leftChunk !== [] || $firstChunk);
                $showRightTotal = ! $rightTotalShown && $isLastRight && ($rightChunk !== [] || $firstChunk);
                if ($showLeftTotal) {
                    $leftTotalShown = true;
                }
                if ($showRightTotal) {
                    $rightTotalShown = true;
                }

                $currentPage['blocks'][] = [
                    'titles' => $block['titles'],
                    'headings' => $block['headings'],
                    'left_rows' => $leftChunk,
                    'right_rows' => $rightChunk,
                    'left_totals' => $block['left_totals'],
                    'right_totals' => $block['right_totals'],
                    'left_empty' => $block['left_empty'],
                    'right_empty' => $block['right_empty'],
                    'show_left_empty' => $firstChunk && $leftChunk === [],
                    'show_right_empty' => $firstChunk && $rightChunk === [],
                    'show_left_total' => $showLeftTotal,
                    'show_right_total' => $showRightTotal,
                    'rendemen' => $completes ? $block['rendemen'] : null,
                ];

                $usedHeightMm += $pairOverheadMm + $take * $rowHeightMm + $sectionGapMm
                    + ($completes && $block['rendemen'] !== null ? $rendemenHeightMm : 0);
                $firstChunk = false;

                if (! $completes) {
                    $flushPage();
                }
            }
        }

        if ($usedHeightMm + $summaryHeightMm > $pageHeightMm) {
            $flushPage();
        }
        $currentPage['summary'] = true;
        $flushPage();

        if ($pages === []) {
            $pages[] = ['blocks' => [], 'summary' => true];
        }
    @endphp

    @foreach ($pages as $pageIndex => $page)
        <div class="page-block {{ $pageIndex === count($pages) - 1 ? 'last-page' : '' }}">
            <h1 class="report-title">Laporan Produksi Per Nomor Produksi Finger Joint</h1>

            <table class="meta-grid">
                <tr>
                    <td class="meta-pane-left">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">No Produksi</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $meta['no_produksi'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Tanggal</td>
                                <td class="meta-sep">:</td>
                                <td>{{ isset($meta['tanggal']) && $meta['tanggal'] instanceof \Carbon\Carbon ? $meta['tanggal']->format('d-M-y') : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="meta-label">Mesin</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $meta['nama_mesin'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Operator</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $meta['operator'] ?? '' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="meta-pane-middle">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">Shift</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $meta['shift'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Jam Kerja</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $fmtInt($meta['jam_kerja'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Anggota</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $fmtInt($meta['anggota'] ?? null) }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="meta-pane-right">
                        <table class="meta-table">
                            <tr>
                                <td class="meta-label">Jam Lembur</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $fmtInt($meta['jam_lembur'] ?? 0) }} Jam</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Hour Meter</td>
                                <td class="meta-sep">:</td>
                                <td>{{ $fmt2($meta['hour_meter'] ?? null) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            @foreach ($page['blocks'] as $block)
                <table class="section-title-grid">
                    <tr>
                        <td>{{ $block['titles'][0] }}</td>
                        <td>{{ $block['titles'][1] }}</td>
                    </tr>
                </table>

                <table class="split-grid">
                    <tr>
                        <td class="left-pane">
                            <p class="section-heading">{{ $block['headings'][0] }}</p>
                            @include('reports.partials.produksi-detail-table', [
                                'rows' => $block['left_rows'],
                                'totals' => $block['left_totals'],
                                'emptyMessage' => $block['left_empty'],
                                'showEmpty' => $block['show_left_empty'],
                                'showTotal' => $block['show_left_total'],
                                'fmtInt' => $fmtInt,
                                'fmt4' => $fmt4,
                            ])
                        </td>
                        <td class="right-pane">
                            <p class="section-heading">{{ $block['headings'][1] }}</p>
                            @include('reports.partials.produksi-detail-table', [
                                'rows' => $block['right_rows'],
                                'totals' => $block['right_totals'],
                                'emptyMessage' => $block['right_empty'],
                                'showEmpty' => $block['show_right_empty'],
                                'showTotal' => $block['show_right_total'],
                                'fmtInt' => $fmtInt,
                                'fmt4' => $fmt4,
                            ])
                        </td>
                    </tr>
                </table>

                @if ($block['rendemen'] !== null)
                    <p class="rendemen-line">
                        {{ $block['rendemen']['label'] }} :
                        ({{ $block['rendemen']['numerator'] }})
                        / {{ $block['rendemen']['denominator'] }}
                        = {{ $block['rendemen']['percent'] }}
                    </p>
                @endif
            @endforeach

            @if ($page['summary'])
                <table class="summary-table">
                    <tr>
                        <td class="summary-title" colspan="6">Rangkuman</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Jam Kerja</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmtInt($meta['jam_kerja'] ?? null) }} Jam</td>
                        <td class="summary-label">Jam Lembur</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmtInt($meta['jam_lembur'] ?? 0) }} Jam</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Input</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmt4($inputTotals['kubik'] ?? 0) }} m&sup3;</td>
                        <td class="summary-label">Total Repair</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmt4($repairTotals['kubik'] ?? 0) }} m&sup3;</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Output</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmt4($outputTotals['kubik'] ?? 0) }} m&sup3;</td>
                        <td class="summary-label">Total Afkir</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmt4($afkirTotals['kubik'] ?? 0) }} m&sup3;</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Input Reproses</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmt4($inputReprosesTotals['kubik'] ?? 0) }} m&sup3;</td>
                        <td class="summary-label">Total Output Reproses</td>
                        <td class="meta-sep">:</td>
                        <td class="summary-value">{{ $fmt4($outputReprosesTotals['kubik'] ?? 0) }} m&sup3;</td>
                    </tr>
                </table>
            @endif
        </div>
    @endforeach

    @include('reports.partials.pdf-reference-footer')
</body>

</html>