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
    @if ($diagramChart !== '')
        <div style="text-align: center; margin: 4px 0 0 0;">
            <img src="{{ $diagramChart }}" alt="Diagram {{ $diagramLabel }}"
                style="width: 250px; height: 250px;">
        </div>
    @endif

    @if (count($diagramLeft) > 0 || count($diagramRight) > 0)
        <table align="center" style="width: 82%; margin: 4px auto 10px auto; border-collapse: collapse; font-size: 10px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding: 0 8px; line-height: 1.35;">
                    @foreach ($diagramLeft as $i => $item)
                        @php $color = $diagramColors[$i % 12] ?? [0, 0, 0]; @endphp
                        <div style="margin: 0 0 2px 0;">
                            <span
                                style="display: inline-block; width: 12px; height: 12px; margin-right: 4px; border: 1px solid #000; background-color: rgb({{ $color[0] }},{{ $color[1] }},{{ $color[2] }});">&nbsp;</span>
                            {{ $item['name'] }} ({{ $item['count'] }}) - {{ number_format((float) $item['percent'], 1) }}%
                        </div>
                    @endforeach
                </td>
                <td style="width: 50%; vertical-align: top; padding: 0 8px; line-height: 1.35;">
                    @foreach ($diagramRight as $i => $item)
                        @php $color = $diagramColors[($diagramMid + $i) % 12] ?? [0, 0, 0]; @endphp
                        <div style="margin: 0 0 2px 0;">
                            <span
                                style="display: inline-block; width: 12px; height: 12px; margin-right: 4px; border: 1px solid #000; background-color: rgb({{ $color[0] }},{{ $color[1] }},{{ $color[2] }});">&nbsp;</span>
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
            <tr style="font-weight: bold;">
                <td class="center" colspan="2" style="border-top: 1px solid #000;">Total :</td>
                <td class="center" style="border-top: 1px solid #000;">{{ $diagramTotal }}</td>
                <td class="center nowrap" style="border-top: 1px solid #000;">
                    {{ $diagramTotal > 0 ? '100.0%' : '0.0%' }}
                </td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>
@endif
