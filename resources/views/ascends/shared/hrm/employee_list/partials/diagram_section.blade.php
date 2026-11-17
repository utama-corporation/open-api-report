@php
    $diagram = $reportData['diagram'] ?? [];
    $diagramItems = $diagram['tableRows'] ?? $diagram['departments'] ?? [];
    $diagramTotal = (int) ($diagram['total'] ?? 0);
    $diagramChart = (string) ($diagram['pie_chart_base64'] ?? '');
    $diagramLabel = $diagramLabel ?? 'Keterangan';
    $diagramMid = (int) ceil(count($diagramItems) / 2);
    $diagramLeft = array_slice($diagramItems, 0, $diagramMid);
    $diagramRight = array_slice($diagramItems, $diagramMid);
    $diagramColors = [
        [52, 73, 94], [231, 76, 60], [241, 196, 15], [46, 204, 113],
        [155, 89, 182], [230, 126, 34], [149, 165, 166], [52, 152, 219],
        [214, 97, 143], [244, 208, 63], [88, 214, 141], [93, 173, 226],
    ];
@endphp

@if (! empty($reportData['diagram']))
    <style>
        .combined-chart-wrapper {
            text-align: center;
            margin: 10px 0;
        }

        .combined-chart-wrapper img {
            width: 320px;
            height: 320px;
        }

        .combined-labels {
            width: 100%;
            margin: 8px 0;
            font-size: 10px;
        }

        .combined-labels td {
            padding: 1px 6px;
            vertical-align: top;
            width: 50%;
        }

        .combined-label-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 4px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .combined-total-row td {
            font-weight: bold;
            border-top: 1px solid #000;
        }
    </style>

    @if ($diagramChart !== '')
        <div class="combined-chart-wrapper">
            <img src="{{ $diagramChart }}" alt="Diagram {{ $diagramLabel }}">
        </div>
    @endif

    @if (count($diagramLeft) > 0 || count($diagramRight) > 0)
        <table class="combined-labels">
            <tr>
                <td>
                    @foreach ($diagramLeft as $i => $item)
                        @php $color = $diagramColors[$i % 12] ?? [0, 0, 0]; @endphp
                        <div>
                            <span class="combined-label-box"
                                style="background: rgb({{ $color[0] }},{{ $color[1] }},{{ $color[2] }});"></span>
                            {{ $item['name'] }} ({{ $item['count'] }}) - {{ number_format((float) $item['percent'], 1) }}%
                        </div>
                    @endforeach
                </td>
                <td>
                    @foreach ($diagramRight as $i => $item)
                        @php $color = $diagramColors[($diagramMid + $i) % 12] ?? [0, 0, 0]; @endphp
                        <div>
                            <span class="combined-label-box"
                                style="background: rgb({{ $color[0] }},{{ $color[1] }},{{ $color[2] }});"></span>
                            {{ $item['name'] }} ({{ $item['count'] }}) - {{ number_format((float) $item['percent'], 1) }}%
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">No.</th>
                <th style="width: 52%;">{{ $diagramLabel }}</th>
                <th style="width: 20%;">Jumlah</th>
                <th style="width: 20%;">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($diagramItems as $item)
                <tr class="{{ $loop->odd ? 'row-odd' : 'row-even' }}">
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="center">{{ $item['count'] }}</td>
                    <td class="center nowrap">{{ number_format((float) $item['percent'], 1) }}%</td>
                </tr>
            @endforeach
            <tr class="combined-total-row">
                <td class="center" colspan="2">Total :</td>
                <td class="center">{{ $diagramTotal }}</td>
                <td class="center nowrap">{{ $diagramTotal > 0 ? '100.0%' : '0.0%' }}</td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>
@endif
