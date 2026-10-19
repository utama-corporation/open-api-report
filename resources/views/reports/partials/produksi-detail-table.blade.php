{{--
    Tabel detail produksi: No Label / Tebal / Lebar / Panjang / Jmlh Batang / Kubik / No SPK Tujuan.
    Dipakai oleh tabel Input, Output, Repair, dan Afkir pada laporan produksi per nomor produksi.

    Data yang dibutuhkan:
      $rows         array<int, array<string, mixed>>  baris pada halaman ini
      $totals       array<string, mixed>             total keseluruhan tabel
      $emptyMessage string                            pesan saat tabel kosong
      $showEmpty    bool                              tampilkan pesan kosong (hanya halaman pertama)
      $showTotal    bool                              tampilkan baris total (hanya halaman terakhir)
      $fmtInt       Closure                          formatter bilangan bulat
      $fmt4         Closure                          formatter 4 desimal
--}}
<table class="detail-table">
    <thead>
        <tr>
            <th style="width: 18%;">No Label</th>
            <th style="width: 11%;">Tebal (mm)</th>
            <th style="width: 11%;">Lebar (mm)</th>
            <th style="width: 15%;">Panjang (ft)</th>
            <th style="width: 15%;">Jmlh Batang</th>
            <th style="width: 15%;">Kubik</th>
            <th style="width: 15%;">No SPK Tujuan</th>
        </tr>
    </thead>
    <tbody>
        @if ($rows === [])
            @if ($showEmpty)
                <tr>
                    <td class="center" colspan="7">{{ $emptyMessage }}</td>
                </tr>
            @endif
        @else
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['no_label'] ?? '' }}</td>
                    <td class="center">{{ $fmtInt($row['tebal'] ?? null) }}</td>
                    <td class="center">{{ $fmtInt($row['lebar'] ?? null) }}</td>
                    <td class="number">{{ $fmtInt($row['panjang'] ?? null) }}</td>
                    <td class="number">{{ $fmtInt($row['jmlh_batang'] ?? null) }}</td>
                    <td class="number">{{ $fmt4($row['kubik'] ?? null) }}</td>
                    <td>{{ $row['no_spk_tujuan'] ?? '' }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
    @if ($showTotal)
        <tfoot>
            <tr>
                <td class="center">{{ $fmtInt($totals['count'] ?? 0) }}</td>
                <td class="total-label" colspan="3">Total :</td>
                <td class="number">{{ $fmtInt($totals['jmlh_batang'] ?? 0) }}</td>
                <td class="number">{{ $fmt4($totals['kubik'] ?? 0) }}</td>
                <td></td>
            </tr>
        </tfoot>
    @endif
</table>
